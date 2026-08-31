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
    .stat-card { background: var(--s50); border: 1px solid var(--s200); border-radius: 14px; padding: 18px 20px; }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; color: var(--s500); margin-bottom: 6px; }
    .stat-value { font-size: 26px; font-weight: 900; color: var(--s900); letter-spacing: -.5px; }
    .stat-value.green { color: #3b6d11; }
    .stat-value.crimson { color: var(--m); }

    .two-col { display: grid; grid-template-columns: 1fr 1.4fr; gap: 20px; align-items: start; }
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
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
            <p class="stat-label">Total Users</p>
            <p class="stat-value">{{ $summary['totalUsers'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Active</p>
            <p class="stat-value green">{{ $summary['activeUsers'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Inactive</p>
            <p class="stat-value crimson">{{ $summary['inactiveUsers'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Roles</p>
            <p class="stat-value">{{ $summary['totalRoles'] }}</p>
        </div>
    </div>

    <div class="charts-grid">
        <div class="card">
            <p class="card-eyebrow">Accounts</p>
            <h2 class="card-title" style="margin-bottom:16px;">Active vs Inactive</h2>
            <div class="chart-wrap">
                <canvas id="statusChart" data-summary="{{ json_encode($summary) }}"></canvas>
            </div>
        </div>
        <div class="card">
            <p class="card-eyebrow">Roles</p>
            <h2 class="card-title" style="margin-bottom:16px;">User Distribution per Role</h2>
            <div class="chart-wrap">
                <canvas id="roleChart" data-rows="{{ json_encode($usersByRole) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-eyebrow">Roles</p>
                    <h2 class="card-title">Users per Role</h2>
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
                <div>
                    <p class="card-eyebrow">Activity</p>
                    <h2 class="card-title">Recent Logins</h2>
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
