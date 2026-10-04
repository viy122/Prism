<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Services\FiscalYearReports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiscalYearController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->roles()->where('name', 'System Administrator')->exists(), 403);
    }

    public function index()
    {
        $this->authorizeAdmin();
        return view('prism.admin.fiscal-years', [
            'years' => FiscalYear::orderByDesc('year')->get(),
            'snapshots' => DB::table('procurement_report_snapshots')->select('fiscal_year', 'version', 'created_at', 'reason')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request, FiscalYearReports $reports)
    {
        $this->authorizeAdmin();
        $data = $request->validate(['year' => 'required|integer|min:2000|max:2200|unique:fiscal_years,year']);
        FiscalYear::create(['year' => $data['year']]);
        $reports->audit($data['year'], auth()->id(), 'fiscal_year_created', 'Opened for planning.');
        return back()->with('status', 'Fiscal year created. Existing records are retained.');
    }

    public function update(Request $request, int $year, string $action, FiscalYearReports $reports)
    {
        $this->authorizeAdmin();
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000']);
        DB::transaction(function () use ($year, $action, $reports, $data) {
            $fy = FiscalYear::lockForUpdate()->findOrFail($year);
            if ($action === 'finalize') {
                $reports->finalize($year, auth()->id(), $data['reason']);
            } elseif ($action === 'activate') {
                abort_if($fy->status === 'locked', 422, 'Reopen the year before making it active.');
                FiscalYear::query()->update(['is_active' => false]);
                $fy->update(['is_active' => true]);
                $reports->audit($year, auth()->id(), 'fiscal_year_activated', $data['reason']);
            } else {
                abort_unless($fy->status === 'locked', 422, 'This year is already open.');
                $fy->update(['status' => 'open', 'locked_at' => null, 'locked_by_user_id' => null]);
                $reports->audit($year, auth()->id(), 'fiscal_year_reopened', $data['reason']);
            }
        });
        return back()->with('status', 'Fiscal year updated. Previous report versions remain preserved.');
    }
}
