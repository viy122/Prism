{{--
    Shared "For My Signature" queue table + signature photo modal.

    Expects: $queueRows — rows from HandlesSignatureQueue::signatureQueueRows()
    (docType, docLabel, id, number, office, title, stageLabel, stageType,
     nextStageLabel, requiresThirdSignerChoice, waitingSince, signUrl, confirmUrl)
--}}

<div class="card">
    <div class="card-head">
        <div>
            <p class="card-eyebrow">Signature Queue</p>
            <h2 class="card-title">Documents For My Signature</h2>
        </div>
        @if(count($queueRows) > 0)
        <span style="display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:20px;font-size:11px;font-weight:700;background:#faeeda;color:#854f0b;border:1px solid #fac775;">{{ count($queueRows) }} waiting</span>
        @endif
    </div>

    @if(count($queueRows) === 0)
        <div class="empty-state">
            <i class="ti ti-signature"></i>
            <p>No documents are waiting for your signature right now.</p>
        </div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Number</th>
                    <th>Office</th>
                    <th>Description</th>
                    <th>Current Stage</th>
                    <th>Waiting Since</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($queueRows as $row)
                <tr id="sig-row-{{ $row['docType'] }}-{{ $row['id'] }}">
                    <td><span class="sig-doc-badge sig-doc-{{ $row['docType'] }}">{{ $row['docLabel'] }}</span></td>
                    <td style="font-weight:700;font-size:12px;color:var(--s500);white-space:nowrap;">{{ $row['number'] }}</td>
                    <td style="font-size:12px;font-weight:600;color:var(--s600);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row['office'] }}</td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row['title'] }}</td>
                    <td style="font-size:12px;white-space:nowrap;">{{ $row['stageLabel'] }}</td>
                    <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $row['waitingSince'] }}</td>
                    <td>
                        @if($row['stageType'] === 'routing')
                        <button class="btn btn-sign sig-forward-btn"
                            data-sign-url="{{ $row['signUrl'] }}"
                            data-number="{{ $row['number'] }}"
                            data-row-id="sig-row-{{ $row['docType'] }}-{{ $row['id'] }}">
                            <i class="ti ti-arrow-forward-up"></i> Forward
                        </button>
                        @else
                        <button class="btn btn-sign sig-open-modal-btn"
                            data-sign-url="{{ $row['signUrl'] }}"
                            data-confirm-url="{{ $row['confirmUrl'] }}"
                            data-number="{{ $row['number'] }}"
                            data-stage="{{ $row['stageLabel'] }}"
                            data-next-stage="{{ $row['nextStageLabel'] ?? '' }}"
                            data-third-signer="{{ $row['requiresThirdSignerChoice'] ? '1' : '0' }}"
                            data-row-id="sig-row-{{ $row['docType'] }}-{{ $row['id'] }}">
                            <i class="ti ti-signature"></i> Sign
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── Signature photo modal ── --}}
<div class="sig-modal-backdrop" id="sigModalBackdrop">
    <div class="sig-modal">
        <div class="sig-modal-head">
            <div>
                <p class="card-eyebrow">Sign Document</p>
                <h3 class="sig-modal-title" id="sigModalTitle">—</h3>
                <p class="sig-modal-sub" id="sigModalSub">Take a photo of the physically signed document and upload it. The system will detect the signature, blur it for privacy, and mark this stage as signed.</p>
            </div>
            <button class="sig-modal-close" id="sigModalClose"><i class="ti ti-x"></i></button>
        </div>

        {{-- Step 1: pick / capture photo --}}
        <div class="sig-step" id="sigStepUpload">
            <label class="sig-dropzone" id="sigDropzone">
                <input type="file" id="sigPhotoInput" accept="image/jpeg,image/png" capture="environment" hidden>
                <i class="ti ti-camera"></i>
                <span id="sigDropzoneText">Tap to take a photo or choose an image of the signed document</span>
            </label>

            <div class="sig-preview-wrap" id="sigPreviewWrap" style="display:none;">
                <div class="sig-preview-canvas" id="sigPreviewCanvas">
                    <img id="sigPreviewImg" alt="Signed document preview">
                    <div class="sig-select-box" id="sigSelectBox" style="display:none;"></div>
                </div>
                <p class="sig-preview-hint" id="sigPreviewHint"></p>
            </div>

            <div class="sig-third-signer" id="sigThirdSigner" style="display:none;">
                <p class="sig-field-label">Who signs 3rd? (Accounting and Vice Chancellor sign in flexible order)</p>
                <label><input type="radio" name="sig_third_signer" value="accounting"> Accounting first</label>
                <label><input type="radio" name="sig_third_signer" value="vice_chancellor"> Vice Chancellor first</label>
            </div>

            <div>
                <p class="sig-field-label">Remarks (optional)</p>
                <textarea id="sigRemarks" class="sig-remarks" rows="2" maxlength="1000" placeholder="Optional remarks…"></textarea>
            </div>

            <div class="sig-status" id="sigStatus" style="display:none;"></div>

            <div class="sig-actions">
                <button class="btn btn-neutral" id="sigCancelBtn">Cancel</button>
                <button class="btn btn-sign" id="sigSubmitBtn" disabled><i class="ti ti-scan"></i> Upload & Detect Signature</button>
                <button class="btn btn-confirm-manual" id="sigConfirmBtn" style="display:none;"><i class="ti ti-check"></i> Confirm Signed</button>
            </div>
        </div>
    </div>
</div>

@push('page-css')
<style>
    .sig-doc-badge { display:inline-flex; align-items:center; height:22px; padding:0 10px; border-radius:6px; font-size:10px; font-weight:800; letter-spacing:.06em; }
    .sig-doc-pr  { background:#e0ecff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .sig-doc-aoc { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    .sig-doc-po  { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }

    .btn-sign { background: var(--crimson, #a32d2d); color: #fff; }
    .btn-sign:hover:not(:disabled) { filter: brightness(.92); }
    .btn-neutral { background: #e2e8f0; color: #334155; }
    .btn-confirm-manual { background: #854f0b; color: #fff; }

    .sig-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; }
    .sig-modal-backdrop.open { display: flex; }
    .sig-modal { background: #fff; border-radius: 18px; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; padding: 24px 26px; box-shadow: 0 24px 60px rgba(0,0,0,.25); }
    .sig-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
    .sig-modal-title { font-size: 17px; font-weight: 800; color: #0f172a; }
    .sig-modal-sub { font-size: 12px; color: #64748b; margin-top: 4px; line-height: 1.5; }
    .sig-modal-close { border: none; background: #f1f5f9; width: 32px; height: 32px; border-radius: 9px; cursor: pointer; color: #475569; flex-shrink: 0; }

    .sig-step { display: flex; flex-direction: column; gap: 14px; }
    .sig-dropzone { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border: 2px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; padding: 26px 18px; cursor: pointer; text-align: center; font-size: 12px; color: #64748b; }
    .sig-dropzone i { font-size: 30px; color: #94a3b8; }
    .sig-dropzone:hover { border-color: #94a3b8; }

    .sig-preview-canvas { position: relative; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; user-select: none; }
    .sig-preview-canvas img { display: block; width: 100%; height: auto; }
    .sig-select-box { position: absolute; border: 2px solid #dc2626; background: rgba(220,38,38,.18); pointer-events: none; }
    .sig-preview-hint { font-size: 11px; color: #64748b; margin-top: 6px; }

    .sig-field-label { font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 6px; }
    .sig-third-signer label { display: block; font-size: 12px; color: #334155; margin-top: 4px; cursor: pointer; }
    .sig-remarks { width: 100%; border: 1px solid #cbd5e1; border-radius: 9px; padding: 8px 12px; font-size: 12px; font-family: inherit; resize: vertical; }

    .sig-status { border-radius: 10px; padding: 10px 14px; font-size: 12px; font-weight: 600; line-height: 1.5; }
    .sig-status.info    { background: #e0ecff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .sig-status.warn    { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .sig-status.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .sig-status.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .sig-actions { display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const backdrop   = document.getElementById('sigModalBackdrop');
    const titleEl    = document.getElementById('sigModalTitle');
    const dropzone   = document.getElementById('sigDropzone');
    const dzText     = document.getElementById('sigDropzoneText');
    const input      = document.getElementById('sigPhotoInput');
    const previewWrap= document.getElementById('sigPreviewWrap');
    const previewImg = document.getElementById('sigPreviewImg');
    const previewHint= document.getElementById('sigPreviewHint');
    const canvasEl   = document.getElementById('sigPreviewCanvas');
    const selectBox  = document.getElementById('sigSelectBox');
    const thirdWrap  = document.getElementById('sigThirdSigner');
    const remarksEl  = document.getElementById('sigRemarks');
    const statusEl   = document.getElementById('sigStatus');
    const submitBtn  = document.getElementById('sigSubmitBtn');
    const confirmBtn = document.getElementById('sigConfirmBtn');

    let ctx = null; // { signUrl, confirmUrl, number, rowId, needsThird, photoPath, manualBox, selecting }

    function setStatus(kind, msg) {
        if (!kind) { statusEl.style.display = 'none'; return; }
        statusEl.style.display = 'block';
        statusEl.className = 'sig-status ' + kind;
        statusEl.innerHTML = msg;
    }

    function resetModal() {
        input.value = '';
        previewWrap.style.display = 'none';
        previewImg.src = '';
        selectBox.style.display = 'none';
        remarksEl.value = '';
        setStatus(null);
        submitBtn.disabled = true;
        submitBtn.style.display = '';
        submitBtn.innerHTML = '<i class="ti ti-scan"></i> Upload & Detect Signature';
        confirmBtn.style.display = 'none';
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="ti ti-check"></i> Confirm Signed';
        dzText.textContent = 'Tap to take a photo or choose an image of the signed document';
        dropzone.style.display = '';
        previewHint.textContent = '';
    }

    function openModal(btn) {
        ctx = {
            signUrl:    btn.dataset.signUrl,
            confirmUrl: btn.dataset.confirmUrl,
            number:     btn.dataset.number,
            rowId:      btn.dataset.rowId,
            needsThird: btn.dataset.thirdSigner === '1',
            photoPath:  null,
            manualBox:  null,
        };
        resetModal();
        titleEl.textContent = btn.dataset.number + ' — ' + btn.dataset.stage;
        thirdWrap.style.display = ctx.needsThird ? 'block' : 'none';
        backdrop.classList.add('open');
    }

    function closeModal() {
        backdrop.classList.remove('open');
        ctx = null;
    }

    function thirdSignerValue() {
        const checked = document.querySelector('input[name="sig_third_signer"]:checked');
        return checked ? checked.value : null;
    }

    function validateThird() {
        if (ctx.needsThird && !thirdSignerValue()) {
            setStatus('error', 'Choose who signs 3rd (Accounting or Vice Chancellor) before proceeding.');
            return false;
        }
        return true;
    }

    function markRowSigned(json) {
        const row = document.getElementById(ctx.rowId);
        if (row) {
            row.style.opacity = '.45';
            row.style.transition = 'opacity .4s';
            setTimeout(() => row.remove(), 600);
        }
        const nextLabel = json.nextStageLabel ? ' Forwarded to: <b>' + json.nextStageLabel + '</b>.' : ' Document is now fully signed.';
        setStatus('success', '<i class="ti ti-circle-check"></i> Signed successfully.' + nextLabel);
        submitBtn.style.display = 'none';
        confirmBtn.style.display = 'none';
        setTimeout(closeModal, 2200);
    }

    // ── Photo selection ──────────────────────────────────────────────────────
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        previewImg.src = URL.createObjectURL(file);
        previewWrap.style.display = 'block';
        dropzone.style.display = 'none';
        submitBtn.disabled = false;
        setStatus(null);
    });
    dropzone.addEventListener('click', () => input.click());

    // ── Manual blur-region drag (fallback when detection misses) ─────────────
    let dragStart = null;
    canvasEl.addEventListener('pointerdown', (e) => {
        if (!ctx || !ctx.photoPath) return; // only in needs-confirmation state
        const rect = canvasEl.getBoundingClientRect();
        dragStart = { x: e.clientX - rect.left, y: e.clientY - rect.top };
        selectBox.style.display = 'block';
        canvasEl.setPointerCapture(e.pointerId);
    });
    canvasEl.addEventListener('pointermove', (e) => {
        if (!dragStart) return;
        const rect = canvasEl.getBoundingClientRect();
        const cur = { x: e.clientX - rect.left, y: e.clientY - rect.top };
        const x = Math.max(Math.min(dragStart.x, cur.x), 0);
        const y = Math.max(Math.min(dragStart.y, cur.y), 0);
        const w = Math.min(Math.abs(cur.x - dragStart.x), rect.width - x);
        const h = Math.min(Math.abs(cur.y - dragStart.y), rect.height - y);
        Object.assign(selectBox.style, { left: x + 'px', top: y + 'px', width: w + 'px', height: h + 'px' });
    });
    canvasEl.addEventListener('pointerup', () => {
        if (!dragStart) return;
        const rect = canvasEl.getBoundingClientRect();
        const s = selectBox.style;
        const box = {
            x: parseFloat(s.left) / rect.width,
            y: parseFloat(s.top) / rect.height,
            w: parseFloat(s.width) / rect.width,
            h: parseFloat(s.height) / rect.height,
        };
        dragStart = null;
        if (box.w > 0.01 && box.h > 0.01) {
            ctx.manualBox = box;
            previewHint.textContent = 'Signature area marked — it will be blurred on the shared copy.';
        }
    });

    // ── Upload & detect ──────────────────────────────────────────────────────
    submitBtn.addEventListener('click', async () => {
        if (!ctx || !input.files[0]) return;
        if (!validateThird()) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Detecting signature…';
        setStatus('info', 'Uploading photo and scanning for the signature…');

        const fd = new FormData();
        fd.append('photo', input.files[0]);
        if (remarksEl.value) fd.append('remarks', remarksEl.value);
        const third = thirdSignerValue();
        if (third) fd.append('third_signer', third);

        try {
            const resp = await fetch(ctx.signUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await resp.json();

            if (resp.ok && json.success && json.signatoryStage) {
                markRowSigned(json);   // auto-detected and advanced
                return;
            }

            if (resp.ok && json.needsConfirmation) {
                ctx.photoPath = json.photoPath;
                setStatus('warn', json.message + '<br><span style="font-weight:500;">Optional: drag a box over the signature on the preview to blur it manually.</span>');
                submitBtn.style.display = 'none';
                confirmBtn.style.display = '';
                previewHint.textContent = 'Drag over the signature area to mark it for blurring (optional).';
                return;
            }

            setStatus('error', json.error || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Failed to process the photo.'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ti ti-scan"></i> Upload & Detect Signature';
        } catch {
            setStatus('error', 'Network error — please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ti ti-scan"></i> Upload & Detect Signature';
        }
    });

    // ── Manual confirm (detection missed / service down) ─────────────────────
    confirmBtn.addEventListener('click', async () => {
        if (!ctx || !ctx.photoPath) return;
        if (!validateThird()) return;

        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite;"></i> Confirming…';

        const payload = { photo_path: ctx.photoPath, remarks: remarksEl.value || null };
        if (ctx.manualBox) payload.boxes = [ctx.manualBox];
        const third = thirdSignerValue();
        if (third) payload.third_signer = third;

        try {
            const resp = await fetch(ctx.confirmUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await resp.json();
            if (resp.ok && json.success && json.signatoryStage) {
                markRowSigned(json);
                return;
            }
            setStatus('error', json.error || 'Failed to confirm.');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="ti ti-check"></i> Confirm Signed';
        } catch {
            setStatus('error', 'Network error — please try again.');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="ti ti-check"></i> Confirm Signed';
        }
    });

    // ── Routing forward (no photo) ───────────────────────────────────────────
    document.querySelectorAll('.sig-forward-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Forward ' + btn.dataset.number + ' to the next stage?')) return;
            btn.disabled = true;
            try {
                const resp = await fetch(btn.dataset.signUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({}),
                });
                const json = await resp.json();
                if (resp.ok && json.success) {
                    const row = document.getElementById(btn.dataset.rowId);
                    if (row) { row.style.opacity = '.45'; setTimeout(() => row.remove(), 500); }
                } else {
                    alert(json.error || 'Failed to forward.');
                    btn.disabled = false;
                }
            } catch {
                alert('Network error — please try again.');
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('.sig-open-modal-btn').forEach(btn => btn.addEventListener('click', () => openModal(btn)));
    document.getElementById('sigModalClose').addEventListener('click', closeModal);
    document.getElementById('sigCancelBtn').addEventListener('click', closeModal);
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });

    if (!document.getElementById('spinStyle')) {
        const s = document.createElement('style');
        s.id = 'spinStyle';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
