<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MVP workflow for Office Head / Dean:
     * proposal header, proposal items, market scoping references, and review timeline.
     *
     * School-specific form fields are intentionally kept in JSON columns until official templates are available.
     */
    public function up(): void
    {
        Schema::create('budget_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('fiscal_year')->index();
            $table->decimal('total_estimated_cost', 15, 2)->default(0);
            $table->decimal('approved_budget', 15, 2)->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('remarks')->nullable();
            $table->json('form_data_json')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['office_id', 'status']);
            $table->index(['fiscal_year', 'status']);
        });

        Schema::create('budget_proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable()->index();
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->decimal('estimated_unit_cost', 15, 2);
            $table->decimal('estimated_total_cost', 15, 2);
            $table->decimal('approved_budget', 15, 2)->nullable();
            $table->string('target_quarter')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->text('remarks')->nullable();
            $table->json('specifications_json')->nullable();
            $table->timestamps();

            $table->index(['budget_proposal_id', 'status']);
        });

        Schema::create('market_scoping_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_proposal_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('selected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supplier_name')->index();
            $table->string('source_type')->nullable()->index();
            $table->string('source_url', 2048)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('PHP');
            $table->date('date_accessed')->nullable();
            $table->string('match_status')->nullable()->index();
            $table->string('status')->default('needs_review')->index();
            $table->boolean('is_selected')->default(false)->index();
            $table->text('remarks')->nullable();
            $table->json('specifications_json')->nullable();
            $table->json('source_snapshot_json')->nullable();
            $table->json('ai_analysis_json')->nullable();
            $table->timestamp('selected_at')->nullable();
            $table->timestamps();

            $table->index(['budget_proposal_item_id', 'is_selected'], 'market_ref_item_selected_idx');
        });

        Schema::create('budget_proposal_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable()->index();
            $table->text('remarks')->nullable();
            $table->json('review_data_json')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['budget_proposal_id', 'status_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_proposal_reviews');
        Schema::dropIfExists('market_scoping_references');
        Schema::dropIfExists('budget_proposal_items');
        Schema::dropIfExists('budget_proposals');
    }
};
