<?php

namespace App\Services;

use App\Models\AbstractOfCanvass;
use App\Models\AocSignatureLog;
use App\Models\PoSignatureLog;
use App\Models\ProcurementStatusUpdate;
use App\Models\PrSignatureLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Shared advance / return / sign-with-photo engine for the three signatory
 * documents (PR, AOC, PO). Extracted from PrismProcurementOfficeController so
 * per-role signature queues (BAC, VC, Accounting, Chancellor) and the central
 * procurement screens drive the exact same transitions.
 */
class SignatoryActionService
{
    /** doc class => [log model, log FK column] */
    private const DOC_MAP = [
        PurchaseRequest::class   => [PrSignatureLog::class, 'purchase_request_id'],
        AbstractOfCanvass::class => [AocSignatureLog::class, 'abstract_of_canvass_id'],
        PurchaseOrder::class     => [PoSignatureLog::class, 'purchase_order_id'],
    ];

    public function __construct(private SignatureDetectionService $detector)
    {
    }

    /**
     * Complete the document's current stage (sign or forward) and move to the
     * next one. Returns the JSON-ready payload the existing blades consume,
     * or ['success' => false, 'error' => ..., 'status' => 422] on failure.
     *
     * @param array $photoColumns extra signature-log columns from the photo pipeline
     */
    public function advance(Model $doc, ?string $remarks = null, ?string $thirdSigner = null, array $photoColumns = []): array
    {
        $next = $doc->nextSignatoryStage();
        if (!$next) {
            return ['success' => false, 'error' => $doc::SIGNATORY_DOC_PREFIX . ' is already fully signed.', 'status' => 422];
        }

        $current         = $doc->signatory_stage;
        $action          = $doc->advanceAction();
        $signatoryNumber = $doc->stageIndex($current);

        $updateData = ['signatory_stage' => $next];

        if ($doc instanceof PurchaseRequest) {
            if ($next === 'fully_signed') {
                $updateData['canvassing_stage'] = 'not_started';
            }

            // Entering the flexible 3rd slot: record who actually signs 3rd
            if ($next === 'at_third_sign') {
                if (!in_array($thirdSigner, ['accounting', 'vice_chancellor'], true)) {
                    return ['success' => false, 'error' => 'Choose who signs 3rd: Accounting or Vice Chancellor.', 'status' => 422];
                }
                $updateData['third_signer'] = $thirdSigner;
                $chosen  = $thirdSigner === 'accounting' ? 'Accounting' : 'Vice Chancellor';
                $remarks = trim(($remarks ?? '') . " [3rd signer: {$chosen}]");
            }
        }

        $doc->update($updateData);
        $this->clearTrackingOverride($doc);

        $this->createLog($doc, array_merge([
            'signatory_number'  => $signatoryNumber,
            'signed_by_user_id' => auth()->id(),
            'action'            => $action,
            'remarks'           => $remarks,
            'signed_at'         => now(),
        ], $photoColumns));

        if ($doc instanceof PurchaseRequest) {
            ProcurementStatusUpdate::create([
                'purchase_request_id' => $doc->id,
                'updated_by_user_id'  => auth()->id(),
                'status'              => $next,
                'remarks'             => $remarks ?? '',
            ]);
            NotificationService::prStatusUpdated($doc);
        }

        $fresh = $doc->fresh();

        // Tell the owning role's users their queue just gained a document
        if ($ownerRole = $fresh->stageOwnerRole($next)) {
            NotificationService::documentAwaitingSignature($fresh, $ownerRole);
        }

        $payload = [
            'success'          => true,
            'signatoryStage'   => $next,
            'signatoryLabel'   => $fresh->signatory_label,
            'stageMeta'        => $fresh->resolvedStageMeta(),
            'currentStageType' => $fresh->stageMetaFor($next)['type'] ?? 'signature',
            'nextStageLabel'   => $fresh->stageMetaFor($fresh->nextSignatoryStage())['label'] ?? null,
        ];

        if ($fresh instanceof PurchaseRequest) {
            $payload['canvassingStage'] = $fresh->canvassing_stage;
            $payload['canvassingLabel'] = $fresh->canvassing_label;
            $payload['thirdSigner']     = $fresh->third_signer;
        }

        return $payload;
    }

    /**
     * Return moves the document back exactly ONE stage — to whoever owned
     * the step right before the current one — not all the way to draft.
     * Only when returning from the very first real stage (e.g. at_end_user)
     * does that "one step back" land on 'draft', same as before.
     */
    public function returnOneStep(Model $doc, string $remarks): array
    {
        $prev      = $doc->signatory_stage;
        $stages    = $doc::signatoryStages();
        $prevIndex = $doc->stageIndex($prev);
        $newIndex  = max(0, $prevIndex - 1);
        $newStage  = $stages[$newIndex];

        $updateData = ['signatory_stage' => $newStage];

        if ($doc instanceof PurchaseRequest) {
            // third_signer only matters at/after the flexible 3rd slot —
            // clear it if returning to a stage before that point was chosen.
            $thirdSignIndex = array_search('at_third_sign', $stages, true);
            if ($thirdSignIndex !== false && $newIndex < $thirdSignIndex) {
                $updateData['third_signer'] = null;
            }
        }

        $doc->update($updateData);
        $this->clearTrackingOverride($doc);

        $this->createLog($doc, [
            'signatory_number'  => $prevIndex,
            'signed_by_user_id' => auth()->id(),
            'action'            => 'returned',
            'remarks'           => $remarks,
            'signed_at'         => now(),
        ]);

        $fresh = $doc->fresh();

        if ($fresh instanceof PurchaseRequest) {
            ProcurementStatusUpdate::create([
                'purchase_request_id' => $fresh->id,
                'updated_by_user_id'  => auth()->id(),
                'status'              => 'returned_one_step',
                'remarks'             => $remarks,
            ]);
        }

        return [
            'success'        => true,
            'signatoryStage' => $newStage,
            'signatoryLabel' => $fresh->signatory_label,
            'stageMeta'      => $fresh->resolvedStageMeta(),
            'thirdSigner'    => $fresh instanceof PurchaseRequest ? $fresh->third_signer : null,
        ];
    }

    /**
     * The signature photo pipeline: store the original privately and
     * auto-detect the signature. Never advances the stage itself — always
     * hands back to the caller so a distinct "Next" action (confirmSign)
     * commits the advance, matching the physical "sign it, then walk it to
     * the next office" flow.
     *
     * Outcomes:
     *  - detected            → needsConfirmation, blurredPath already ready
     *  - not detected        → needsConfirmation (caller shows manual confirm + drag-box)
     *  - microservice down   → needsConfirmation with detection 'pending'
     */
    public function signWithPhoto(Model $doc, UploadedFile $photo): array
    {
        $stageMeta = $doc->stageMetaFor($doc->signatory_stage);
        if (($stageMeta['type'] ?? 'signature') !== 'signature') {
            return ['success' => false, 'error' => 'The current stage is a routing step — no signature photo needed.', 'status' => 422];
        }

        $originalPath = $this->storeOriginal($doc, $photo);
        $contents     = Storage::disk('signatures')->get($originalPath);

        $detection = $this->detector->detect($contents);

        if ($detection === null) {
            // Microservice unreachable — offer manual confirm; photo processed later
            return [
                'success'           => true,
                'needsConfirmation' => true,
                'detection'         => 'pending',
                'photoPath'         => $originalPath,
                'message'           => 'Signature scanning is temporarily unavailable. Confirm the document is signed to proceed — the photo will be processed later.',
            ];
        }

        if (!($detection['detected'] ?? false)) {
            return [
                'success'           => true,
                'needsConfirmation' => true,
                'detection'         => 'not_detected',
                'photoPath'         => $originalPath,
                'message'           => 'No signature was detected on the photo. Confirm the document is signed, or mark the signature area manually.',
            ];
        }

        $blurredPath = $this->storeBlurred($doc, base64_decode($detection['blurred_image_b64']));

        return [
            'success'           => true,
            'needsConfirmation' => true,
            'detection'         => 'detected',
            'photoPath'         => $originalPath,
            'blurredPath'       => $blurredPath,
            'confidence'        => $detection['confidence'] ?? null,
            'message'           => 'Signature detected and blurred. Click Next to send it to the next signatory.',
        ];
    }

    /**
     * The "Next" action — the sole place that actually advances a signature-
     * type stage. Called after signWithPhoto (photoPath/blurredPath already
     * known — no signature was captured yet if $photoPath is null, which is
     * fine since a photo is optional) or directly for the no-photo case.
     * Optional $boxes ({x,y,w,h} fractions) let the signer blur the signature
     * region themselves when auto-detection missed it or wasn't run.
     */
    public function confirmSign(Model $doc, ?string $photoPath = null, ?array $boxes = null, ?string $remarks = null, ?string $thirdSigner = null, ?string $blurredPath = null): array
    {
        if ($photoPath && (!$this->photoBelongsToDoc($doc, $photoPath) || !Storage::disk('signatures')->exists($photoPath))) {
            return ['success' => false, 'error' => 'Uploaded photo not found — please re-upload.', 'status' => 422];
        }

        // Only trust a client-supplied blurred_path if it's actually one of
        // this doc's own blurred copies — otherwise treat it as not provided.
        if ($blurredPath && (!$this->blurredPhotoBelongsToDoc($doc, $blurredPath) || !Storage::disk('public')->exists($blurredPath))) {
            $blurredPath = null;
        }

        $detectionStatus = $photoPath ? 'pending' : 'none';

        if ($blurredPath) {
            // Already detected & blurred by signWithPhoto — nothing left to do
            $detectionStatus = 'detected';
        } elseif ($photoPath && $boxes) {
            $blurred = $this->detector->blurRegion(Storage::disk('signatures')->get($photoPath), $boxes);
            if ($blurred !== null) {
                $blurredPath     = $this->storeBlurred($doc, $blurred);
                $detectionStatus = 'manual_confirmed';
            }
        } elseif ($photoPath && $this->detector->isAvailable()) {
            // Service is up but found nothing and the signer confirmed anyway
            $detectionStatus = 'manual_confirmed';
        }

        $photoColumns = $photoPath ? [
            'photo_path'           => $photoPath,
            'blurred_photo_path'   => $blurredPath,
            'signature_boxes_json' => $boxes ?? [],
            'detection_status'     => $detectionStatus,
            'photo_uploaded_at'    => now(),
        ] : [];

        return $this->advance($doc, $remarks, $thirdSigner, $photoColumns) + ['detection' => $detectionStatus];
    }

    /**
     * Re-run detection for a log whose photo could not be processed
     * (detection_status pending/failed). Used by procurement's Reprocess.
     */
    public function reprocessPhoto(Model $doc, Model $log): array
    {
        if (!$log->photo_path || !Storage::disk('signatures')->exists($log->photo_path)) {
            return ['success' => false, 'error' => 'No stored photo to reprocess.', 'status' => 422];
        }

        $detection = $this->detector->detect(Storage::disk('signatures')->get($log->photo_path));
        if ($detection === null) {
            return ['success' => false, 'error' => 'Signature service is still unavailable.', 'status' => 503];
        }

        if (!($detection['detected'] ?? false)) {
            $log->update(['detection_status' => 'failed']);
            return ['success' => true, 'detection' => 'failed'];
        }

        $log->update([
            'blurred_photo_path'   => $this->storeBlurred($doc, base64_decode($detection['blurred_image_b64'])),
            'signature_boxes_json' => $detection['boxes'] ?? [],
            'detection_status'     => 'detected',
        ]);

        return ['success' => true, 'detection' => 'detected'];
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * Any real progress event invalidates a manual tracking-status pin — clear
     * it so the computed status (PurchaseRequest::currentTrackingStage()) takes
     * over again rather than silently going stale.
     */
    private function clearTrackingOverride(Model $doc): void
    {
        $pr = match (true) {
            $doc instanceof PurchaseRequest   => $doc,
            $doc instanceof AbstractOfCanvass => $doc->purchaseRequest,
            $doc instanceof PurchaseOrder     => $doc->abstractOfCanvass?->purchaseRequest,
            default                           => null,
        };

        $pr?->clearTrackingOverride();
    }

    private function createLog(Model $doc, array $attributes): void
    {
        [$logClass, $fk] = self::DOC_MAP[get_class($doc)];
        $logClass::create([$fk => $doc->id] + $attributes);
    }

    private function docPrefix(Model $doc): string
    {
        return strtolower($doc::SIGNATORY_DOC_PREFIX);
    }

    private function storeOriginal(Model $doc, UploadedFile $photo): string
    {
        $name = $doc->signatory_stage . '-' . now()->format('YmdHis') . '.' . strtolower($photo->getClientOriginalExtension() ?: 'jpg');
        return Storage::disk('signatures')->putFileAs($this->docPrefix($doc) . '/' . $doc->id, $photo, $name);
    }

    private function storeBlurred(Model $doc, string $jpegContents): string
    {
        $path = 'signature-photos/' . $this->docPrefix($doc) . '/' . $doc->id . '/' . $doc->signatory_stage . '-' . now()->format('YmdHis') . '.jpg';
        Storage::disk('public')->put($path, $jpegContents);
        return $path;
    }

    private function photoBelongsToDoc(Model $doc, string $photoPath): bool
    {
        return str_starts_with($photoPath, $this->docPrefix($doc) . '/' . $doc->id . '/');
    }

    private function blurredPhotoBelongsToDoc(Model $doc, string $blurredPath): bool
    {
        return str_starts_with($blurredPath, 'signature-photos/' . $this->docPrefix($doc) . '/' . $doc->id . '/');
    }
}
