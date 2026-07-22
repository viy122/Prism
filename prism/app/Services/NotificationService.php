<?php

namespace App\Services;

use App\Models\BudgetProposal;
use App\Models\PrismNotification;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    // ── Core senders ─────────────────────────────────────────────────────────

    public static function send(
        int    $userId,
        string $type,
        string $title,
        string $message,
        string $actionUrl = '',
        array  $data = []
    ): void {
        PrismNotification::create([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'action_url' => $actionUrl,
            'data_json'  => $data,
        ]);

        self::sendExpoPush($userId, $title, $message, $data);
    }

    /**
     * Fire-and-forget Expo push. Failure never blocks the in-app notification.
     */
    private static function sendExpoPush(int $userId, string $title, string $message, array $data = []): void
    {
        $token = User::find($userId)?->expo_push_token;

        if (!$token || !str_starts_with($token, 'ExponentPushToken')) {
            return;
        }

        try {
            Http::timeout(5)->post('https://exp.host/--/api/v2/push/send', [
                'to'    => $token,
                'title' => $title,
                'body'  => $message,
                'sound' => 'default',
                'data'  => $data,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Expo push failed for user ' . $userId . ': ' . $e->getMessage());
        }
    }

    public static function sendToRole(
        string $roleName,
        string $type,
        string $title,
        string $message,
        string $actionUrl = '',
        array  $data = []
    ): void {
        User::whereHas('roles', fn ($q) => $q->where('name', $roleName))
            ->get()
            ->each(fn ($user) => self::send($user->id, $type, $title, $message, $actionUrl, $data));
    }

    // ── Budget Proposal events ────────────────────────────────────────────────

    /**
     * Triggered when: Office Head submits a proposal.
     * Notifies: Finance Office — they need to review it.
     */
    public static function proposalSubmitted(BudgetProposal $proposal): void
    {
        $office = $proposal->office?->name ?? 'an office';
        $year   = $proposal->fiscal_year;

        self::sendToRole(
            'Finance Office',
            'proposal_submitted',
            'New PPMP for Review',
            "{$office} submitted their FY {$year} PPMP. Please review and approve.",
            route('finance-office.proposal-review'),
        );
    }

    /**
     * Triggered when: Finance Office endorses a proposal.
     * Notifies: Chancellor — next approver.
     *           Office Head — their proposal moved forward.
     */
    public static function proposalEndorsed(BudgetProposal $proposal): void
    {
        $office = $proposal->office?->name ?? 'An office';
        $year   = $proposal->fiscal_year;

        self::sendToRole(
            'Chancellor',
            'proposal_endorsed',
            'PPMP Ready for Approval',
            "{$office}'s FY {$year} PPMP has been approved by the Finance Office and is ready for your approval.",
            route('chancellor.budget-approval'),
        );

        if ($proposal->submitted_by_user_id) {
            self::send(
                $proposal->submitted_by_user_id,
                'proposal_endorsed',
                'Your Proposal Has Been Endorsed',
                "Your FY {$year} PPMP has been approved by the Finance Office and forwarded to the Chancellor for approval.",
                route('office-head.my-proposals'),
            );
        }
    }

    /**
     * Triggered when: Finance Office returns a proposal.
     * Notifies: Office Head — they need to revise and resubmit.
     */
    public static function proposalReturnedByFinance(BudgetProposal $proposal, string $remarks = ''): void
    {
        $year = $proposal->fiscal_year;

        if ($proposal->submitted_by_user_id) {
            $remarkNote = $remarks ? " Remarks: \"{$remarks}\"" : '';
            self::send(
                $proposal->submitted_by_user_id,
                'proposal_returned',
                'Proposal Returned for Revision',
                "Your FY {$year} PPMP was returned by the Finance Office.{$remarkNote} Please revise and resubmit.",
                route('office-head.budget-proposal'),
            );
        }
    }

    /**
     * Triggered when: Chancellor approves a proposal.
     * Notifies: Office Head — final approval received.
     *           Vice Chancellor — for awareness.
     */
    public static function proposalApproved(BudgetProposal $proposal): void
    {
        $office = $proposal->office?->name ?? 'An office';
        $year   = $proposal->fiscal_year;

        if ($proposal->submitted_by_user_id) {
            self::send(
                $proposal->submitted_by_user_id,
                'proposal_approved',
                'PPMP Approved',
                "Congratulations! Your FY {$year} PPMP has been approved by the Chancellor.",
                route('office-head.my-proposals'),
            );
        }

        self::sendToRole(
            'Vice Chancellor',
            'proposal_approved',
            'PPMP Approved',
            "{$office}'s FY {$year} PPMP has been approved by the Chancellor.",
            route('vice-chancellor.division-procurement-status'),
        );
    }

    /**
     * Triggered when: Chancellor returns a proposal.
     * Notifies: Finance Office — for awareness.
     *           Office Head — needs revision.
     */
    public static function proposalReturnedByChancellor(BudgetProposal $proposal, string $remarks = ''): void
    {
        $office = $proposal->office?->name ?? 'An office';
        $year   = $proposal->fiscal_year;

        self::sendToRole(
            'Finance Office',
            'proposal_returned',
            'Proposal Returned by Chancellor',
            "{$office}'s FY {$year} PPMP was returned by the Chancellor for revision." . ($remarks ? " Remarks: \"{$remarks}\"" : ''),
            route('finance-office.proposal-review'),
        );

        if ($proposal->submitted_by_user_id) {
            $remarkNote = $remarks ? " Remarks: \"{$remarks}\"" : '';
            self::send(
                $proposal->submitted_by_user_id,
                'proposal_returned',
                'Proposal Returned by Chancellor',
                "Your FY {$year} PPMP was returned by the Chancellor.{$remarkNote} Please coordinate with the Finance Office.",
                route('office-head.my-proposals'),
            );
        }
    }

    // ── Signatory queue events ────────────────────────────────────────────────

    /**
     * Triggered when: a PR/AOC/PO lands on a stage owned by a signatory role.
     * Notifies: every user holding that role — a document awaits their signature.
     */
    public static function documentAwaitingSignature($doc, string $roleCode): void
    {
        $roleNames = [
            'bac'               => 'BAC',
            'vice-chancellor'   => 'Vice Chancellor',
            'chancellor'        => 'Chancellor',
            'accounting-office' => 'Accounting Office',
        ];
        $queueRoutes = [
            'bac'               => 'bac.for-my-signature',
            'vice-chancellor'   => 'vice-chancellor.for-my-signature',
            'chancellor'        => 'chancellor.for-my-signature',
            'accounting-office' => 'accounting-office.for-my-signature',
        ];

        if (!isset($roleNames[$roleCode])) {
            return;
        }

        $prefix = $doc::SIGNATORY_DOC_PREFIX;
        $number = $doc->number ?? $doc->code ?? $doc->po_number ?? ($prefix . '-' . str_pad($doc->id, 4, '0', STR_PAD_LEFT));
        $stage  = $doc->stageMetaFor($doc->signatory_stage)['label'] ?? '';

        self::sendToRole(
            $roleNames[$roleCode],
            'awaiting_signature',
            "{$prefix} Awaiting Your Signature",
            "{$number} is now at \"{$stage}\" and needs your action.",
            route($queueRoutes[$roleCode]),
        );
    }

    // ── Purchase Request events ───────────────────────────────────────────────

    /**
     * Triggered when: Office Head uploads a PR document.
     * Notifies: Procurement Office — new PR to process.
     */
    public static function prUploaded(PurchaseRequest $pr): void
    {
        $office = $pr->office?->name ?? 'An office';

        self::sendToRole(
            'Procurement Office',
            'pr_uploaded',
            'New Purchase Request Uploaded',
            "{$pr->number} from {$office} has been uploaded and is ready for processing.",
            route('procurement-office.purchase-request-management'),
        );
    }

    /**
     * Triggered when: Procurement Office updates a PR status.
     * Notifies: Office Head of the PR's office.
     */
    public static function prStatusUpdated(PurchaseRequest $pr): void
    {
        if (!$pr->office_id) {
            return;
        }

        $officeHead = User::where('office_id', $pr->office_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Office Head / Dean'))
            ->first();

        if ($officeHead) {
            $statusLabel = ucwords(str_replace('_', ' ', $pr->status));
            self::send(
                $officeHead->id,
                'pr_status_updated',
                'Purchase Request Status Updated',
                "PR {$pr->number} has been updated to \"{$statusLabel}\" by the Procurement Office.",
                route('office-head.purchase-requests'),
            );
        }
    }
}
