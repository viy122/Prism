@extends('prism.layouts.app')
@section('title', 'Fiscal Years | PRISM')
@section('content')
<div style="padding:24px;display:grid;gap:20px;">
    <h1>Fiscal Years</h1>
    <p>Finalization freezes planning and saves a report version. Existing PRs, signatures and payments can continue under their original year.</p>
    @if(session('status')) <p role="status">{{ session('status') }}</p> @endif
    @foreach($errors->all() as $error) <p role="alert" style="color:#b91c1c">{{ $error }}</p> @endforeach
    <form method="POST" action="{{ route('fiscal-years.store') }}">@csrf
        <label>New fiscal year <input name="year" type="number" min="2000" max="2200" value="{{ now()->year + 1 }}" required></label>
        <button type="submit">Create year</button>
    </form>
    @foreach($years as $fy)
        <section style="padding:20px;background:white;border:1px solid #ddd;border-radius:12px;">
            <h2>FY {{ $fy->year }} · {{ ucfirst($fy->status) }} {{ $fy->is_active ? '· Active' : '' }}</h2>
            <form method="POST" action="{{ route('fiscal-years.update', [$fy->year, $fy->status === 'locked' ? 'reopen' : 'finalize']) }}" style="margin:12px 0;">@csrf
                <label>Reason <input name="reason" minlength="5" maxlength="1000" required style="width:50%;padding:8px;"></label>
                <button type="submit">{{ $fy->status === 'locked' ? 'Reopen planning' : 'Finalize report and lock planning' }}</button>
            </form>
            @if(!$fy->is_active && $fy->status !== 'locked')
                <form method="POST" action="{{ route('fiscal-years.update', [$fy->year, 'activate']) }}">@csrf
                    <input type="hidden" name="reason" value="Set FY {{ $fy->year }} as the default year.">
                    <button type="submit">Set as active year</button>
                </form>
            @endif
            @foreach($snapshots->where('fiscal_year', $fy->year) as $snapshot)
                <p style="margin-top:10px;">Version {{ $snapshot->version }} · {{ $snapshot->created_at }} · {{ $snapshot->reason }}
                    <a href="{{ route('procurement-office.procurement-reports', ['year'=>$fy->year, 'version'=>$snapshot->version]) }}">View report</a>
                    <a href="{{ route('procurement-office.procurement-reports.export', ['year'=>$fy->year, 'version'=>$snapshot->version]) }}">Export CSV</a>
                </p>
            @endforeach
        </section>
    @endforeach
</div>
@endsection
