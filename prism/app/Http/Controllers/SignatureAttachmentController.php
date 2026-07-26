<?php

namespace App\Http\Controllers;

use App\Models\SignatureAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves a mobile-attached signature file (photo or PDF) from the private
 * `signatures` disk. Reached only via a signed URL generated server-side for
 * whoever is allowed to see this document's signature history (mirrors how
 * PrintController's print.pr route is signed rather than role-gated) — both
 * the web history views and the mobile app open the same signed link.
 */
class SignatureAttachmentController extends Controller
{
    public function show(int $id): Response
    {
        $attachment = SignatureAttachment::findOrFail($id);

        abort_unless(Storage::disk('signatures')->exists($attachment->file_path), 404);

        return Storage::disk('signatures')->response($attachment->file_path, $attachment->original_filename);
    }
}
