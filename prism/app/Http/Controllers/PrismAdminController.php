<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrismAdminController extends Controller
{
    public function dashboard(): View
    {
        $usersByRole = Role::withCount('users')
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => ['role' => $role->name, 'count' => $role->users_count])
            ->all();

        $recentLogins = User::whereNotNull('last_login_at')
            ->with('roles')
            ->orderByDesc('last_login_at')
            ->take(10)
            ->get()
            ->map(fn ($user) => [
                'name'      => $user->name,
                'username'  => $user->username,
                'role'      => $user->roles->first()?->name ?? '—',
                'lastLogin' => $user->last_login_at->format('M d, Y g:i A'),
            ])
            ->all();

        return view('prism.admin.dashboard', $this->withCommon('dashboard', [
            'pageTitle'    => 'System Administration',
            'summary'      => [
                'totalUsers'    => User::count(),
                'activeUsers'   => User::where('account_status', 'active')->count(),
                'inactiveUsers' => User::where('account_status', '!=', 'active')->count(),
                'totalRoles'    => Role::count(),
            ],
            'usersByRole'  => $usersByRole,
            'recentLogins' => $recentLogins,
        ]));
    }

    public function userManagement(): View
    {
        $users = User::withTrashed()
            ->with(['roles', 'office'])
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id'            => $user->id,
                'name'          => $user->name,
                'username'      => $user->username,
                'email'         => $user->email,
                'positionTitle' => $user->position_title ?? '—',
                'office'        => $user->office?->code ?? '—',
                'officeId'      => $user->office_id,
                'role'          => $user->roles->first()?->name ?? '—',
                'roleId'        => $user->roles->first()?->id,
                'status'        => $user->deleted_at ? 'deleted' : ($user->account_status ?? 'active'),
                'lastLogin'     => $user->last_login_at?->format('M d, Y g:i A') ?? 'Never',
                'isSelf'        => $user->id === auth()->id(),
            ])
            ->all();

        return view('prism.admin.user-management', $this->withCommon('user-management', [
            'pageTitle' => 'User Management',
            'users'     => $users,
            'roles'     => Role::orderBy('name')->get(['id', 'name'])->toArray(),
            'offices'   => Office::orderBy('name')->get(['id', 'name'])->toArray(),
        ]));
    }

    public function storeUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => 'required|string|max:100|unique:users,username',
            'email'           => 'required|email|max:255|unique:users,email',
            'employee_number' => 'nullable|string|max:100',
            'position_title'  => 'nullable|string|max:255',
            'password'        => 'required|string|min:8',
            'role_id'         => 'required|exists:roles,id',
            'office_id'       => 'required|exists:offices,id',
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'username'        => $validated['username'],
            'email'           => $validated['email'],
            'employee_number' => $validated['employee_number'] ?? null,
            'position_title'  => $validated['position_title'] ?? null,
            'password'        => Hash::make($validated['password']),
            'office_id'       => $validated['office_id'],
            'account_status'  => 'active',
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($validated['role_id'], [
            'assigned_by_user_id' => auth()->id(),
            'assigned_at'         => now(),
        ]);

        return response()->json(['success' => true, 'userId' => $user->id]);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'position_title'  => 'nullable|string|max:255',
            'role_id'         => 'required|exists:roles,id',
            'office_id'       => 'required|exists:offices,id',
        ]);

        $user->update([
            'name'           => $validated['name'],
            'username'       => $validated['username'],
            'email'          => $validated['email'],
            'position_title' => $validated['position_title'] ?? null,
            'office_id'      => $validated['office_id'],
        ]);

        $user->roles()->sync([
            $validated['role_id'] => ['assigned_by_user_id' => auth()->id(), 'assigned_at' => now()],
        ]);

        return response()->json(['success' => true]);
    }

    public function deactivateUser(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot deactivate your own account.'], 422);
        }

        $user->update(['account_status' => 'inactive']);

        return response()->json(['success' => true]);
    }

    public function reactivateUser(User $user): JsonResponse
    {
        $user->update(['account_status' => 'active']);

        return response()->json(['success' => true]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate(['password' => 'required|string|min:8']);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['success' => true]);
    }

    /**
     * Lets an admin manually add a shop link as a Market Scoping price
     * source — the price-api service normally only picks up new vendors
     * through its own automatic discovery (SerpApi-driven, still requiring
     * a human to approve each candidate); this is the direct path where the
     * admin's own submission already is that approval.
     */
    public function marketSources(): View
    {
        $sources = [];
        $vendors = [];
        $serviceDown = false;

        try {
            $sourcesResp = Http::timeout(8)->get($this->priceApiUrl('/admin/sources'));
            $vendorsResp = Http::timeout(8)->get($this->priceApiUrl('/admin/vendors/pending'));

            if ($sourcesResp->successful()) {
                $sources = $sourcesResp->json('sources', []);
            }
            if ($vendorsResp->successful()) {
                $vendors = $vendorsResp->json('vendors', []);
            }
            if (!$sourcesResp->successful() && !$vendorsResp->successful()) {
                $serviceDown = true;
            }
        } catch (\Throwable) {
            $serviceDown = true;
        }

        return view('prism.admin.market-sources', $this->withCommon('market-sources', [
            'pageTitle'   => 'Market Scoping Sources',
            'sources'     => $sources,
            'vendors'     => $vendors,
            'serviceDown' => $serviceDown,
        ]));
    }

    public function addMarketSource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'        => 'required|url|max:2048',
            'seller'     => 'nullable|string|max:255',
            'department' => 'nullable|string|max:100',
        ]);

        try {
            $response = Http::timeout(15)->post($this->priceApiUrl('/admin/vendors/add'), array_filter([
                'url'        => $validated['url'],
                'seller'     => $validated['seller'] ?? null,
                'department' => $validated['department'] ?? null,
            ]));
        } catch (\Throwable) {
            return response()->json(['error' => 'The price scoping service is unreachable right now.'], 503);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'The price scoping service returned an error.'], 502);
        }

        $body = $response->json();
        if (empty($body['detected'])) {
            return response()->json(['error' => $body['message'] ?? 'Could not recognize that link as a supported shop platform.'], 422);
        }

        return response()->json(['success' => true, 'vendor' => $body['vendor']]);
    }

    public function removeMarketSource(int $vendorId): JsonResponse
    {
        try {
            $response = Http::timeout(10)->post($this->priceApiUrl("/admin/vendors/{$vendorId}/remove"));
        } catch (\Throwable) {
            return response()->json(['error' => 'The price scoping service is unreachable right now.'], 503);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'Could not remove that source.'], 422);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mutes a chronically-failing source (built-in or manually-added alike)
     * without touching its scraper code — it just stops being queried until
     * re-enabled here.
     */
    public function disableMarketSource(Request $request, string $sourceName): JsonResponse
    {
        try {
            $response = Http::timeout(10)->post(
                $this->priceApiUrl("/admin/sources/{$sourceName}/disable"),
                ['reason' => $request->input('reason')]
            );
        } catch (\Throwable) {
            return response()->json(['error' => 'The price scoping service is unreachable right now.'], 503);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'Could not disable that source.'], 422);
        }

        return response()->json(['success' => true]);
    }

    public function enableMarketSource(string $sourceName): JsonResponse
    {
        try {
            $response = Http::timeout(10)->post($this->priceApiUrl("/admin/sources/{$sourceName}/enable"));
        } catch (\Throwable) {
            return response()->json(['error' => 'The price scoping service is unreachable right now.'], 503);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'Could not enable that source.'], 422);
        }

        return response()->json(['success' => true]);
    }

    private function priceApiUrl(string $path): string
    {
        return rtrim(config('services.price_api.url'), '/') . $path;
    }

    private function withCommon(string $activePage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'admin',
            'activeModulePage' => $activePage,
            'brandHref'        => route('admin.dashboard'),
            'roleLabel'        => 'System Administrator',
            'roleInitials'     => 'SA',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Administration pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',       'label' => 'Dashboard',       'href' => route('admin.dashboard'),       'icon' => 'layout-dashboard'],
                ['slug' => 'user-management', 'label' => 'User Management', 'href' => route('admin.user-management'), 'icon' => 'users'],
                ['slug' => 'market-sources',  'label' => 'Market Sources',  'href' => route('admin.market-sources'),  'icon' => 'link'],
            ],
        ], $data);
    }
}
