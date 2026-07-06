<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetWorkflowData extends Command
{
    protected $signature   = 'prism:reset-workflow';
    protected $description = 'Truncate all PRISM workflow tables (proposals, PRs, etc.) while keeping users.';

    public function handle(): int
    {
        $tables = [
            'po_signature_logs',
            'purchase_orders',
            'aoc_signature_logs',
            'abstract_of_canvasses',
            'pr_signature_logs',
            'procurement_status_updates',
            'purchase_request_items',
            'purchase_requests',
            'annual_procurement_plan_items',
            'annual_procurement_plans',
            'market_scoping_references',
            'budget_proposal_reviews',
            'budget_proposal_items',
            'budget_proposals',
            'personnel_budget_items',
            'document_uploads',
            'prism_notifications',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->line("  Truncated: <fg=yellow>$table</>");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info('Workflow data cleared. Users, roles, offices, and permissions are intact.');

        return self::SUCCESS;
    }
}
