<dialog id="createPpmpDialog" aria-labelledby="createPpmpTitle" style="margin:auto;border:1px solid #e2e8f0;border-radius:16px;padding:24px;width:min(420px,calc(100vw - 32px));color:#0f172a;box-shadow:0 20px 60px #0003;">
    <form id="createPpmpForm" method="POST" action="{{ route('office-head.budget-proposal.create-draft') }}">
        @csrf
        <h2 id="createPpmpTitle" style="font-size:20px;margin:0 0 18px;">Create PPMP</h2>
        <label for="planningFiscalYear" style="display:block;font-weight:600;margin-bottom:8px;">Planning Fiscal Year</label>
        <select name="planning_year" id="planningFiscalYear" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;background:white;font:inherit;">
            @forelse($planningYears as $fy)
                <option value="{{ $fy->year }}" @selected((int) old('planning_year', $defaultPlanningYear) === $fy->year)>FY {{ $fy->year }} · {{ $fy->year > now()->year ? 'Advance Planning' : ($fy->year === now()->year ? 'Current' : 'Past Year') }}</option>
            @empty
                <option value="">No open planning years</option>
            @endforelse
        </select>
        <p style="font-size:13px;color:#64748b;margin:12px 0;">You can prepare a draft for next year once an administrator registers that fiscal year. Only open years are available.</p>
        @error('planning_year')<p style="color:#991b1b;font-size:13px;">{{ $message }}</p>@enderror
        @error('fiscal_year')<p style="color:#991b1b;font-size:13px;">{{ $message }}</p>@enderror
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
            <button type="button" onclick="document.getElementById('createPpmpDialog').close()" style="padding:10px 16px;border:1px solid #cbd5e1;border-radius:8px;background:white;cursor:pointer;font:inherit;">Cancel</button>
            <button type="submit" @disabled($planningYears->isEmpty()) style="padding:10px 16px;border:0;border-radius:8px;background:#681012;color:white;cursor:pointer;font:inherit;">Create Draft</button>
        </div>
    </form>
</dialog>
<style>#createPpmpDialog::backdrop { background:rgba(15,23,42,.45); } #createPpmpDialog button:disabled { opacity:.5;cursor:not-allowed; }</style>
<script>
document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-create-ppmp]');
    if (trigger) { event.preventDefault(); document.getElementById('createPpmpDialog').showModal(); }
});
@if($errors->has('planning_year') || $errors->has('fiscal_year'))
document.getElementById('createPpmpDialog').showModal();
@endif
</script>
