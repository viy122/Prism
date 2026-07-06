<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aoc_signature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abstract_of_canvass_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('signatory_number'); // 1–5
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 10); // 'signed' | 'returned'
            $table->text('remarks')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->index(['abstract_of_canvass_id', 'signatory_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aoc_signature_logs');
    }
};
