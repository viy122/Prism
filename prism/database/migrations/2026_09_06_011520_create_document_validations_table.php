<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_validations', function (Blueprint $table) {
            $table->id();

            // The document that was checked (PurchaseRequest, DocumentUpload,
            // AbstractOfCanvass, PurchaseOrder) …
            $table->string('validatable_type');
            $table->unsignedBigInteger('validatable_id');
            // … and the upstream document it was checked against (BudgetProposal
            // for a PR, the PR itself for a quotation, and so on).
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            // Which link in the chain this row represents — 'ppmp_pr' today,
            // 'pr_canvass' / 'canvass_aoc' / 'aoc_po' / 'po_accounting' /
            // 'po_receipt' as the remaining pairs come online.
            $table->string('pair', 40)->index();

            $table->string('verdict', 20);              // passed | failed | unreadable
            $table->unsignedTinyInteger('score')->default(0);
            // What subset was compared, e.g. the PPMP quarter the uploader picked.
            $table->string('scope', 40)->nullable();
            // Per-item results + warnings, so a failed check can explain itself
            // long after the upload without re-reading the PDF.
            $table->json('details_json')->nullable();

            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['validatable_type', 'validatable_id', 'pair'], 'doc_validations_target_pair_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_validations');
    }
};
