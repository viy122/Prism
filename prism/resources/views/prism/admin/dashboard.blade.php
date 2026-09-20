@extends('prism.layouts.app')
@section('title', 'Admin | PRISM')

@push('head-extras')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
@endpush

@push('page-css')
<style>
    .page-hdr { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--border2); border-radius: var(--r); box-shadow: var(--sh); padding: 18px 22px; }
    .page-hdr-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--crimson-mid); border: 1px solid var(--crimson-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .page-hdr-icon i { font-size: 22px; color: var(--crimson); }
    .page-hdr-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--crimson); margin-bottom: 3px; }
    .page-hdr-title { font-size: 18px; font-weight: 800; color: var(--txt); letter-spacing: -.3px; }
    .page-hdr-sub { font-size: 12px; color: var(--txt3); margin-top: 2px; }

    .content {
        padding: 28px 32px 56px; flex: 1; display: flex; flex-direction: column; gap: 20px;
        --m: var(--crimson);
        --s50: #f8fafc; --s100: #f1f5f9; --s200: #e2e8f0; --s300: #cbd5e1;
        --s400: #94a3b8; --s500: #64748b; --s600: #475569; --s700: #334155; --s900: #0f172a;
        --sh-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    }

    .card { background: var(--white); border: 1px solid var(--s200); border-radius: 18px; padding: 22px 26px; box-shadow: var(--sh-sm); }
    .card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
    .card-title   { font-size: 17px; font-weight: 800; color: var(--s900); letter-spacing: -.2px; }
    .card-head    { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }

    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    .stat-card {
        position: relative; overflow: hidden;
        background: var(--white); border: 1px solid var(--s200);
        border-radius: 15px; padding: 18px 20px 16px;
        box-shadow: var(--sh-sm); transition: box-shadow .25s, border-color .25s, transform .2s;
    }
    .stat-card:hover { box-shadow: 0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05); border-color: rgba(104,16,18,.2); transform: translateY(-2px); }
    .stat-card::before { content: ''; position: absolute; left: 0; top: 16px; width: 4px; height: 38px; border-radius: 0 4px 4px 0; background: #681012; }
    .stat-icon { position: absolute; right: 16px; top: 16px; width: 38px; height: 38px; border-radius: 11px; background: rgba(104,16,18,.07); display: flex; align-items: center; justify-content: center; }
    .stat-icon svg { width: 19px; height: 19px; stroke: #681012; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--s400); margin-bottom: 9px; padding-right: 46px; }
    .stat-value { font-size: 28px; font-weight: 800; color: #681012; letter-spacing: -.7px; line-height: 1; margin-bottom: 5px; }
    .stat-hint { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

    .two-col { display: grid; grid-template-columns: 1fr 1.4fr; gap: 20px; align-items: start; }
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .chart-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .chart-icon-badge {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        background: rgba(104,16,18,.07);
        display: flex; align-items: center; justify-content: center;
    }
    .chart-icon-badge i { font-size: 18px; color: var(--m); }
    .chart-card-head-text { flex: 1; min-width: 0; }
    .chart-wrap  { position: relative; width: 100%; height: 230px; }

    .table-wrap { border-radius: 12px; border: 1px solid var(--s200); overflow: auto; background: var(--white); }
    table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--s700); text-align: left; }
    thead th { background: var(--s50); border-bottom: 1px solid var(--s200); padding: 11px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--s500); white-space: nowrap; }
    tbody td { padding: 12px 16px; border-bottom: 1px solid var(--s100); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }

    @media (max-width: 1000px) { .two-col { grid-template-columns: 1fr; } .charts-grid { grid-template-columns: 1fr; } .stat-grid { grid-template-columns: 1fr 1fr; } .content { padding: 16px 16px 40px; } }
</style>
@endpush

@section('content')

<div class="content">

    <div class="page-hdr">
        <div class="page-hdr-icon"><i class="ti ti-settings"></i></div>
        <div style="flex:1;">
            <p class="page-hdr-eyebrow">System Administrator</p>
            <h1 class="page-hdr-title">System Administration</h1>
            <p class="page-hdr-sub">Manage PRISM users, roles, and account access.</p>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
            <p class="stat-label">Total Users</p>
            <p class="stat-value">{{ $summary['totalUsers'] }}</p>
            <p class="stat-hint">All registered accounts</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <p class="stat-label">Active</p>
            <p class="stat-value">{{ $summary['activeUsers'] }}</p>
            <p class="stat-hint">Currently enabled accounts</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
            <p class="stat-label">Inactive</p>
            <p class="stat-value">{{ $summary['inactiveUsers'] }}</p>
            <p class="stat-hint">Disabled accounts</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
            <p class="stat-label">Roles</p>
            <p class="stat-value">{{ $summary['totalRoles'] }}</p>
            <p class="stat-hint">Defined access roles</p>
        </div>
    </div>

    <div class="charts-grid">
        <div class="card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-chart-donut"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Accounts</p>
                    <h2 class="card-title">Active vs Inactive</h2>
                </div>
            </div>
            <div class="chart-wrap" style="height:340px;">
                <canvas id="statusChart" data-summary="{{ json_encode($summary) }}"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="chart-card-head">
                <div class="chart-icon-badge"><i class="ti ti-shield-lock"></i></div>
                <div class="chart-card-head-text">
                    <p class="card-eyebrow">Roles</p>
                    <h2 class="card-title">User Distribution per Role</h2>
                </div>
            </div>
            <div class="chart-wrap">
                <canvas id="roleChart" data-rows="{{ json_encode($usersByRole) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div class="card">
            <div class="card-head">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="chart-icon-badge"><i class="ti ti-shield-lock"></i></div>
                    <div>
                        <p class="card-eyebrow">Roles</p>
                        <h2 class="card-title">Users per Role</h2>
                    </div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Role</th><th>Users</th></tr></thead>
                    <tbody>
                        @foreach($usersByRole as $row)
                        <tr>
                            <td style="font-weight:600;">{{ $row['role'] }}</td>
                            <td>{{ $row['count'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="chart-icon-badge"><i class="ti ti-history"></i></div>
                    <div>
                        <p class="card-eyebrow">Activity</p>
                        <h2 class="card-title">Recent Logins</h2>
                    </div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Last Login</th></tr></thead>
                    <tbody>
                        @forelse($recentLogins as $row)
                        <tr>
                            <td style="font-weight:600;">{{ $row['name'] }}</td>
                            <td style="color:var(--s500);">{{ $row['username'] }}</td>
                            <td style="font-size:12px;">{{ $row['role'] }}</td>
                            <td style="font-size:12px;color:var(--s500);white-space:nowrap;">{{ $row['lastLogin'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--s400);padding:22px;">No logins recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        const s = JSON.parse(statusEl.dataset.summary || '{}');
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{ data: [s.activeUsers || 0, s.inactiveUsers || 0], backgroundColor: ['#3b6d11', '#a32d2d'], borderWidth: 0 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            },
        });
    }

    const roleEl = document.getElementById('roleChart');
    if (roleEl) {
        const rows = JSON.parse(roleEl.dataset.rows || '[]');
        // Horizontal — the role list can grow past what a vertical bar
        // chart's x-axis labels could fit legibly.
        roleEl.parentElement.style.height = Math.max(230, rows.length * 34) + 'px';
        new Chart(roleEl, {
            type: 'bar',
            data: {
                labels: rows.map(r => r.role),
                datasets: [{ label: 'Users', data: rows.map(r => r.count), backgroundColor: '#681012', borderRadius: 4 }],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }
})();
</script>
@endpush
