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
        $userRole = Auth::user()?->roles()->first()?->name;

        if ($userRole === 'System Administrator') {
            return $next($request);
        }

        if ($userRole !== $role) {
            $redirectRoute = $this->roleRoutes[$userRole] ?? 'prism.home';
            return redirect()->route($redirectRoute)
                ->with('error', 'Access denied. You do not have permission to view that area.');
        }

        return $next($request);
    }
}
