<?php
// Benchmark diagnostics only: reads configuration and counts, never changes application data.
require __DIR__.'/../../prism/vendor/autoload.php';
$app = require __DIR__.'/../../prism/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$counts = [];
foreach (['budget_proposals', 'purchase_requests'] as $table) {
    $counts[$table] = Illuminate\Support\Facades\DB::table($table)->whereNull('deleted_at')
        ->selectRaw('fiscal_year, COUNT(*) as count')->groupBy('fiscal_year')->get();
}
$user = App\Models\User::where('username', 'procurement')->first();
echo json_encode([
    'laravel' => $app->version(), 'php' => PHP_VERSION,
    'environment' => $app->environment(), 'debug' => config('app.debug'),
    'session_driver' => config('session.driver'), 'cache_store' => config('cache.default'),
    'database_driver' => config('database.default'),
    'database_version' => Illuminate\Support\Facades\DB::selectOne('SELECT VERSION() AS version')->version,
    'fiscal_years' => App\Models\FiscalYear::orderBy('year')->get(['year','status','is_active']),
    'counts' => $counts,
    'demo_account' => $user ? ['active' => $user->account_status === 'active', 'roles' => $user->roles()->pluck('name')] : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
