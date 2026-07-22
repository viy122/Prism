<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Response;

class PrintController extends Controller
{
    public function purchaseRequest(int $id): Response
    {
        $pr = PurchaseRequest::with(['office', 'items', 'createdBy'])->findOrFail($id);

        return response(view('prism.documents.pr-print', compact('pr')));
    }
}
