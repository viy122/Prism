<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic APP and Purchase Request tracking foundation.
     * Columns avoid official-template assumptions and keep school-specific data in JSON.
     */
    public function up(): void
    {
        Schema::create('annual_procurement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('fiscal_year')->index();
            $table->decimal('total_estimated_cost', 15, 2)->default(0);
            $table->decimal('approved_budget', 15, 2)->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('remarks')->nullable();
            $table->json('template_data_json')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fiscal_year', 'status']);
        });

        Schema::create('annual_procurement_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_procurement_plan_id');
            $table->foreignId('budget_proposal_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->decimal('estimated_unit_cost', 15, 2);
            $table->decimal('estimated_total_cost', 15, 2);
            $table->decimal('approved_budget', 15, 2)->nullable();
            $table->string('target_quarter')->nullable()->index();
            $table->string('procurement_mode')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('remarks')->nullable();
            $table->json('specifications_json')->nullable();
            $table->timestamps();

            $table->foreign('annual_procurement_plan_id', 'app_item_plan_fk')
                ->references('id')
                ->on('annual_procurement_plans')
                ->cascadeOnDelete();
            $table->index(['annual_procurement_plan_id', 'status'], 'app_item_plan_status_idx');
        });

        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_procurement_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('office_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number')->nullable()->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('fiscal_year')->nullable()->index();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->text('remarks')->nullable();
            $table->string('file_path')->nullable();
            $table->json('extracted_fields_json')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['office_id', 'status']);
        });

        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('annual_procurement_plan_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->decimal('estimated_unit_cost', 15, 2);
            $table->decimal('estimated_total_cost', 15, 2);
            $table->decimal('approved_budget', 15, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->json('specifications_json')->nullable();
            $table->timestamps();
        });

        Schema::create('procurement_status_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('annual_procurement_plan_item_id')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->index();
            $table->text('remarks')->nullable();
            $table->json('update_data_json')->nullable();
            $table->timestamps();

            $table->foreign('annual_procurement_plan_item_id', 'proc_status_app_item_fk')
                ->references('id')
                ->on('annual_procurement_plan_items')
                ->nullOnDelete();
            $table->index(['purchase_request_id', 'status'], 'proc_status_pr_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_status_updates');
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('annual_procurement_plan_items');
        Schema::dropIfExists('annual_procurement_plans');
    }
};
