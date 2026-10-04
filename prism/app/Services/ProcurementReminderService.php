<?php

namespace App\Services;

use App\Models\BudgetProposalItem;
use App\Models\PrismNotification;
use App\Models\PurchaseRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ProcurementReminderService
{
    private const UPCOMING_DAYS = [7, 1, 0];
    private const INACTIVE_PR_DAYS = 10;
    private ?string $recipientUsername = null;
    private bool $sendPush = true;

    /**
     * Generate target-date, inactive-PR, and Monday digest notifications.
     * Dry runs return the exact candidates without inserting notifications.
     */
    public function run(
        CarbonImmutable $today,
        bool $dryRun = false,
        bool $includeWeekly = false,
        ?string $recipientUsername = null,
        bool $sendPush = true
    ): array
    {
        $today = $today->setTimezone(config('app.timezone'))->startOfDay();
        $this->recipientUsername = $recipientUsername;
        $this->sendPush = $sendPush;
        $results = collect();
        $procurementUsers = $this->usersForRole('Procurement Office');
        $officeHeads = User::query()
            ->where('account_status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('name', 'Office Head / Dean'))
            ->when($recipientUsername, fn ($query, $username) => $query->where('username', $username))
            ->get()
            ->groupBy('office_id');

        $purchaseRequests = PurchaseRequest::with(['items', 'office', 'statusUpdates', 'signatureLogs'])->get();
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($query) => $query->whereIn('status', ['endorsed', 'approved']))
            ->where(function ($query) {
                $query->whereNotNull('procurement_start_date')
                    ->orWhereNotNull('date_needed')
                    ->orWhereNotNull('target_quarter');
            })
            ->get();

        $openItems = $items->filter(function (BudgetProposalItem $item) use ($purchaseRequests) {
            $pr = $this->matchingPurchaseRequest($item, $purchaseRequests);
            return !$pr || !in_array($pr->lifecycleBucket(), ['completed', 'delayed'], true);
        });

        foreach ($openItems as $item) {
            foreach ($this->targetDates($item) as $target) {
                $daysUntil = (int) $today->diffInDays($target['date'], false);
                if (!in_array($daysUntil, self::UPCOMING_DAYS, true)) {
                    continue;
                }

                $office = $item->budgetProposal?->office?->code ?? 'Unknown office';
                $when = match ($daysUntil) {
                    7 => 'in 7 days',
                    1 => 'tomorrow',
                    default => 'today',
                };
                $title = $daysUntil === 0 ? 'Procurement Target Is Today' : 'Upcoming Procurement Target';
                $message = "{$item->name} ({$office}) has its {$target['label']} {$when}, " . $target['date']->format('M d, Y') . '.';
                $key = "target:item:{$item->id}:{$target['key']}:{$target['date']->toDateString()}:{$daysUntil}";
                $data = ['itemId' => $item->id, 'daysUntil' => $daysUntil, 'targetDate' => $target['date']->toDateString()];

                foreach ($procurementUsers as $user) {
                    $this->deliver($results, $user, $key, 'procurement_target', $title, $message,
                        route('procurement-office.annual-procurement-plan', [], false), $data, $dryRun);
                }
                foreach ($officeHeads->get($item->budgetProposal?->office_id, collect()) as $user) {
                    $this->deliver($results, $user, $key, 'procurement_target', $title, $message,
                        route('office-head.purchase-requests', [], false), $data, $dryRun);
                }
            }
        }

        $inactivePrs = $purchaseRequests->filter(function (PurchaseRequest $pr) use ($today) {
            if ($pr->signingStatusBucket() === 'completed' || $pr->lifecycleBucket() === 'delayed') {
                return false;
            }
            return $this->lastMeaningfulActionAt($pr)?->diffInDays($today) >= self::INACTIVE_PR_DAYS;
        });

        foreach ($inactivePrs as $pr) {
            $lastAction = $this->lastMeaningfulActionAt($pr);
            $days = (int) $lastAction->diffInDays($today);
            $number = $pr->number ?: 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT);
            $message = "{$number} has had no recorded action for {$days} days. Please review or follow up.";
            $key = "inactive-pr:{$pr->id}:day-" . self::INACTIVE_PR_DAYS;
            $data = ['docType' => 'pr', 'id' => $pr->id, 'daysInactive' => $days];

            foreach ($procurementUsers as $user) {
                $this->deliver($results, $user, $key, 'pr_inactive', 'Purchase Request Needs Follow-up', $message,
                    route('procurement-office.purchase-request-management', [], false), $data, $dryRun);
            }
            foreach ($officeHeads->get($pr->office_id, collect()) as $user) {
                $this->deliver($results, $user, $key, 'pr_inactive', 'Your Purchase Request Needs Follow-up', $message,
                    route('office-head.purchase-requests', [], false), $data, $dryRun);
            }
        }

        if ($today->isMonday() || $includeWeekly) {
            $this->sendWeeklyDigests($results, $today, $openItems, $inactivePrs, $procurementUsers, $officeHeads, $dryRun);
        }

        return [
            'date' => $today->toDateString(),
            'dryRun' => $dryRun,
            'created' => $results->where('status', 'created')->count(),
            'planned' => $results->where('status', 'planned')->count(),
            'skipped' => $results->where('status', 'skipped')->count(),
            'results' => $results->values()->all(),
        ];
    }

    private function sendWeeklyDigests(
        Collection $results,
        CarbonImmutable $today,
        Collection $openItems,
        Collection $inactivePrs,
        Collection $procurementUsers,
        Collection $officeHeads,
        bool $dryRun
    ): void {
        $weekKey = $today->startOfWeek()->toDateString();
        $overdueItems = $openItems->filter(fn ($item) => collect($this->targetDates($item))->contains(fn ($target) => $target['date']->isBefore($today)));
        $upcomingItems = $openItems->filter(fn ($item) => collect($this->targetDates($item))->contains(function ($target) use ($today) {
            $days = (int) $today->diffInDays($target['date'], false);
            return $days >= 0 && $days <= 7;
        }));

        $message = "Weekly procurement check: {$openItems->count()} open activities, {$upcomingItems->count()} due within 7 days, {$overdueItems->count()} past target, and {$inactivePrs->count()} PRs inactive for 10+ days.";
        foreach ($procurementUsers as $user) {
            $this->deliver($results, $user, "weekly-procurement:{$weekKey}", 'procurement_weekly_digest',
                'Monday Procurement Check', $message, route('procurement-office.annual-procurement-plan', [], false),
                ['weekOf' => $weekKey], $dryRun);
        }

        foreach ($officeHeads as $officeId => $users) {
            $officeItems = $openItems->filter(fn ($item) => (int) $item->budgetProposal?->office_id === (int) $officeId);
            $officeInactive = $inactivePrs->where('office_id', $officeId);
            if ($officeItems->isEmpty() && $officeInactive->isEmpty()) {
                continue;
            }
            $officeOverdue = $officeItems->filter(fn ($item) => collect($this->targetDates($item))->contains(fn ($target) => $target['date']->isBefore($today)))->count();
            $officeMessage = "Weekly check for your office: {$officeItems->count()} open procurement activities, {$officeOverdue} past target, and {$officeInactive->count()} PRs inactive for 10+ days.";
            foreach ($users as $user) {
                $this->deliver($results, $user, "weekly-procurement:{$weekKey}", 'procurement_weekly_digest',
                    'Monday Procurement Check', $officeMessage, route('office-head.purchase-requests', [], false),
                    ['weekOf' => $weekKey, 'officeId' => (int) $officeId], $dryRun);
            }
        }
    }

    private function targetDates(BudgetProposalItem $item): array
    {
        $dates = [];
        if ($item->procurement_start_date) {
            $dates[] = ['key' => 'start', 'label' => 'procurement start date', 'date' => CarbonImmutable::instance($item->procurement_start_date)->startOfDay()];
        }
        if ($item->date_needed) {
            $dates[] = ['key' => 'needed', 'label' => 'date needed', 'date' => CarbonImmutable::instance($item->date_needed)->startOfDay()];
        }
        if (!$dates && preg_match('/^Q([1-4])$/', (string) $item->target_quarter, $match) && $item->budgetProposal?->fiscal_year) {
            $month = 1 + (((int) $match[1] - 1) * 3);
            $dates[] = [
                'key' => 'quarter',
                'label' => "target quarter {$item->target_quarter}",
                'date' => CarbonImmutable::create((int) $item->budgetProposal->fiscal_year, $month, 1, 0, 0, 0, config('app.timezone')),
            ];
        }
        return $dates;
    }

    private function matchingPurchaseRequest(BudgetProposalItem $item, Collection $purchaseRequests): ?PurchaseRequest
    {
        $name = strtolower(trim($item->name));
        $matchesName = fn (PurchaseRequest $pr) => $pr->items->contains(function ($prItem) use ($name) {
            $candidate = strtolower(trim($prItem->name));
            return $candidate === $name || str_contains($candidate, $name) || str_contains($name, $candidate);
        });

        return $purchaseRequests->first(fn ($pr) => $pr->budget_proposal_id === $item->budget_proposal_id && $matchesName($pr))
            ?? $purchaseRequests->first(fn ($pr) => $pr->office_id === $item->budgetProposal?->office_id && $matchesName($pr));
    }

    private function lastMeaningfulActionAt(PurchaseRequest $pr): ?CarbonImmutable
    {
        return collect([
            $pr->submitted_at,
            $pr->uploaded_at,
            $pr->created_at,
            $pr->statusUpdates->max('created_at'),
            $pr->signatureLogs->max('signed_at'),
            $pr->signatureLogs->max('created_at'),
        ])->filter()->map(fn ($date) => CarbonImmutable::instance($date))->max();
    }

    private function usersForRole(string $roleName): Collection
    {
        return User::query()
            ->where('account_status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('name', $roleName))
            ->when($this->recipientUsername, fn ($query, $username) => $query->where('username', $username))
            ->get();
    }

    private function deliver(
        Collection $results,
        User $user,
        string $dedupeKey,
        string $type,
        string $title,
        string $message,
        string $actionUrl,
        array $data,
        bool $dryRun
    ): void {
        $fullKey = $user->id . ':' . $dedupeKey;
        if ($dryRun) {
            $status = PrismNotification::where('dedupe_key', $fullKey)->exists() ? 'skipped' : 'planned';
        } else {
            $status = NotificationService::sendOnce($user->id, $dedupeKey, $type, $title, $message, $actionUrl, $data, $this->sendPush)
                ? 'created' : 'skipped';
        }

        $results->push([
            'status' => $status,
            'recipient' => $user->username,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
