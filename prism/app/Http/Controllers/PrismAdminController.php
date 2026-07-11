<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                'office'        => $user->office?->name ?? '—',
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
            ],
        ], $data);
    }
}
