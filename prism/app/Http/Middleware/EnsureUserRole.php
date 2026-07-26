<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    private array $roleRoutes = [
        'Office Head / Dean'  => 'office-head.dashboard',
        'Budget Office'       => 'finance-office.dashboard',
        'Procurement Office'  => 'procurement-office.dashboard',
        'Chancellor'          => 'chancellor.dashboard',
        'Vice Chancellor'     => 'vice-chancellor.dashboard',
        'Accounting Office'   => 'accounting-office.dashboard',
        'BAC'                 => 'bac.dashboard',
        'Cashier'             => 'cashier.dashboard',
    ];

    public function handle(Request $request, Closure $next, string $role): Response
    {
        // A user can hold more than one role at once (e.g. a Vice Chancellor
        // who is also Dean of their home college) — check every role they
        // hold rather than just roles()->first(), whose order isn't
        // guaranteed and shouldn't decide which of a dual-hat user's areas
        // they can reach.
        $userRoleNames = Auth::user()?->roles()->pluck('name')->all() ?? [];

        if (in_array('System Administrator', $userRoleNames, true)) {
            return $next($request);
        }

        if (in_array($role, $userRoleNames, true)) {
            return $next($request);
        }

        $redirectRoute = $this->roleRoutes[$userRoleNames[0] ?? null] ?? 'prism.home';
        return redirect()->route($redirectRoute)
            ->with('error', 'Access denied. You do not have permission to view that area.');
    }
}
