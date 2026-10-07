<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\DeliveryStatusService;
use Illuminate\Console\Command;

class SyncDeliveryStatuses extends Command
{
    protected $signature = 'procurement:sync-delivery-statuses {--dry-run : Preview changes without writing}';

    protected $description = 'Reconcile PO delivery status with recorded item receipts, preserving payment statuses';

    public function handle(DeliveryStatusService $delivery): int
    {
        $changed = 0;
        $reviews = 0;
        $dryRun = (bool) $this->option('dry-run');
        PurchaseOrder::whereHas('itemReceipts')->orderBy('id')->chunkById(100, function ($orders) use ($delivery, $dryRun, &$changed, &$reviews) {
            foreach ($orders as $po) {
                $result = $delivery->sync($po->id, dryRun: $dryRun);
                if ($result['from'] !== $result['to']) {
                    $changed++;
                    $this->line("{$result['number']}: {$result['from']} -> {$result['to']}");
                }
                if ($result['review']) {
                    $reviews++;
                    $this->warn("{$result['number']}: Review required: receipts incomplete; payment status preserved.");
                }
            }
        });
        $this->info(($dryRun ? 'DRY RUN' : 'SYNC COMPLETE').": {$changed} delivery updates; {$reviews} requiring review.");
        return self::SUCCESS;
    }
}
