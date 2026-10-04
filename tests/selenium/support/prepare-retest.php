<?php
/** Prepare/check only the five failed cases; never runs HTTP or Selenium. */
require __DIR__.'/../../../prism/vendor/autoload.php';
$app = require __DIR__.'/../../../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{BudgetProposal, PurchaseRequest, PurchaseOrder, User, FiscalYear};
use Illuminate\Support\Facades\{DB, Hash, Storage};

function requireFixture(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}
$root = dirname(__DIR__);
$target = $root.'/workflow.retest.local.json';
$manifestPath = $root.'/fixtures/private/retest-manifest.json';
requireFixture(app()->environment('local') && config('database.default') === 'mysql'
    && DB::connection()->getDatabaseName() === 'prism_db'
    && in_array(config('database.connections.mysql.host'),['127.0.0.1','localhost'],true), 'Requires the prepared local MySQL database.');
$checkOnly = in_array('--check', $argv, true);
if (!$checkOnly) {
    requireFixture(!file_exists($target) && !file_exists($manifestPath),'Rerun fixtures already exist; refusing to reset evidence.');
    $original = json_decode(file_get_contents($root.'/workflow.local.json'),true,512,JSON_THROW_ON_ERROR);
    $config = array_intersect_key($original,array_flip(['base_url','timeout','accounts']));
    $config['cases'] = ['report_year'=>$original['cases']['report_year']];
    $manifest = ['prepared_at'=>now()->toIso8601String(),'purpose'=>'FC-03/07/09/23/29 only',
        'source_execution'=>'artifacts/20261004T020932.038561Z','functional_rerun'=>'NOT EXECUTED',
        'original_records_preserved'=>[72,75,81],'accounts_unchanged'=>true];
    DB::transaction(function () use (&$config,&$manifest,$original) {
        foreach (['draft_crud'=>['FC07',72,'draft'],'finance_return'=>['FC09',75,'submitted']] as $key=>[$label,$oldId,$status]) {
            $source=BudgetProposal::with('items.documents')->findOrFail($oldId);
            requireFixture($source->office->code === 'SELTEST' && str_starts_with($source->code,'SEL-20261004-'), 'Source must be a disposable proposal.');
            requireFixture($source->items->count() === 1 && (float)$source->total_estimated_cost === 2500.0,'Unexpected source proposal baseline.');
            $p=$source->replicate();
            $p->code='SEL-20261004-R1-'.$label;
            $p->title='TEST Selenium rerun '.$label;
            $p->status=$status;
            $p->remarks=null;
            $p->reviewed_at=$p->approved_at=null;
            $p->reviewed_by_user_id=$p->approved_by_user_id=null;
            $p->submitted_at=$status === 'submitted' ? now() : null;
            $p->submitted_by_user_id=$status === 'submitted' ? $source->created_by_user_id : null;
            $p->approved_budget=null;
            $p->save();
            foreach ($source->items as $item) {
                $copy=$item->replicate(); $copy->budget_proposal_id=$p->id;
                $copy->finance_ok=$copy->finance_remark=null; $copy->save();
                foreach ($item->documents as $doc) {
                    $newDoc=$doc->replicate(); $newDoc->attachable_id=$copy->id; $newDoc->save();
                }
            }
            $path='/office-head/budget-proposal?proposal='.$p->id.'&year=2026';
            if ($key === 'draft_crud') $config['cases'][$key]=['path'=>$path,'proposal_id'=>$p->id,'title'=>$p->title,'before_total'=>2500];
            else $config['cases'][$key]=['proposal_id'=>$p->id,'proposal_code'=>$p->code,'title'=>$p->title,'office_path'=>$path,
                'finance_path'=>'/finance-office/proposal-review/'.$p->id.'?year=2026','timeline_path'=>'/office-head/my-proposals?year=2026'];
            $manifest[$key]=['id'=>$p->id,'code'=>$p->code,'status'=>$status,'item_count'=>1,'total'=>2500];
        }
        $source=PurchaseRequest::with('items')->findOrFail(81);
        requireFixture($source->number === 'SEL-20261004-PR-SIGNATURE-HANDOFF' && $source->office->code === 'SELTEST','Source must be disposable signature PR.');
        $pr=$source->replicate();
        $pr->number='SEL-20261004-R1-PR-FC23'; $pr->title='TEST Selenium rerun FC23';
        $pr->signatory_stage='at_end_user'; $pr->canvassing_stage='not_started'; $pr->third_signer=null;
        $pr->remarks='Disposable FC-23 rerun starting state'; $pr->submitted_at=now(); $pr->save();
        foreach ($source->items as $item) { $copy=$item->replicate(); $copy->purchase_request_id=$pr->id; $copy->save(); }
        $config['cases']['signature_handoff']=array_replace($original['cases']['signature_handoff'],[
            'document_key'=>'pr-'.$pr->id,'number'=>$pr->number,'before_log_count'=>0]);
        $manifest['signature_handoff']=['id'=>$pr->id,'number'=>$pr->number,'stage'=>'at_end_user','log_count'=>0];
    });
    file_put_contents($target,json_encode($config,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    file_put_contents($manifestPath,json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}
$config=json_decode(file_get_contents($target),true,512,JSON_THROW_ON_ERROR);
$manifest=json_decode(file_get_contents($manifestPath),true,512,JSON_THROW_ON_ERROR);
$passwords=json_decode(file_get_contents($root.'/fixtures/private/credentials.json'),true,512,JSON_THROW_ON_ERROR);
requireFixture($app->configurationIsCached(),'Cache the verified local configuration before rerunning FC-03.');
foreach (['office_head','finance','next_signer','procurement'] as $key) {
    $a=$config['accounts'][$key]; $u=User::where('email',$a['email'])->firstOrFail();
    requireFixture($u->account_status === 'active' && Hash::check($passwords[$a['password_env']],$u->password),"$key prepared credentials");
}
foreach (['draft_crud'=>'draft','finance_return'=>'submitted'] as $key=>$status) {
    $p=BudgetProposal::with('items.sourceFiles','reviews')->findOrFail($config['cases'][$key]['proposal_id']);
    requireFixture($p->status === $status && $p->reviews->count() === 0 && $p->remarks === null,"$key untouched starting state");
    requireFixture($p->items->count() === 1 && (float)$p->items->sum('estimated_total_cost') === 2500.0,"$key independent item baseline");
    foreach ($p->items as $item) foreach ($item->sourceFiles as $file) requireFixture(Storage::disk('public')->exists($file->file_path),'Source PDF missing');
}
$pr=PurchaseRequest::findOrFail($manifest['signature_handoff']['id']);
requireFixture($pr->signatory_stage === 'at_end_user' && $pr->signatureLogs()->count() === 0 && !$pr->abstractOfCanvass,'FC-23 untouched starting state');
foreach ([2026=>[78,4],2025=>[79,2]] as $year=>[$id,$count]) {
    requireFixture(FiscalYear::findOrFail($year)->status === 'open',"FY $year must be open");
    $p=BudgetProposal::with('items','office')->findOrFail($id);
    requireFixture($p->office->code === 'SELRPT' && $p->status === 'approved' && $p->fiscal_year === $year
        && $p->items->where('target_quarter','Q4')->count() === $count,"FY $year report baseline");
}
requireFixture(PurchaseOrder::findOrFail(38)->status === 'paid','Report matched PR remains paid');
echo json_encode(['configuration_cached'=>true,'fixtures'=>$manifest,'preparation_check'=>'ready','selenium_executed'=>false],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
