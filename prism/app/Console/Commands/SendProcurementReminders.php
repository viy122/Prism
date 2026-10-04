<?php

namespace App\Console\Commands;

use App\Services\ProcurementReminderService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendProcurementReminders extends Command
{
    protected $signature = 'procurement:send-reminders
        {--date= : Evaluate reminders as of this date (YYYY-MM-DD)}
        {--dry-run : Show candidates without creating notifications}
        {--include-weekly : Include the Monday digest even when the selected date is not Monday}
        {--recipient= : Limit a test run to one username}
        {--no-push : Create in-app notifications without sending Expo push notifications}';

    protected $description = 'Send procurement target-date, weekly, and inactive purchase-request reminders';

    public function handle(ProcurementReminderService $reminders): int
    {
        try {
            $date = $this->option('date')
                ? CarbonImmutable::parse($this->option('date'), config('app.timezone'))
                : CarbonImmutable::now(config('app.timezone'));
        } catch (\Throwable) {
            $this->error('Invalid --date value. Use YYYY-MM-DD.');
            return self::FAILURE;
        }

        $summary = $reminders->run(
            $date,
            (bool) $this->option('dry-run'),
            (bool) $this->option('include-weekly'),
            $this->option('recipient') ?: null,
            !(bool) $this->option('no-push'),
        );

        $this->info(($summary['dryRun'] ? 'DRY RUN' : 'REMINDER RUN') . " for {$summary['date']}");
        $this->line("Created: {$summary['created']} | Planned: {$summary['planned']} | Skipped duplicates: {$summary['skipped']}");
        $this->newLine();
        $this->table(
            ['Status', 'Recipient', 'Type', 'Title', 'Message'],
            collect($summary['results'])->map(fn ($row) => [
                $row['status'], $row['recipient'], $row['type'], $row['title'], $row['message'],
            ])->all()
        );

        return self::SUCCESS;
    }
}
