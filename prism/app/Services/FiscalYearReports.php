<?php

namespace App\Services;

use App\Http\Controllers\PrismChancellorController;
use App\Http\Controllers\PrismProcurementOfficeController;
use App\Models\AuditLog;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FiscalYearReports
{
    private const KEYS = [
        'procurement' => ['quarterlyRows', 'completedPurchases', 'delayedItems', 'ppmpValidationRows', 'offices', 'deliveryRows'],
        'chancellor' => ['generatedAt', 'offices', 'quarterOptions', 'accomplishmentRows', 'quarterlyRows', 'utilizationSummary', 'delayedByOffice', 'accomplishmentChart', 'utilizationChart', 'deliveryRows'],
    ];

    public function finalize(int $year, int $userId, string $reason): void
    {
        DB::transaction(function () use ($year, $userId, $reason) {
            $fy = FiscalYear::lockForUpdate()->findOrFail($year);
            if ($fy->status === 'locked') throw ValidationException::withMessages(['year' => 'This fiscal year is already finalized.']);
            $context = app(FiscalYearContext::class);
            [$oldYear, $oldFilter, $oldCapture] = [$context->year, $context->filterReads, $context->capturing];
            $context->year = $year;
            $context->filterReads = $context->capturing = true;
            try {
                $payload = [];
                foreach (['procurement' => PrismProcurementOfficeController::class, 'chancellor' => PrismChancellorController::class] as $kind => $controller) {
                    foreach ($kind === 'chancellor' ? ['', 'Q1', 'Q2', 'Q3', 'Q4'] : [''] as $quarter) {
                        $request = Request::create('/', 'GET', ['year' => $year, 'quarter' => $quarter]);
                        $data = app($controller)->procurementReports($request)->getData();
                        $payload[$kind][$quarter ?: 'all'] = array_intersect_key($data, array_flip(self::KEYS[$kind]));
                    }
                }
                $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $version = 1 + (int) DB::table('procurement_report_snapshots')->where('fiscal_year', $year)->max('version');
                DB::table('procurement_report_snapshots')->insert([
                    'fiscal_year' => $year, 'version' => $version, 'payload' => $json,
                    'checksum' => hash('sha256', $json), 'finalized_by_user_id' => $userId,
                    'reason' => $reason, 'created_at' => now(),
                ]);
                $fy->update(['status' => 'locked', 'locked_at' => now(), 'locked_by_user_id' => $userId]);
                $this->audit($year, $userId, 'fiscal_year_finalized', $reason, ['version' => $version]);
            } finally {
                [$context->year, $context->filterReads, $context->capturing] = [$oldYear, $oldFilter, $oldCapture];
            }
        });
    }

    public function archived(string $kind, Request $request): ?array
    {
        $context = app(FiscalYearContext::class);
        if ($context->capturing || !$context->year) return null;
        $version = $request->query('version');
        if (!$version && FiscalYear::find($context->year)?->status !== 'locked') return null;
        if ($version !== null) $request->validate(['version' => 'integer|min:1']);
        $snapshot = DB::table('procurement_report_snapshots')->where('fiscal_year', $context->year)
            ->when($version, fn ($q) => $q->where('version', $version))->orderByDesc('version')->first();
        abort_unless($snapshot, 404, 'Finalized report version not found.');
        abort_unless(hash_equals($snapshot->checksum, hash('sha256', $snapshot->payload)), 409, 'Report integrity check failed.');
        $quarter = in_array($request->query('quarter'), ['Q1', 'Q2', 'Q3', 'Q4'], true) ? $request->query('quarter') : 'all';
        $data = json_decode($snapshot->payload, true, 512, JSON_THROW_ON_ERROR)[$kind][$kind === 'chancellor' ? $quarter : 'all'];
        $data['offices'] = collect($data['offices'])->map(fn ($office) => (object) $office);
        $office = (string) $request->query('office', '');
        if ($office !== '') {
            foreach (['quarterlyRows', 'completedPurchases', 'delayedItems', 'ppmpValidationRows', 'accomplishmentRows', 'utilizationSummary', 'deliveryRows'] as $key) {
                if (isset($data[$key])) $data[$key] = array_values(array_filter($data[$key], fn ($row) => $row['office'] === $office));
            }
            if (isset($data['delayedByOffice'])) $data['delayedByOffice'] = array_intersect_key($data['delayedByOffice'], [$office => true]);
        }
        if ($kind === 'chancellor') {
            $a = collect($data['accomplishmentRows']);
            $u = collect($data['utilizationSummary']);
            $data['accomplishmentChart'] = ['procured' => $a->sum('procured'), 'remaining' => max(0, $a->sum('targeted') - $a->sum('procured'))];
            $data['utilizationChart'] = ['utilized' => round($u->sum('utilized')), 'unutilized' => max(0, round($u->sum('budget') - $u->sum('utilized')))];
        }
        return $data + [
            'selectedOffice' => $office, 'selectedQuarter' => $quarter === 'all' ? '' : $quarter,
            'reportVersion' => $snapshot->version, 'reportFinalizedAt' => $snapshot->created_at,
            'exportUrl' => route('procurement-office.procurement-reports.export', ['year' => $context->year, 'office' => $office, 'version' => $snapshot->version]),
        ];
    }

    public function audit(int $year, int $userId, string $action, string $reason, array $extra = []): void
    {
        AuditLog::create(['user_id' => $userId, 'action' => $action, 'auditable_type' => FiscalYear::class,
            'auditable_id' => $year, 'metadata_json' => ['reason' => $reason] + $extra]);
    }
}
