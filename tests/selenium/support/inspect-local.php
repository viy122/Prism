<?php
// Read-only inspection used to prepare local Selenium fixtures. No HTTP/browser tests.
require __DIR__.'/../../../prism/vendor/autoload.php';
$app = require __DIR__.'/../../../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
$tables = ['campuses','roles','offices','users','role_user','office_user_assignments','fiscal_years',
    'budget_proposals','budget_proposal_items','budget_proposal_reviews','document_uploads',
    'annual_procurement_plans','annual_procurement_plan_items','purchase_requests',
    'purchase_request_items',(new App\Models\AbstractOfCanvass())->getTable(),'purchase_orders','item_receipts','prism_notifications',
    'permissions','permission_role','market_scoping_references','pr_signature_logs','aoc_signature_logs','po_signature_logs'];
$out = ['environment'=>app()->environment(), 'database'=>DB::connection()->getDatabaseName(),
    'roles'=>DB::table('roles')->select('id','code','name')->get(),
    'offices'=>DB::table('offices')->select('id','code','name','parent_office_id','office_type')->get(),
    'fiscal_years'=>DB::table('fiscal_years')->get()];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) $out['schema'][$table] = DB::select('SHOW COLUMNS FROM `'.$table.'`');
}
$path = __DIR__.'/../fixtures/private/schema-inspection.json';
if (!is_dir(dirname($path))) mkdir(dirname($path),0777,true);
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode(array_diff_key($out,['schema'=>true]), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\nSchema saved locally.\n";
