<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrScanUpload;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PrScanController extends Controller
{
    public function upload(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'scan' => 'required|image|max:10240',
        ]);

        $pr = PurchaseRequest::findOrFail($id);

        $path = $request->file('scan')->store('pr-scans', 'public');

        PrScanUpload::create([
            'purchase_request_id'  => $pr->id,
            'uploaded_by_user_id'  => $request->user()->id,
            'file_path'            => $path,
            'original_filename'    => $request->file('scan')->getClientOriginalName(),
        ]);

        return response()->json([
            'success'     => true,
            'uploader'    => $request->user()->name,
            'uploaded_at' => Carbon::now()->toDateTimeString(),
        ]);
    }
}
