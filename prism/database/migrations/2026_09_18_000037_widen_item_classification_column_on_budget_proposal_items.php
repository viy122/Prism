<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * item_classification was created as varchar(20) even though storeItem()/
 * updateItem() validate it up to max:50 (the free-text "Other" box on the
 * Classification select) — anything between 21-50 chars there silently
 * 500'd the whole item-add request with a raw SQLSTATE 22001 truncation
 * error instead of a clean validation message. Widen the column to match
 * what was always being validated.
 *
 * Raw SQL rather than Schema::table()->change() — doctrine/dbal (required
 * for column modification via the schema builder) isn't installed here.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE budget_proposal_items MODIFY item_classification VARCHAR(50) NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite has no real VARCHAR length limit to begin with — nothing to change.
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE budget_proposal_items MODIFY item_classification VARCHAR(20) NULL');
        }
    }
};
