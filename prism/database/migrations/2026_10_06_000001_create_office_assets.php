<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('office_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_receipt_id')->constrained()->restrictOnDelete();
            $table->foreignId('office_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->string('serial_number')->nullable();
            $table->string('property_number')->nullable()->unique();
            $table->string('location')->nullable();
            $table->string('accountable_person')->nullable();
            $table->date('assigned_on')->nullable();
            $table->string('usage_status')->default('not_in_use');
            $table->date('usage_started_on')->nullable();
            $table->string('warranty_coverage')->default('not_recorded');
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->text('warranty_notes')->nullable();
            $table->string('warranty_path')->nullable();
            $table->string('warranty_filename')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->uuid('registration_token');
            $table->unsignedInteger('registration_unit');
            $table->timestamps();
            $table->unique(['registration_token', 'registration_unit']);
            $table->index(['office_id', 'usage_status']);
        });
        Schema::create('office_asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_asset_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_office_id')->constrained('offices')->restrictOnDelete();
            $table->foreignId('to_office_id')->constrained('offices')->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->text('reason');
            $table->text('resolution_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_asset_transfers');
        Schema::dropIfExists('office_assets');
    }
};
