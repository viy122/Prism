<?php
// Read-only post-run evidence. No HTTP requests and no browser execution.
require __DIR__.'/../../../prism/vendor/autoload.php';
$app = require __DIR__.'/../../../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{BudgetProposal, PurchaseRequest, Office, FiscalYear, User};
use Illuminate\Support\Facades\DB;
$out = ['database'=>DB::connection()->getDatabaseName(), 'db_connection'=>config('database.default'),
    'config_cached'=>$app->configurationIsCached(), 'session_driver'=>config('session.driver'),
    'session_connection'=>config('session.connection'), 'checked_at'=>now()->toIso8601String()];
foreach ([72,75,78,79] as $id) {
    $p=BudgetProposal::with('items','reviews')->findOrFail($id);
    $out['proposals'][$id]=$p->toArray();
}
$out['signature_pr']=PurchaseRequest::with('signatureLogs','statusUpdates')->findOrFail(81)->toArray();
$out['fiscal_years']=FiscalYear::whereIn('year',[2025,2026])->get()->toArray();
$out['report_prs']=PurchaseRequest::with('items','abstractOfCanvass.purchaseOrder')->where('office_id',Office::where('code','SELRPT')->value('id'))->get()->toArray();
$out['accounts']=User::where('email','like','selenium.%@prism.test')->get(['id','email','account_status','office_id','vc_type','last_login_at'])->toArray();
$path=__DIR__.'/../fixtures/private/failure-state-20261004.json';
if (file_exists($path)) throw new RuntimeException('Preserving existing post-run snapshot.');
file_put_contents($path,json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode(['database'=>$out['database'],'config_cached'=>$out['config_cached'],
    'proposals'=>array_map(fn($p)=>['id'=>$p['id'],'status'=>$p['status'],'items'=>count($p['items']),'total'=>$p['total_estimated_cost'],'remarks'=>$p['remarks']],$out['proposals']),
    'signature_stage'=>$out['signature_pr']['signatory_stage'],'signature_logs'=>$out['signature_pr']['signature_logs']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
