<?php
require __DIR__.'/../prism/vendor/autoload.php';
$app = require __DIR__.'/../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$counts = [];
foreach (['users', 'purchase_requests', 'purchase_orders', 'item_receipts', 'office_assets'] as $table) {
    $counts[$table] = Illuminate\Support\Facades\Schema::hasTable($table) ? Illuminate\Support\Facades\DB::table($table)->count() : null;
}
echo json_encode($counts).PHP_EOL;
