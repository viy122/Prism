<?php

namespace App\Services;

use App\Models\AnnualProcurementPlan;
use App\Models\BudgetProposal;
use App\Models\FiscalYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FiscalYearGuard
{
    public function assertOpen(?int $year): void
    {
        if ($year && FiscalYear::whereKey($year)->where('status', 'locked')->exists()) {
            throw ValidationException::withMessages(['fiscal_year' => "FY {$year} is finalized. Planning records are read-only. An administrator must reopen it with a reason."]);
        }
    }

    public function check(Model $model): void
    {
        // Transactions retain their year but can progress after planning closes.
        if ($model instanceof \App\Models\PurchaseRequest || $model instanceof \App\Models\AbstractOfCanvass || $model instanceof \App\Models\PurchaseOrder) return;
        if ($model instanceof BudgetProposal || $model instanceof AnnualProcurementPlan) {
            $context = app(FiscalYearContext::class);
            if ($context->filterReads && $context->year && (int) $model->fiscal_year !== $context->year) {
                throw ValidationException::withMessages(['fiscal_year' => 'Select this record\'s fiscal year before editing its planning data.']);
            }
            $this->assertOpen($model->getOriginal('fiscal_year'));
            $this->assertOpen($model->fiscal_year);
            if ($model->exists && $model->isDirty('fiscal_year')) {
                throw ValidationException::withMessages(['fiscal_year' => 'Create a new record for the new fiscal year; existing records cannot be moved between years.']);
            }
            return;
        }
        foreach (['budget_proposal_id' => BudgetProposal::class, 'annual_procurement_plan_id' => AnnualProcurementPlan::class] as $key => $class) {
            foreach (array_unique([$model->getOriginal($key), $model->getAttribute($key)]) as $id) {
                if ($id) $this->assertOpen($class::withoutGlobalScope('selectedFiscalYear')->find($id)?->fiscal_year);
            }
        }
        // Planning attachments/references inherit their owner's lock.
        foreach (array_unique([$model->getOriginal('budget_proposal_item_id'), $model->getAttribute('budget_proposal_item_id')]) as $id) {
            if ($id && ($item = \App\Models\BudgetProposalItem::withoutGlobalScope('selectedFiscalYear')->find($id))) $this->check($item);
        }
        if ($model instanceof \App\Models\DocumentUpload) {
            foreach (['original', 'current'] as $version) {
                $type = $version === 'original' ? $model->getOriginal('attachable_type') : $model->attachable_type;
                $id = $version === 'original' ? $model->getOriginal('attachable_id') : $model->attachable_id;
                $class = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($type ?? '') ?? $type;
                if ($id && in_array($class, [BudgetProposal::class, AnnualProcurementPlan::class, \App\Models\BudgetProposalItem::class], true)) {
                    if ($owner = $class::withoutGlobalScope('selectedFiscalYear')->find($id)) $this->check($owner);
                }
            }
        }
    }
}
