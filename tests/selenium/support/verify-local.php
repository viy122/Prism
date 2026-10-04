<?php
/** Read-only fixture/configuration inspection. Does not dispatch application requests. */
require __DIR__.'/../../../prism/vendor/autoload.php';
$app = require __DIR__.'/../../../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{User, Office, FiscalYear, BudgetProposal, PurchaseRequest, PurchaseRequestItem, AbstractOfCanvass, PurchaseOrder, PrismNotification};
use Illuminate\Support\Facades\{DB, Hash, Storage};

$root = dirname(__DIR__);
$config = json_decode(file_get_contents($root.'/workflow.local.json'),true,512,JSON_THROW_ON_ERROR);
$manifest = json_decode(file_get_contents($root.'/fixtures/private/prepared-manifest.json'),true,512,JSON_THROW_ON_ERROR);
$passwords = json_decode(file_get_contents($root.'/fixtures/private/credentials.json'),true,512,JSON_THROW_ON_ERROR);
$checks = [];
function checkFixture(bool $condition, string $name): void {
    global $checks;
    if (!$condition) throw new RuntimeException('Fixture preflight failed: '.$name);
    $checks[] = $name;
}
checkFixture(app()->environment('local') && DB::connection()->getDatabaseName() === $manifest['database'],'local database identity');
foreach ($config['accounts'] as $key=>$account) {
    $spec = $manifest['accounts'][$key];
    $u = User::with('roles','officeAssignments')->findOrFail($spec['id']);
    checkFixture($u->email === $account['email'] && $u->account_status === 'active',"$key active identity");
    checkFixture(Hash::check($passwords[$account['password_env']],$u->password),"$key prepared password hash");
    checkFixture($u->roles->pluck('id')->all() === [$spec['role_id']],"$key exact role");
    checkFixture((int)$u->office_id === $spec['office_id'] && $u->officeAssignments->contains('id',$spec['office_id']),"$key office assignment");
    checkFixture($u->vc_type === $spec['vc_type'],"$key signer subtype");
}
foreach ([2025,2026] as $year) checkFixture(FiscalYear::findOrFail($year)->status === 'open',"FY $year open");
foreach ($manifest['proposals'] as $key=>$spec) {
    $p = BudgetProposal::with('items.sourceFiles','items.marketReferences')->findOrFail($spec['id']);
    checkFixture($p->title === $spec['title'] && $p->status === $spec['status'] && $p->fiscal_year === $spec['year'],"$key proposal state");
    checkFixture($p->items->count() === $spec['item_count'] && (float)$p->items->sum('estimated_total_cost') === (float)$spec['total'],"$key independent items/total");
    foreach ($p->items as $item) {
        $supported = $item->sourceFiles->count() > 0 || $item->marketReferences->where('is_selected',true)->count() > 0;
        checkFixture($supported === ($key !== 'missing_sources'),"$key item {$item->id} source prerequisite");
        foreach ($item->sourceFiles as $doc) checkFixture(Storage::disk('public')->exists($doc->file_path),"$key source file exists");
    }
}
foreach ($manifest['prs'] as $key=>$spec) {
    $p = PurchaseRequest::findOrFail($spec['id']);
    checkFixture($p->number === $spec['number'] && $p->signatory_stage === $spec['stage'] && (int)$p->office_id === $spec['office_id'],"$key PR state");
    checkFixture($p->items()->whereKey($spec['item_id'])->exists() && (float)$p->total_amount === 2500.0,"$key PR items/amount");
    checkFixture(Storage::disk('public')->exists($p->file_path),"$key PR document exists");
}
foreach ($manifest['aocs'] as $key=>$spec) {
    $a = AbstractOfCanvass::findOrFail($spec['id']);
    checkFixture($a->code === $spec['code'] && (int)$a->purchase_request_id === $spec['pr_id'] && $a->signatory_stage === 'fully_signed',"$key AOC state");
}
foreach ($manifest['pos'] as $key=>$spec) {
    $p = PurchaseOrder::findOrFail($spec['id']);
    checkFixture($p->po_number === $spec['number'] && (int)$p->abstract_of_canvass_id === $spec['aoc_id'] && $p->status === $spec['status'],"$key PO state");
}
$c = $config['cases'];
checkFixture(!PurchaseRequest::where('number',$c['create_pr']['number'])->exists(),'FC-19 unused PR number');
checkFixture(!PurchaseRequest::where('budget_proposal_id',$c['create_pr']['proposal_id'])->exists(),'FC-19 PPMP has no PR');
$extractionProposal = BudgetProposal::findOrFail($c['create_pr']['proposal_id']);
foreach ($extractionProposal->items as $item) {
    checkFixture(!PurchaseRequestItem::where('name',$item->name)->whereHas('purchaseRequest',fn($q)=>$q->where('office_id',$extractionProposal->office_id)->where('fiscal_year',2026))->exists(),'FC-19 item has no legacy office/year match');
    checkFixture($item->name === $c['create_pr']['expected_items'][0]['name'],'FC-19 item name matches independent PDF specification');
}
$aocPr = PurchaseRequest::findOrFail($c['create_aoc']['pr_id']);
checkFixture($aocPr->signatory_stage === 'fully_signed' && $aocPr->canvassing_stage === 'completed' && !$aocPr->abstractOfCanvass,'FC-20 eligibility');
checkFixture($aocPr->canvassDocuments()->count() === 2,'FC-20 quotations');
checkFixture(!AbstractOfCanvass::findOrFail($c['create_po']['aoc_id'])->purchaseOrder,'FC-21 no existing PO');
foreach (['receiving','record_receipt'] as $key) {
    $item = PurchaseRequestItem::findOrFail($c[$key]['item_id']);
    checkFixture((float)$item->quantity === 10.0 && $item->receipts()->count() === 0,"$key ten unreceived units");
    $po = $item->purchaseRequest->abstractOfCanvass->purchaseOrder;
    checkFixture($po->procured_on->format('Y-m-d') <= '2026-10-03' && $po->signatory_stage === 'fully_signed',"$key eligible receipt dates/stage");
}
$n = PrismNotification::findOrFail($c['notification']['id']);
checkFixture($n->read_at === null && $n->title === $c['notification']['title'],'FC-30 unread fixture');
checkFixture(PrismNotification::where('user_id',$n->user_id)->whereNull('read_at')->count() === $c['notification']['unread_before'],'FC-30 exact unread count');
$officeId = $manifest['accounts']['office_head']['office_id'];
foreach (['pr'=>PurchaseRequest::class,'aoc'=>AbstractOfCanvass::class,'po'=>PurchaseOrder::class] as $key=>$class) {
    $query = $class::query();
    if ($key === 'pr') $query->where('office_id',$officeId)->where('fiscal_year',2026);
    elseif ($key === 'aoc') $query->whereHas('purchaseRequest',fn($q)=>$q->where('office_id',$officeId)->where('fiscal_year',2026));
    else $query->whereHas('abstractOfCanvass.purchaseRequest',fn($q)=>$q->where('office_id',$officeId)->where('fiscal_year',2026));
    $ids = $query->pluck('id')->all(); sort($ids); $expected = $c[$key]['office_ids']; sort($expected);
    checkFixture($ids === $expected,"$key exact prepared office filter IDs");
}
// Independent report arithmetic from the explicit fixture specification, not rendered HTML.
foreach ([2026=>4,2025=>2] as $year=>$count) {
    $p = BudgetProposal::findOrFail($manifest['proposals']['report_'.$year]['id']);
    checkFixture($p->items()->where('target_quarter','Q4')->count() === $count,"FC-28/29 $year Q4 targets");
}
checkFixture(PurchaseOrder::findOrFail($manifest['pos']['REPORT-PAID']['id'])->status === 'paid','FC-28 one matched paid PR');
// Verify every configured local route exists without invoking its controller.
$walk = function ($node) use (&$walk,$app) {
    foreach ($node as $key=>$value) {
        if (is_array($value)) $walk($value);
        elseif (is_string($value) && ($key === 'path' || str_ends_with($key,'_path') || $key === 'destination')) {
            $route = $app['router']->getRoutes()->match(Illuminate\Http\Request::create($value,'GET'));
            checkFixture($route !== null,'route '.$value);
        }
    }
};
$walk($config);
checkFixture(class_exists(Smalot\PdfParser\Parser::class),'PDF extraction dependency available');
$text = (new Smalot\PdfParser\Parser())->parseFile($root.'/'.$c['create_pr']['pdf'])->getText();
foreach ([$c['create_pr']['number'],'Bond Paper A4','2,500.00','SELTEST','2026'] as $part) checkFixture(str_contains($text,$part),'PDF contains '.$part);
checkFixture(!str_contains(json_encode($config),'REPLACE'),'no configuration placeholders');
$out = ['kind'=>'fixture_preflight','functional_tests_executed'=>false,'checked_at'=>now()->toIso8601String(),'checks'=>$checks];
file_put_contents($root.'/fixtures/private/preflight.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo count($checks).' configuration/fixture checks completed. No functional tests executed.'.PHP_EOL;
