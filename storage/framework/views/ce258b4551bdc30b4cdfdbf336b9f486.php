<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startPush('head-extras'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-css'); ?>
<style>
        /* ═══════════════════════════════════════
           DASHBOARD CONTENT
        ═══════════════════════════════════════ */
        .dash {
            padding: 28px 32px 56px;
            flex: 1;

            --m:      #681012;
            --m-dark: #4e0c0e;
            --white:  #ffffff;
            --s50:    #f8fafc;
            --s100:   #f1f5f9;
            --s200:   #e2e8f0;
            --s400:   #94a3b8;
            --s500:   #64748b;
            --s600:   #475569;
            --s700:   #334155;
            --s900:   #0f172a;
            --sh-sm:  0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
            --sh-md:  0 4px 16px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
            --sh-lg:  0 8px 28px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.05);
        }

        /* Page header card */
        .pd-header {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 18px; padding: 22px 26px; margin-bottom: 20px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; box-shadow: var(--sh-sm);
        }
        .pd-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 4px; }
        .pd-header h1 { font-size: 26px; font-weight: 800; color: var(--s900); letter-spacing: -.5px; margin-bottom: 5px; line-height: 1.15; }
        .pd-header-sub { font-size: 13px; color: var(--s600); line-height: 1.65; max-width: 500px; }
        .pd-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        .pd-btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--m); color: #fff;
            padding: 10px 18px; border-radius: 10px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 10px rgba(104,16,18,.22);
            transition: background .2s, transform .15s;
        }
        .pd-btn-primary:hover { background: var(--m-dark); transform: translateY(-1px); }
        .pd-btn-primary svg { width: 15px; height: 15px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        .pd-btn-outline {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--white); color: var(--m);
            border: 1.5px solid rgba(104,16,18,.35);
            padding: 10px 18px; border-radius: 10px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: border-color .2s, background .2s, transform .15s;
        }
        .pd-btn-outline:hover { border-color: var(--m); background: rgba(104,16,18,.04); transform: translateY(-1px); }
        .pd-btn-outline svg { width: 15px; height: 15px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* Stat cards */
        .pd-stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 13px; margin-bottom: 20px; }
        .pd-stat {
            background: var(--white); border: 1px solid var(--s200);
            border-radius: 15px; padding: 18px 20px 16px;
            position: relative; overflow: hidden; box-shadow: var(--sh-sm);
            transition: box-shadow .25s, border-color .25s, transform .2s;
        }
        .pd-stat:hover { box-shadow: var(--sh-lg); border-color: rgba(104,16,18,.2); transform: translateY(-2px); }
        .pd-stat::before {
            content: ""; position: absolute; left: 0; top: 16px;
            width: 4px; height: 38px; background: var(--m); border-radius: 0 4px 4px 0;
        }
        .pd-stat-icon {
            position: absolute; right: 16px; top: 16px;
            width: 38px; height: 38px; border-radius: 11px;
            background: rgba(104,16,18,.07);
            display: flex; align-items: center; justify-content: center;
        }
        .pd-stat-icon svg { width: 19px; height: 19px; stroke: var(--m); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pd-stat-label { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--s400); margin-bottom: 9px; }
        .pd-stat-value { font-size: 28px; font-weight: 800; color: var(--m); letter-spacing: -.7px; line-height: 1; margin-bottom: 5px; }
        .pd-stat-value.sm { font-size: 18px; letter-spacing: -.3px; }
        .pd-stat-hint { font-size: 11.5px; color: var(--s400); line-height: 1.5; }

        /* Section card */
        .pd-card { background: var(--white); border: 1px solid var(--s200); border-radius: 15px; padding: 20px 22px; box-shadow: var(--sh-sm); }
        .pd-card-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--m); margin-bottom: 3px; }
        .pd-card-title { font-size: 15px; font-weight: 800; color: var(--s900); margin: 0 0 16px; letter-spacing: -.2px; }
        .pd-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
        .pd-card-head .pd-card-title { margin-bottom: 0; }

        /* Badges */
        .pd-badge { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; line-height: 1.4; flex-shrink: 0; }
        .pd-badge-approved  { background: #dcfce7; color: #166534; }
        .pd-badge-pending   { background: #fef3c7; color: #92400e; }
        .pd-badge-returned  { background: #fee2e2; color: #991b1b; }
        .pd-badge-submitted { background: #dbeafe; color: #1e40af; }
        .pd-badge-info      { background: #e0f2fe; color: #0369a1; }
        .pd-badge-progress  { background: #ede9fe; color: #4c1d95; }
        .pd-badge-finance   { background: #f0fdf4; color: #166534; }

        /* Main 2-col */
        .pd-main-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px; margin-bottom: 15px; }

        /* Progress */
        .pd-prog-nums { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-bottom: 13px; }
        .pd-prog-box { background: var(--s50); border: 1px solid var(--s200); border-radius: 11px; padding: 13px 15px; }
        .pd-prog-box strong { display: block; font-size: 26px; font-weight: 800; color: var(--m); letter-spacing: -.5px; line-height: 1; }
        .pd-prog-box span   { display: block; font-size: 12px; font-weight: 600; color: var(--s400); margin-top: 3px; }
        .pd-prog-bar-wrap   { height: 9px; background: var(--s100); border-radius: 99px; overflow: hidden; margin-bottom: 11px; }
        .pd-prog-bar        { height: 100%; background: var(--m); border-radius: 99px; }
        .pd-prog-note       { background: rgba(104,16,18,.05); border-radius: 9px; padding: 9px 13px; font-size: 12px; color: var(--s600); line-height: 1.6; margin-bottom: 14px; }

        /* Updates */
        .pd-updates { display: flex; flex-direction: column; gap: 7px; }
        .pd-update {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 12px; background: var(--s50); border: 1px solid var(--s200);
            border-radius: 11px; padding: 12px 14px;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .pd-update:hover { border-color: rgba(104,16,18,.18); background: var(--white); box-shadow: var(--sh-sm); }
        .pd-update-title  { font-size: 13px; font-weight: 700; color: var(--s900); margin-bottom: 2px; }
        .pd-update-detail { font-size: 12px; color: var(--s600); line-height: 1.55; }
        .pd-update-meta   { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
        .pd-update-time   { font-size: 11px; color: var(--s400); white-space: nowrap; }

        /* Bottom 3-col */
        .pd-bottom-grid { display: grid; grid-template-columns: 1.1fr 1fr 0.85fr; gap: 15px; }

        /* PR links */
        .pd-pr-links { display: flex; flex-direction: column; gap: 9px; }
        .pd-pr-link {
            display: block; background: var(--s50); border: 1px solid var(--s200);
            border-radius: 11px; padding: 13px 15px; text-decoration: none;
            transition: border-color .2s, background .2s, box-shadow .2s, transform .15s;
        }
        .pd-pr-link:hover { border-color: rgba(104,16,18,.22); background: var(--white); box-shadow: var(--sh-md); transform: translateY(-1px); }
        .pd-pr-link-title { font-size: 13px; font-weight: 700; color: var(--s900); margin-bottom: 3px; display: flex; align-items: center; gap: 7px; }
        .pd-pr-link-title svg { width: 14px; height: 14px; stroke: var(--m); fill: none; flex-shrink: 0; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .pd-pr-link-sub { font-size: 12px; color: var(--s600); line-height: 1.55; }

        /* Legend */
        .pd-legend { display: flex; flex-wrap: wrap; gap: 13px; margin-bottom: 10px; font-size: 12px; color: var(--s600); }
        .pd-legend-item { display: flex; align-items: center; gap: 6px; }
        .pd-legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

        /* Chart */
        .pd-chart-wrap { position: relative; width: 100%; }

        /* Mobile */
        @media (max-width: 1024px) {
            .dash { padding: 16px 16px 40px; }
            .pd-stat-grid { grid-template-columns: repeat(2,1fr); }
            .pd-main-grid { grid-template-columns: 1fr; }
            .pd-bottom-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .pd-stat-grid { grid-template-columns: 1fr; }
            .pd-bottom-grid { grid-template-columns: 1fr; }
        }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="dash">

            <?php
                $quarterTotal = $summary['purchasedThisQuarter'] + $summary['unpurchasedThisQuarter'];
                $purchasedPercent = $quarterTotal > 0 ? round(($summary['purchasedThisQuarter'] / $quarterTotal) * 100) : 0;
            ?>

            
            <div class="pd-header">
                <div>
                    <p class="pd-eyebrow">Office Head / Dean</p>
                    <h1>Dashboard</h1>
                    <p class="pd-header-sub">Track proposed budgets, approvals, procurement movement, and PR readiness for your office.</p>
                </div>
                <div class="pd-header-actions">
                    <a href="<?php echo e(route('office-head.budget-proposal')); ?>" class="pd-btn-primary">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        New Budget Proposal
                    </a>
                    <a href="<?php echo e(route('office-head.purchase-requests')); ?>" class="pd-btn-outline">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View PRs
                    </a>
                </div>
            </div>

            
            <div class="pd-stat-grid">
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
                    <div class="pd-stat-label">Total Proposed Items</div>
                    <div class="pd-stat-value"><?php echo e(number_format($summary['totalProposedItems'])); ?></div>
                    <div class="pd-stat-hint">Across active fiscal year proposals</div>
                </article>
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                    <div class="pd-stat-label">Total Proposed Budget</div>
                    <div class="pd-stat-value sm">PHP <?php echo e(number_format($summary['totalProposedBudget'])); ?></div>
                    <div class="pd-stat-hint">Submitted and draft items combined</div>
                </article>
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                    <div class="pd-stat-label">Items Approved</div>
                    <div class="pd-stat-value"><?php echo e(number_format($summary['approvedItems'])); ?></div>
                    <div class="pd-stat-hint">Eligible for PR preparation</div>
                </article>
                <article class="pd-stat">
                    <div class="pd-stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                    <div class="pd-stat-label">Pending Approval</div>
                    <div class="pd-stat-value"><?php echo e(number_format($summary['pendingItems'])); ?></div>
                    <div class="pd-stat-hint">Under Finance or Chancellor review</div>
                </article>
            </div>

            
            <div class="pd-main-grid">

                
                <article class="pd-card">
                    <div class="pd-card-head">
                        <div>
                            <p class="pd-card-eyebrow">This Quarter</p>
                            <h2 class="pd-card-title">Procurement Progress</h2>
                        </div>
                        <span class="pd-badge pd-badge-info"><?php echo e($purchasedPercent); ?>% purchased</span>
                    </div>
                    <div class="pd-prog-nums">
                        <div class="pd-prog-box">
                            <strong><?php echo e($summary['purchasedThisQuarter']); ?></strong>
                            <span>Purchased</span>
                        </div>
                        <div class="pd-prog-box">
                            <strong><?php echo e($summary['unpurchasedThisQuarter']); ?></strong>
                            <span>Unpurchased</span>
                        </div>
                    </div>
                    <div class="pd-prog-bar-wrap" role="progressbar" aria-valuenow="<?php echo e($purchasedPercent); ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="pd-prog-bar" style="width:<?php echo e($purchasedPercent); ?>%"></div>
                    </div>
                    <p class="pd-prog-note"><?php echo e($summary['unpurchasedThisQuarter']); ?> item<?php echo e($summary['unpurchasedThisQuarter'] === 1 ? '' : 's'); ?> still need procurement movement this quarter.</p>
                    <div class="pd-legend">
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#681012"></span>Purchased</span>
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#e2e8f0"></span>Unpurchased</span>
                    </div>
                    <div class="pd-chart-wrap" style="height:160px;">
                        <canvas id="donutChart" role="img"
                            aria-label="Donut: <?php echo e($summary['purchasedThisQuarter']); ?> purchased vs <?php echo e($summary['unpurchasedThisQuarter']); ?> unpurchased"
                            data-purchased="<?php echo e($summary['purchasedThisQuarter']); ?>"
                            data-unpurchased="<?php echo e($summary['unpurchasedThisQuarter']); ?>">
                        </canvas>
                    </div>
                </article>

                
                <article class="pd-card">
                    <p class="pd-card-eyebrow">Activity</p>
                    <h2 class="pd-card-title">Recent Status Updates</h2>
                    <div class="pd-updates">
                        <?php $__empty_1 = true; $__currentLoopData = $recentUpdates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $sc = match(strtolower($update['status'])) {
                                    'approved'                                   => 'pd-badge-approved',
                                    'returned'                                   => 'pd-badge-returned',
                                    'pending'                                    => 'pd-badge-pending',
                                    'submitted'                                  => 'pd-badge-submitted',
                                    'in progress', 'progress'                    => 'pd-badge-progress',
                                    'finance review completed', 'finance review' => 'pd-badge-finance',
                                    default                                      => 'pd-badge-info',
                                };
                            ?>
                            <div class="pd-update">
                                <div>
                                    <div class="pd-update-title"><?php echo e($update['title']); ?></div>
                                    <div class="pd-update-detail"><?php echo e($update['details']); ?></div>
                                </div>
                                <div class="pd-update-meta">
                                    <span class="pd-badge <?php echo e($sc); ?>"><?php echo e($update['status']); ?></span>
                                    <span class="pd-update-time"><?php echo e($update['time']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px 0;">No recent updates.</p>
                        <?php endif; ?>
                    </div>
                </article>

            </div>

            
            <div class="pd-bottom-grid">

                
                <article class="pd-card">
                    <p class="pd-card-eyebrow">Budget Overview</p>
                    <h2 class="pd-card-title">Monthly Budget Utilization</h2>
                    <div class="pd-legend">
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#681012"></span>Budget used (PHP)</span>
                    </div>
                    <div class="pd-chart-wrap" style="height:196px;">
                        <canvas id="barChart" role="img" aria-label="Monthly budget utilization bar chart">Monthly budget data.</canvas>
                    </div>
                </article>

                
                <article class="pd-card">
                    <p class="pd-card-eyebrow">Proposal Breakdown</p>
                    <h2 class="pd-card-title">Items by Status</h2>
                    <div class="pd-legend">
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#166534"></span>Approved</span>
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#92400e"></span>Pending</span>
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#991b1b"></span>Returned</span>
                        <span class="pd-legend-item"><span class="pd-legend-dot" style="background:#334155"></span>Draft</span>
                    </div>
                    <div class="pd-chart-wrap" style="height:196px;">
                        <canvas id="statusChart" role="img" aria-label="Items by status horizontal bar chart"
                            data-approved="<?php echo e($summary['approvedItems']); ?>"
                            data-pending="<?php echo e($summary['pendingItems']); ?>"
                            data-returned="<?php echo e($summary['returnedItems'] ?? 0); ?>"
                            data-draft="<?php echo e($summary['draftItems'] ?? 0); ?>">
                        </canvas>
                    </div>
                </article>

                
                <article class="pd-card">
                    <div class="pd-card-head">
                        <div>
                            <p class="pd-card-eyebrow">Next Actions</p>
                            <h2 class="pd-card-title">PR Readiness</h2>
                        </div>
                        <span class="pd-badge pd-badge-pending"><?php echo e($summary['pendingItems']); ?> pending</span>
                    </div>
                    <div class="pd-pr-links">
                        <a href="<?php echo e(route('office-head.budget-proposal')); ?>" class="pd-pr-link">
                            <div class="pd-pr-link-title">
                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                Prepare budget proposal
                            </div>
                            <div class="pd-pr-link-sub">Encode items and run market scoping before submission.</div>
                        </a>
                        <a href="<?php echo e(route('office-head.my-proposals')); ?>" class="pd-pr-link">
                            <div class="pd-pr-link-title">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Review proposal status
                            </div>
                            <div class="pd-pr-link-sub">Check Finance and Chancellor remarks for returned items.</div>
                        </a>
                        <a href="<?php echo e(route('office-head.purchase-requests')); ?>" class="pd-pr-link">
                            <div class="pd-pr-link-title">
                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                View Purchase Requests
                            </div>
                            <div class="pd-pr-link-sub">Track the status of your office's purchase requests.</div>
                        </a>
                    </div>
                </article>

            </div>
        </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const pp = "'Poppins', sans-serif";
    Chart.defaults.font.family = pp;

    /* DONUT */
    const dEl = document.getElementById('donutChart');
    if (dEl) {
        new Chart(dEl, {
            type: 'doughnut',
            data: {
                labels: ['Purchased','Unpurchased'],
                datasets: [{
                    data: [parseInt(dEl.dataset.purchased||0), parseInt(dEl.dataset.unpurchased||0)],
                    backgroundColor: ['#681012','#e2e8f0'],
                    borderWidth: 0, borderRadius: 4, hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => '  '+c.label+': '+c.parsed+' item'+(c.parsed!==1?'s':'') }}
                }
            }
        });
    }

    /* BAR — Monthly */
    const bEl = document.getElementById('barChart');
    if (bEl) {
        new Chart(bEl, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Budget Used (PHP)',
                    data: <?php echo json_encode($summary['monthlyBudgetUsage'] ?? array_fill(0,12,0)); ?>,
                    backgroundColor: '#681012', borderRadius: 6, borderSkipped: false
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: pp } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10, family: pp }, callback: v => v>=1000?'PHP '+Math.round(v/1000)+'k':'PHP '+v } }
                }
            }
        });
    }

    /* HORIZONTAL BAR — Status */
    const sEl = document.getElementById('statusChart');
    if (sEl) {
        new Chart(sEl, {
            type: 'bar',
            data: {
                labels: ['Approved','Pending','Returned','Draft'],
                datasets: [{
                    data: [parseInt(sEl.dataset.approved||0),parseInt(sEl.dataset.pending||0),parseInt(sEl.dataset.returned||0),parseInt(sEl.dataset.draft||0)],
                    backgroundColor: ['#166534','#92400e','#991b1b','#334155'],
                    borderRadius: 6, borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10, family: pp } } },
                    y: { grid: { display: false }, ticks: { color: '#334155', font: { size: 12, weight: '600', family: pp } } }
                }
            }
        });
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('prism.layouts.office-head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Prism\resources\views/prism/office-head/dashboard.blade.php ENDPATH**/ ?>