<?php

namespace App\Http\Controllers;

use App\Models\AocSignatureLog;
use App\Models\PoSignatureLog;
use App\Models\PrSignatureLog;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves ORIGINAL (unblurred) signed-document photos from the private
 * `signatures` disk. Everyone else only ever sees the blurred copy on the
 * public disk — the signature stays hidden for privacy.
 */
class SignaturePhotoController extends Controller
{
    public function original(string $docType, int $logId): Response
    {
        $log = match ($docType) {
            'pr'    => PrSignatureLog::findOrFail($logId),
            'aoc'   => AocSignatureLog::findOrFail($logId),
            'po'    => PoSignatureLog::findOrFail($logId),
            default => abort(404),
        };

        $user = auth()->user();
        $role = $user?->roles()->first()?->name;

        $allowed = in_array($role, ['Procurement Office', 'System Administrator'], true)
            || $log->signed_by_user_id === $user?->id;

        abort_unless($allowed, 403, 'Only the Procurement Office or the signer may view the unblurred document.');
        abort_unless($log->photo_path && Storage::disk('signatures')->exists($log->photo_path), 404);

        return Storage::disk('signatures')->response($log->photo_path);
    }
}
