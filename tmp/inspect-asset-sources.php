<?php
require __DIR__.'/asset-live-check.php';
foreach (App\Models\ItemReceipt::with(['item.purchaseRequest.office', 'purchaseOrder'])->get() as $receipt) {
    echo json_encode(['receipt'=>$receipt->id,'item'=>$receipt->item?->name,'office'=>$receipt->item?->purchaseRequest?->office?->code,'office_id'=>$receipt->item?->purchaseRequest?->office_id,'quantity'=>$receipt->quantity,'arrival'=>$receipt->arrival_date?->toDateString(),'pr_status'=>$receipt->item?->purchaseRequest?->status,'po_stage'=>$receipt->purchaseOrder?->signatory_stage,'po_status'=>$receipt->purchaseOrder?->status]).PHP_EOL;
}
echo 'DEMO_OFFICE '.json_encode(App\Models\User::where('username','office_head')->first()?->only(['office_id'])).PHP_EOL;
