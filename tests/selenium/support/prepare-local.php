<?php
/** Local fixture preparation only. Never invokes HTTP, WebDriver or pytest. */
require __DIR__.'/../../../prism/vendor/autoload.php';
$app = require __DIR__.'/../../../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, Role, Office, FiscalYear, BudgetProposal, PurchaseRequest, AbstractOfCanvass, PurchaseOrder, PrismNotification};
use Illuminate\Support\Facades\{DB, Hash, Storage};

$root = dirname(__DIR__);
$private = $root.'/fixtures/private';
if (!app()->environment(['local', 'testing']) || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('This preparer requires a LOCAL application and local database host.');
}
if (file_exists($private.'/prepared-manifest.json') || Office::whereIn('code', ['SELTEST','SELRPT'])->exists()) {
    throw new RuntimeException('Fixtures already exist. Refusing to reset records or overwrite preparation evidence.');
}
foreach ([2025, 2026] as $year) {
    if (FiscalYear::findOrFail($year)->status !== 'open') throw new RuntimeException("FY $year must be open.");
}
if (!is_dir($private)) mkdir($private, 0777, true);
$prefix = 'SEL-20261004';
$diskDir = 'selenium-fixtures/'.$prefix;
function pdfBytes(string $heading, array $lines): string {
    // Small text PDF with real selectable text, no external generator dependency.
    $stream = "BT /F1 10 Tf 40 790 Td 24 TL\n";
    foreach (array_merge([$heading], $lines) as $line) {
        $stream .= '('.str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$line).") Tj T*\n";
    }
    $stream .= "ET\n";
    $objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>', '<< /Length '.strlen($stream).">>\nstream\n".$stream.'endstream'];
    $pdf = "%PDF-1.4\n"; $offsets = [0];
    foreach ($objects as $i=>$object) { $offsets[] = strlen($pdf); $pdf .= ($i+1)." 0 obj\n$object\nendobj\n"; }
    $start = strlen($pdf); $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    foreach (array_slice($offsets,1) as $offset) $pdf .= sprintf("%010d 00000 n \n",$offset);
    return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$start\n%%EOF\n";
}
$prNumber = $prefix.'-EXTRACT';
$pdf = pdfBytes('PURCHASE REQUEST - DISPOSABLE SELENIUM TEST ONLY', [
    'PR No: '.$prNumber, 'Date: October 03, 2026',
    'Name of Project: TEST PDF paper purchase',
    'Department /Office: Selenium Test Office (SELTEST)', 'Project Location: Local Test Laboratory',
    'UNIT ITEM AND DESCRIPTION QTY UNIT COST TOTAL COST',
    'ream TEST Extraction Bond Paper A4 10 Php 250.00 Php 2,500.00', 'TOTAL COST Php 2,500.00',
    'Requested by: Selenium Office Head / TEST signature', 'Approved by: Selenium Chancellor / TEST signature',
    'Synthetic test document. No real purchase or official signature.'
]);
file_put_contents($private.'/approved-test-pr.pdf', $pdf);
$source = pdfBytes('TEST market source - fictional quotation', ['Bond Paper A4: 10 ream at PHP 250.00 = PHP 2,500.00.', 'For local Selenium fixtures only.']);
Storage::disk('public')->put($diskDir.'/market-source.pdf', $source);
Storage::disk('public')->put($diskDir.'/signed-pr.pdf', $pdf);
$credentials = [];
$config = ['base_url'=>'http://prism.test','timeout'=>30,'accounts'=>[],'cases'=>[]];
$manifest = ['prepared_at'=>now()->toIso8601String(), 'database'=>DB::connection()->getDatabaseName(), 'prefix'=>$prefix,
    'execution'=>'NOT RUN', 'fiscal_years'=>[2026,2025], 'fixture_files'=>[$diskDir.'/market-source.pdf',$diskDir.'/signed-pr.pdf'],
    'expected_data_source'=>'Synthetic fixture specification in support/prepare-local.php; never browser output.'];

DB::transaction(function () use (&$config, &$manifest, &$credentials, $prefix, $diskDir, $source, $prNumber) {
    $campus = Office::where('code','CICS')->firstOrFail()->campus_id;
    $office = Office::create(['campus_id'=>$campus,'code'=>'SELTEST','name'=>'Selenium Test Office','office_type'=>'academic','status'=>'active']);
    $reportOffice = Office::create(['campus_id'=>$campus,'code'=>'SELRPT','name'=>'Selenium Report Test Office','office_type'=>'academic','status'=>'active']);
    $manifest['offices'] = [$office->id, $reportOffice->id];
    $roles = [
        'office_head'=>['office-head','SELTEST','/office-head','PRISM_UI_OFFICE_PASSWORD','office'],
        'finance'=>['finance-office','FIN','/finance-office','PRISM_UI_FINANCE_PASSWORD','finance'],
        'chancellor'=>['chancellor','OC','/chancellor','PRISM_UI_CHANCELLOR_PASSWORD','chancellor'],
        'procurement'=>['procurement-office','PROC','/procurement-office','PRISM_UI_PROCUREMENT_PASSWORD','procurement'],
        'admin'=>['system-admin','ICTS','/admin','PRISM_UI_ADMIN_PASSWORD','admin'],
        'next_signer'=>['vice-chancellor','OVC','/vice-chancellor','PRISM_UI_NEXT_PASSWORD','vcaa'],
    ];
    $users = [];
    foreach ($roles as $key=>[$roleCode,$officeCode,$dashboard,$env,$suffix]) {
        $email = 'selenium.'.$suffix.'@prism.test';
        if (User::withTrashed()->where('email',$email)->exists()) throw new RuntimeException("Refusing to overwrite $email");
        $role = Role::where('code',$roleCode)->firstOrFail();
        $assignedOffice = Office::where('code',$officeCode)->firstOrFail();
        $password = 'Sel!'.bin2hex(random_bytes(12)).'a9';
        $user = User::create(['name'=>'TEST Selenium '.$role->name,'username'=>'selenium_'.$suffix,
            'email'=>$email,'password'=>$password,'office_id'=>$assignedOffice->id,'position_title'=>'LOCAL TEST ONLY',
            'account_status'=>'active','vc_type'=>$key === 'next_signer' ? 'vcaa' : null]);
        $user->roles()->attach($role->id, ['assigned_at'=>now()]);
        $user->officeAssignments()->attach($assignedOffice->id, ['is_primary'=>true,'role_in_office'=>$role->name,'starts_on'=>'2025-01-01']);
        if (!Hash::check($password,$user->password)) throw new RuntimeException('Prepared hash mismatch');
        $credentials[$env] = $password;
        $config['accounts'][$key] = ['email'=>$email,'password_env'=>$env,'dashboard_path'=>$dashboard];
        $manifest['accounts'][$key] = ['id'=>$user->id,'email'=>$email,'role'=>$role->name,'role_id'=>$role->id,'office'=>$officeCode,'office_id'=>$assignedOffice->id,'vc_type'=>$user->vc_type];
        $users[$key] = $user;
    }
    $owner = $users['office_head'];
    $proposal = function (string $key, string $status='draft', bool $withSource=true, ?Office $target=null, int $year=2026, int $count=1) use ($office,$owner,$prefix,$diskDir,$source,&$manifest) {
        $target ??= $office;
        $p = BudgetProposal::create(['office_id'=>$target->id,'created_by_user_id'=>$owner->id,
            'code'=>$prefix.'-'.$key,'title'=>'TEST Selenium '.$key,'description'=>'Disposable Selenium fixture; no real procurement.',
            'fiscal_year'=>$year,'status'=>$status,'total_estimated_cost'=>2500*$count,'proposed_budget'=>10000,
            'approved_budget'=>$status === 'approved' ? 10000 : null,
            'submitted_by_user_id'=>$status !== 'draft' ? $owner->id : null,
            'submitted_at'=>$status !== 'draft' ? now() : null]);
        for ($i=0;$i<$count;$i++) {
            $item = $p->items()->create(['created_by_user_id'=>$owner->id,'name'=>$key === 'create_pr' ? 'TEST Extraction Bond Paper A4' : ($count === 1 ? 'Bond Paper A4' : 'TEST Report Paper '.($i+1)),
                'description'=>'Disposable test supplies','quantity'=>10,'unit'=>'ream','estimated_unit_cost'=>250,'estimated_total_cost'=>2500,
                'schedule_type'=>'sched9_supplies','ppmp_category'=>'A','source_of_fund'=>'General Fund','target_quarter'=>'Q4',
                'procurement_start_date'=>"$year-10-01",'date_needed'=>"$year-12-31",'status'=>'draft',
                'specifications_json'=>['quarter_distribution'=>['Q1'=>['qty'=>0,'amount'=>0],'Q2'=>['qty'=>0,'amount'=>0],'Q3'=>['qty'=>0,'amount'=>0],'Q4'=>['qty'=>10,'amount'=>2500]]]]);
            if ($withSource) $item->documents()->create(['uploaded_by_user_id'=>$owner->id,'document_type'=>'market_study_source',
                'title'=>'TEST market reference','original_filename'=>'market-source.pdf','file_path'=>$diskDir.'/market-source.pdf',
                'mime_type'=>'application/pdf','file_size'=>strlen($source),'status'=>'uploaded','uploaded_at'=>now()]);
        }
        $manifest['proposals'][$key] = ['id'=>$p->id,'title'=>$p->title,'status'=>$status,'year'=>$year,'office_id'=>$target->id,'item_count'=>$count,'total'=>2500*$count];
        return $p;
    };
    $officePath = fn($p)=>'/office-head/budget-proposal?proposal='.$p->id.'&year=2026';
    foreach (['draft','draft_crud','missing_sources','planning_chain'] as $key) {
        $p = $proposal($key,'draft',$key !== 'missing_sources');
        $config['cases'][$key] = ['path'=>$officePath($p),'title'=>$p->title,'proposal_id'=>$p->id];
        if ($key === 'draft_crud') $config['cases'][$key]['before_total'] = 2500;
        if ($key === 'planning_chain') $config['cases'][$key] += ['finance_path'=>'/finance-office/proposal-review/'.$p->id.'?year=2026','approval_path'=>'/chancellor/budget-approval?year=2026'];
    }
    $p = $proposal('finance_return','submitted');
    $config['cases']['finance_return'] = ['proposal_id'=>$p->id,'proposal_code'=>$p->code,'title'=>$p->title,'finance_path'=>'/finance-office/proposal-review/'.$p->id.'?year=2026',
        'office_path'=>$officePath($p),'timeline_path'=>'/office-head/my-proposals?year=2026'];
    $p = $proposal('chancellor_return','endorsed');
    $config['cases']['chancellor_return'] = ['path'=>'/chancellor/budget-approval?year=2026','proposal_id'=>$p->id,'title'=>$p->title];
    $p = $proposal('create_pr','approved');
    $prPath = '/procurement-office/purchase-request-management?year=2026';
    $aocPath = '/procurement-office/abstract-of-canvass?year=2026';
    $poPath = '/procurement-office/purchase-orders?year=2026';
    $config['cases']['create_pr'] = ['path'=>$prPath,'proposal_id'=>$p->id,'pdf'=>'fixtures/private/approved-test-pr.pdf','number'=>$prNumber,
        'extraction_timeout'=>120,'expected_items'=>[['name'=>'TEST Extraction Bond Paper A4','unit'=>'ream','quantity'=>10,'unit_cost'=>250]],'expected_total'=>2500,
        'expected'=>['#fPrNumber'=>$prNumber,'#fOffice'=>'SELTEST','#fItem'=>'TEST PDF paper purchase']];

    $makePr = function ($key,$stage='fully_signed',?Office $target=null,$year=2026,$name='Bond Paper A4') use ($office,$owner,$prefix,$diskDir,&$manifest) {
        $p = PurchaseRequest::create(['office_id'=>($target ?? $office)->id,'created_by_user_id'=>$owner->id,
            'number'=>$prefix.'-PR-'.$key,'title'=>'TEST Selenium '.$key,'fiscal_year'=>$year,'total_amount'=>2500,
            'status'=>'approved','signatory_stage'=>$stage,'canvassing_stage'=>$stage === 'fully_signed' ? 'completed' : 'not_started',
            'file_path'=>$diskDir.'/signed-pr.pdf','uploaded_at'=>now(),'submitted_at'=>now(),'remarks'=>'Disposable TEST fixture']);
        $item = $p->items()->create(['name'=>$name,'quantity'=>10,'unit'=>'ream','estimated_unit_cost'=>250,'estimated_total_cost'=>2500]);
        $manifest['prs'][$key] = ['id'=>$p->id,'number'=>$p->number,'title'=>$p->title,'item_id'=>$item->id,'stage'=>$stage,'office_id'=>$p->office_id,'year'=>$year];
        return $p;
    };
    $makeAoc = function ($key,$pr) use ($owner,$prefix,&$manifest) {
        $a = AbstractOfCanvass::create(['purchase_request_id'=>$pr->id,'created_by_user_id'=>$owner->id,'code'=>$prefix.'-AOC-'.$key,'signatory_stage'=>'fully_signed']);
        $manifest['aocs'][$key] = ['id'=>$a->id,'pr_id'=>$pr->id,'code'=>$a->code];
        return $a;
    };
    $makePo = function ($key,$aoc,$paid=false) use ($owner,$prefix,&$manifest) {
        $p = PurchaseOrder::create(['abstract_of_canvass_id'=>$aoc->id,'created_by_user_id'=>$owner->id,'po_number'=>$prefix.'-PO-'.$key,
            'supplier_name'=>'TEST Supplier Alpha','supplier_address'=>'TEST Local Laboratory','total_amount'=>2500,
            'signatory_stage'=>'fully_signed','status'=>$paid ? 'paid' : 'awaiting_delivery',
            'issued_at'=>'2026-10-01 08:00:00','procured_on'=>'2026-10-01','expected_delivery_date'=>'2026-12-31',
            'paid_at'=>$paid ? '2026-10-03 08:00:00' : null]);
        $manifest['pos'][$key] = ['id'=>$p->id,'aoc_id'=>$aoc->id,'number'=>$p->po_number,'status'=>$p->status];
        return $p;
    };
    $quotes = function ($pr) use ($owner,$diskDir,$source) {
        foreach (['Alpha','Beta'] as $supplier) $pr->documents()->create(['uploaded_by_user_id'=>$owner->id,'document_type'=>'canvass_quotation',
            'title'=>'TEST Supplier '.$supplier,'original_filename'=>'test-quotation.pdf','file_path'=>$diskDir.'/market-source.pdf',
            'mime_type'=>'application/pdf','file_size'=>strlen($source),'uploaded_at'=>now(),
            'extracted_fields_json'=>['supplier_address'=>'TEST Local Laboratory','items'=>[['name'=>'Bond Paper A4','quantity'=>10,'unit'=>'ream','unitPrice'=>250]]]]);
    };
    $stable = $makePr('STABLE'); $quotes($stable);
    $aoc = $makeAoc('STABLE',$stable); $po = $makePo('STABLE',$aoc);
    $config['cases']['pr'] = ['path'=>$prPath,'id'=>$stable->id,'expected'=>['#fPrNumber'=>$stable->number,'#fOffice'=>'SELTEST','#fItem'=>$stable->title],
        'search'=>$stable->number,'search_ids'=>[$stable->id],'office'=>'SELTEST'];
    $config['cases']['aoc'] = ['path'=>$aocPath,'id'=>$aoc->id,'expected'=>['#fCode'=>$aoc->code,'#fPrNumber'=>$stable->number,'#fOffice'=>'SELTEST','#fTitle'=>$stable->title],
        'expected_suppliers'=>['TEST Supplier Alpha','TEST Supplier Beta'],'search'=>$aoc->code,'search_ids'=>[$aoc->id],'office'=>'SELTEST'];
    $config['cases']['po'] = ['path'=>$poPath,'id'=>$po->id,'expected'=>['#fPoNumber'=>$po->po_number,'#fAocCode'=>$aoc->code,'#fOffice'=>'SELTEST','#fSupplier'=>'TEST Supplier Alpha','#fAmount'=>'₱2,500.00'],
        'search'=>$po->po_number,'search_ids'=>[$po->id],'office'=>'SELTEST'];
    $p = $makePr('CREATE-AOC'); $quotes($p);
    $config['cases']['create_aoc'] = ['path'=>$aocPath,'pr_id'=>$p->id,'expected'=>['#fPrNumber'=>$p->number,'#fOffice'=>'SELTEST','#fTitle'=>$p->title]];
    $p = $makePr('CREATE-PO'); $quotes($p); $a = $makeAoc('CREATE-PO',$p);
    $config['cases']['create_po'] = ['path'=>$poPath,'aoc_id'=>$a->id,'aoc_code'=>$a->code,'supplier'=>'TEST Supplier Gamma','amount'=>2500,'address'=>'TEST Gamma Warehouse','delivery_date'=>'2026-12-31'];
    foreach (['signature_readonly'=>'at_vice_chancellor','signature_handoff'=>'at_end_user'] as $key=>$stage) {
        $p = $makePr(strtoupper(str_replace('_','-',$key)),$stage);
        $config['cases'][$key] = ['account'=>'office_head','path'=>'/office-head/for-my-signature?year=2026','document_key'=>'pr-'.$p->id,'number'=>$p->number];
    }
    $config['cases']['signature_handoff'] += ['expected_stage'=>'PR – At Vice Chancellor (VCAA)','next_account'=>'next_signer',
        'next_path'=>'/vice-chancellor/for-my-signature?year=2026','before_log_count'=>0];
    foreach (['receiving','record_receipt'] as $key) {
        $p = $makePr(strtoupper(str_replace('_','-',$key))); $a = $makeAoc(strtoupper($key),$p); $makePo(strtoupper($key),$a);
        $config['cases'][$key] = ['path'=>'/office-head/purchase-requests?year=2026','item_id'=>$p->items->first()->id,'remaining_quantity'=>10];
    }
    $config['cases']['record_receipt'] += ['arrival_date'=>'2026-10-03','quantity'=>2,'recipient'=>'TEST Selenium Receiver',
        'before_quantity'=>'0 / 10 ream','after_quantity'=>'2 / 10 ream','after_status'=>'Partially Received'];
    // Two isolated report years: four Q4 targets / one paid, versus two / none.
    $proposal('report_2026','approved',true,$reportOffice,2026,4);
    $proposal('report_2025','approved',true,$reportOffice,2025,2);
    $p = $makePr('REPORT-PAID','fully_signed',$reportOffice,2026,'TEST Report Paper 1');
    $a = $makeAoc('REPORT-PAID',$p); $makePo('REPORT-PAID',$a,true);
    $stats = ['ITEMS TARGETED'=>'4','ITEMS PROCURED'=>'1','COMPLETION RATE'=>'25%','DELAYED ITEMS'=>'0'];
    $config['cases']['report_office'] = ['path'=>'/procurement-office/procurement-reports?year=2026','office'=>'SELRPT','expected_stats'=>$stats,
        'expected_rows'=>[['SELRPT','Q4','4','1','25%']]];
    $config['cases']['report_year'] = ['path'=>'/procurement-office/procurement-reports?year=2026&office=SELRPT','target_year'=>2025,
        'before_stats'=>$stats,'after_stats'=>['ITEMS TARGETED'=>'2','ITEMS PROCURED'=>'0','COMPLETION RATE'=>'0%','DELAYED ITEMS'=>'0']];
    $config['cases']['new_user'] = ['role_id'=>Role::where('code','office-head')->firstOrFail()->id,'office_id'=>$office->id];
    $n = PrismNotification::create(['user_id'=>$owner->id,'type'=>'selenium_fixture','title'=>'TEST Selenium signature reminder',
        'message'=>'Disposable notification for FC-30.','action_url'=>'/office-head/for-my-signature?year=2026','data_json'=>['test_fixture'=>$prefix]]);
    $manifest['notification_id'] = $n->id;
    $config['cases']['notification'] = ['account'=>'office_head','path'=>'/office-head?year=2026','id'=>$n->id,'title'=>$n->title,
        'destination'=>'/office-head/for-my-signature','unread_before'=>1];
    // Exact filter sets are derived from the records explicitly created above.
    $config['cases']['pr']['office_ids'] = array_values(array_column(array_filter($manifest['prs'],fn($r)=>$r['office_id']===$office->id),'id'));
    foreach (['aoc'=>'aocs','po'=>'pos'] as $key=>$section) {
        $config['cases'][$key]['office_ids'] = array_values(array_column(array_filter($manifest[$section],fn($r,$k)=>$k !== 'REPORT-PAID', ARRAY_FILTER_USE_BOTH),'id'));
    }
});
file_put_contents($private.'/credentials.json', json_encode($credentials, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
file_put_contents($root.'/workflow.local.json', json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
file_put_contents($private.'/prepared-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
echo 'Prepared six accounts and disposable fixtures. No Selenium tests executed.'.PHP_EOL;
