<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['pr_signature_logs', 'aoc_signature_logs', 'po_signature_logs'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('remarks');
                $table->string('blurred_photo_path')->nullable()->after('photo_path');
                $table->json('signature_boxes_json')->nullable()->after('blurred_photo_path');
                // detected | manual_confirmed | failed | pending
                $table->string('detection_status', 32)->nullable()->index()->after('signature_boxes_json');
                $table->timestamp('photo_uploaded_at')->nullable()->after('detection_status');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn([
                    'photo_path',
                    'blurred_photo_path',
                    'signature_boxes_json',
                    'detection_status',
                    'photo_uploaded_at',
                ]);
            });
        }
    }
};
