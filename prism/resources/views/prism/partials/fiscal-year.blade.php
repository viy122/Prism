@if($fySelected && !request()->routeIs('*.office-assets', '*.office-assets.*', 'office-assets.*'))
<script>
// Pin the displayed FY in this tab's URL (also used by form/AJAX referrers).
(() => { const u = new URL(location.href); if (!u.searchParams.has('year')) { u.searchParams.set('year', @json($fySelected)); history.replaceState(null, '', u); } })();
</script>
<style>@media print { .fiscal-year-toolbar { display:none !important; } }</style>
<div class="fiscal-year-toolbar" style="margin:16px 24px 0;padding:12px 16px;border:1px solid #e2e8f0;border-radius:12px;background:white;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
    <label for="globalFiscalYear" style="font-size:12px;font-weight:700;">Fiscal Year</label>
    {{-- Inline handlers include document in their scope: bare URL resolves to document.URL. --}}
    <select id="globalFiscalYear" style="padding:8px;border:1px solid #cbd5e1;border-radius:8px;" onchange="const u=new window.URL(window.location.href);u.searchParams.set('year',this.value);['proposal','fy','version','page'].forEach(k=>u.searchParams.delete(k));window.location.assign(u);">
        @foreach($fyYears as $fy)
            <option value="{{ $fy->year }}" @selected($fy->year === $fySelected)>FY {{ $fy->year }}{{ $fy->is_active ? ' • Active' : '' }}{{ $fy->status === 'locked' ? ' • Locked' : '' }}</option>
        @endforeach
    </select>
    <span style="font-size:12px;color:#64748b;">{{ $fyLocked ? 'Planning locked. Finalized reports are preserved; existing transactions may continue.' : 'Reports and planning show the selected year.' }}</span>
    @if(request()->routeIs('*.for-my-signature*', 'accounting-office.*', 'cashier.*', 'bac.*'))
        <span style="font-size:12px;color:#92400e;">Pending work includes all fiscal years.</span>
    @endif
    @if(auth()->user()?->roles()->where('name', 'System Administrator')->exists())
        <a href="{{ route('fiscal-years.index', ['year'=>$fySelected]) }}" style="margin-left:auto;font-size:12px;">Manage fiscal years</a>
    @endif
</div>
@if($fyLocked && request()->routeIs('office-head.budget-proposal*', 'office-head.market-scoping*', 'procurement-office.annual-procurement-plan*', 'finance-office.proposal-review*', 'chancellor.budget-approval*'))
<script>
window.addEventListener('DOMContentLoaded', () => {
    // Backend guards enforce the lock; disable the matching editing controls too.
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(form => {
        // New drafts choose their own open planning year in the modal.
        if (form.id === 'createPpmpForm') return;
        if (/budget-proposal|proposal-review|budget-approval|market-scoping|annual-procurement-plan/.test(form.action)) {
            form.querySelectorAll('button,input,select,textarea').forEach(el => el.disabled = true);
        }
    });
    document.querySelectorAll('.js-override-btn,.js-save-btn,.js-row-edit-btn,.js-row-save-btn').forEach(el => { el.disabled = true; el.title = 'Fiscal year finalized'; });
});
</script>
@endif
@endif
