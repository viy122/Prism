<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the generic signatory chains with the university's real chains.
 *
 * PR:  draft → end user → vice chancellor → 3rd/4th (accounting + VC, flexible
 *      order recorded in purchase_requests.third_signer) → chancellor → fully signed
 * AOC: draft → end user → BAC member → BAC vice chair → BAC chair
 *      → VC countersign → internal audit (routing) → chancellor → fully signed
 * PO:  draft → budget ORS (routing) → accounting → VC review (routing)
 *      → internal audit (routing) → chancellor → supplier → fully signed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('third_signer', 20)->nullable()->after('signatory_stage')
                ->comment('Who signed 3rd: accounting | vice_chancellor');
        });

        // Map legacy generic stage keys onto the new chains.
        $this->remap('purchase_requests', [
            'at_signatory_2' => 'at_vice_chancellor',
            'at_signatory_3' => 'at_third_sign',
            'at_signatory_4' => 'at_fourth_sign',
        ]);
        // Legacy rows already past the 3rd slot get a deterministic default.
        DB::table('purchase_requests')
            ->whereIn('signatory_stage', ['at_third_sign', 'at_fourth_sign'])
            ->update(['third_signer' => 'accounting']);

        $this->remap('abstract_of_canvasses', [
            'at_signatory_2' => 'at_bac_member',
            'at_signatory_3' => 'at_bac_vice_chair',
            'at_signatory_4' => 'at_bac_chair',
        ]);

        $this->remap('purchase_orders', [
            'at_end_user'    => 'at_budget_ors',
            'at_signatory_2' => 'at_accounting',
            'at_signatory_3' => 'at_vc_review',
            'at_signatory_4' => 'at_audit',
        ]);
    }

    public function down(): void
    {
        $this->remap('purchase_requests', [
            'at_vice_chancellor' => 'at_signatory_2',
            'at_third_sign'      => 'at_signatory_3',
            'at_fourth_sign'     => 'at_signatory_4',
        ]);

        $this->remap('abstract_of_canvasses', [
            'at_bac_member'     => 'at_signatory_2',
            'at_bac_vice_chair' => 'at_signatory_3',
            'at_bac_chair'      => 'at_signatory_4',
            'at_vc_countersign' => 'at_signatory_4',
            'at_audit'          => 'at_chancellor',
        ]);

        $this->remap('purchase_orders', [
            'at_budget_ors' => 'at_end_user',
            'at_accounting' => 'at_signatory_2',
            'at_vc_review'  => 'at_signatory_3',
            'at_audit'      => 'at_signatory_4',
            'at_supplier'   => 'at_chancellor',
        ]);

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('third_signer');
        });
    }

    private function remap(string $table, array $map): void
    {
        foreach ($map as $old => $new) {
            DB::table($table)->where('signatory_stage', $old)->update(['signatory_stage' => $new]);
        }
    }
};
