@extends('prism.layouts.app')
@section('title', 'Proposal Archive | Budget Office')

@push('page-css')
<style>
    :root {
        --s50:   #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400:  #94a3b8; --s500: #64748b; --s600: #475569;
        --s700:  #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-sub     { font-size: 13px; color: var(--s500); margin-top: 4px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .filter-select {
        height: 40px; border-radius: 99px; border: 1px solid var(--s200);
        background: var(--s50); padding: 0 30px 0 14px;
        font-size: 12.5px; font-weight: 600; color: var(--s700);
        font-family: 'Poppins', sans-serif; outline: none; cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
    }
    .filter-select:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-mid); }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 13px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--crimson-mid); }

    .btn-doc {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 12px; border-radius: 8px;
        border: 1px solid var(--s200); background: var(--white);
        color: var(--s600); font-size: 11.5px; font-weight: 700;
        text-decoration: none; transition: background .15s, border-color .15s, color .15s;
    }
    .btn-doc:hover { background: var(--crimson-mid); border-color: var(--crimson); color: var(--crimson); }
</style>
@endpush

@section('content')
<div class="page-shell">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-archive"></i></div>
        <div>
            <p class="page-hdr-eyebrow">Budget Office</p>
            <h1 class="page-hdr-title">Proposal Archive</h1>
            <p class="page-hdr-sub">Every PPMP Budget Office has reviewed, including ones already endorsed or approved — Proposal Review only shows what's still actionable.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-eyebrow">Full history</p>
                <h2 class="card-title">All Reviewed Proposals</h2>
                <p class="card-sub">{{ count($proposals) }} proposal{{ count($proposals) === 1 ? '' : 's' }} shown</p>
            </div>
            <form method="GET" action="{{ route('finance-office.proposal-archive') }}">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(count($proposals) === 0)
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:180px;border-radius:12px;border:1.5px dashed var(--s300);background:var(--s50);padding:32px;text-align:center;">
                <i class="ti ti-inbox" style="font-size:38px;color:var(--s300);"></i>
                <p style="font-size:13px;color:var(--s400);max-width:280px;line-height:1.6;">No proposals match this filter yet.</p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Office</th>
                        <th>FY</th>
                        <th>Submitted</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proposals as $p)
                        <tr>
                            <td style="font-weight:700;font-size:12px;color:var(--s500);">{{ $p['code'] ?: '—' }}</td>
                            <td style="font-weight:600;color:var(--s900);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p['title'] }}</td>
                            <td style="font-size:12px;font-weight:600;color:var(--s600);">{{ $p['office'] }}</td>
                            <td style="font-size:12px;color:var(--s500);">{{ $p['fiscalYear'] }}</td>
                            <td style="font-size:12px;color:var(--s500);">{{ $p['submittedDate'] }}</td>
                            <td style="font-weight:600;white-space:nowrap;">PHP {{ number_format($p['totalAmount']) }}</td>
                            <td><x-prism.status-badge :status="$p['status']" /></td>
                            <td>
                                <a class="btn-doc" href="{{ $p['documentUrl'] }}" target="_blank" rel="noopener">
                                    <i class="ti ti-file-text"></i> View PPMP
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
