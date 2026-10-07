<?php
// Disposable browser-test database only. Never boots the normal application database.
$root = dirname(__DIR__, 3);
$database = $root.'/tmp/office-assets-browser-'.date('YmdHis').'.sqlite';
if (file_exists($database)) throw new RuntimeException('Refusing to replace an existing browser-test database.');
touch($database);
foreach (['APP_ENV' => 'testing', 'APP_CONFIG_CACHE' => $root.'/tmp/no-asset-ui-config.php', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database, 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'file', 'QUEUE_CONNECTION' => 'sync'] as $key => $value) {
    putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value;
}
require $root.'/prism/vendor/autoload.php';
$app = require $root.'/prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($app->configurationIsCached() || config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $database) throw new RuntimeException('Unsafe browser-test database configuration.');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
use App\Models\{AbstractOfCanvass, Campus, FiscalYear, ItemReceipt, Office, PurchaseOrder, PurchaseRequest, Role, User};
$campus = Campus::create(['code' => 'ASSETUI', 'name' => 'Disposable Asset Browser Tests']);
$office = Office::create(['campus_id' => $campus->id, 'code' => 'CICS', 'name' => 'Test Computing Office', 'office_type' => 'academic']);
$destination = Office::create(['campus_id' => $campus->id, 'code' => 'COE', 'name' => 'Test Engineering Office', 'office_type' => 'academic']);
foreach ([['office_head', 'Office Head / Dean', $office], ['asset_recipient', 'Office Head / Dean', $destination], ['procurement', 'Procurement Office', $office], ['chancellor', 'Chancellor', $office], ['vice_chancellor', 'Vice Chancellor', $office]] as [$name, $roleName, $assignedOffice]) {
    $user = User::factory()->create(['username' => $name, 'name' => 'TEST '.$name, 'email' => $name.'@asset.test', 'password' => Illuminate\Support\Facades\Hash::make('AssetBrowserTestOnly!2026'), 'office_id' => $assignedOffice->id, 'account_status' => 'active', 'vc_type' => $name === 'vice_chancellor' ? 'vcaa' : null]);
    $user->roles()->attach(Role::firstOrCreate(['name' => $roleName], ['code' => Illuminate\Support\Str::slug($roleName)]));
}
foreach ([2026, 2027] as $year) FiscalYear::firstOrCreate(['year' => $year]);
FiscalYear::where('year', 2026)->update(['is_active' => true]);
$pr = PurchaseRequest::create(['office_id' => $office->id, 'number' => 'PR-TEST-ASSET-2026-Q3', 'title' => 'TEST Laptop delivery', 'fiscal_year' => 2026, 'status' => 'submitted', 'signatory_stage' => 'fully_signed']);
$item = $pr->items()->create(['name' => 'TEST Laptop', 'quantity' => 5, 'unit' => 'unit', 'estimated_unit_cost' => 100, 'estimated_total_cost' => 500]);
$aoc = AbstractOfCanvass::create(['purchase_request_id' => $pr->id, 'code' => 'AOC-TEST-ASSET', 'signatory_stage' => 'fully_signed']);
$po = PurchaseOrder::create(['abstract_of_canvass_id' => $aoc->id, 'po_number' => 'PO-TEST-ASSET', 'supplier_name' => 'TEST Supplier', 'signatory_stage' => 'fully_signed', 'status' => 'paid', 'procured_on' => '2026-09-10', 'expected_delivery_date' => '2026-09-20']);
$receipt = ItemReceipt::create(['purchase_order_id' => $po->id, 'purchase_request_item_id' => $item->id, 'arrival_date' => '2026-09-18', 'quantity' => 3, 'received_by_name' => 'TEST Receiver', 'submission_token' => (string) Illuminate\Support\Str::uuid()]);
file_put_contents($root.'/tmp/office-assets-browser-fixture.json', json_encode(['database' => $database, 'item' => $item->id, 'receipt' => $receipt->id, 'destination' => $destination->id]));
echo "Prepared isolated SQLite browser fixture. No application records changed.\n";
