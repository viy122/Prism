@php
    $reportYear = app(\App\Services\FiscalYearContext::class)->year;
    $reportVersions = \Illuminate\Support\Facades\DB::table('procurement_report_snapshots')->where('fiscal_year', $reportYear)->orderByDesc('version')->get(['version', 'created_at']);
@endphp
<div style="padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:14px;">
    <strong>FY {{ $reportYear }}</strong> ·
    @isset($reportVersion)
        Finalized report · Version {{ $reportVersion }} · As of {{ $reportFinalizedAt }}
    @else
        Live report · {{ now()->format('M d, Y') }}
    @endisset
    @foreach($reportVersions as $version)
        <a style="margin-left:12px;" href="{{ request()->fullUrlWithQuery(['year'=>$reportYear, 'version'=>$version->version]) }}">Version {{ $version->version }}</a>
    @endforeach
</div>
