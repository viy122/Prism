<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Try to authenticate with email first, then with username
        $user = User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->onlyInput('email');
        }

        // Check if account is active
        if ($user->account_status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is not active. Please contact the administrator.',
            ])->onlyInput('email');
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Get the user's primary role
        $userRole = $user->roles()->first();

        if (!$userRole) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'No role assigned to your account.',
            ])->onlyInput('email');
        }

        // Redirect based on role
        return $this->redirectByRole($userRole->name);
    }

    /**
     * Redirect user based on their role
     */
    private function redirectByRole($roleName)
    {
        return match ($roleName) {
            'Office Head / Dean' => redirect()->route('office-head.dashboard'),
            'Finance Office'     => redirect()->route('finance-office.dashboard'),
            'Procurement Office' => redirect()->route('procurement-office.dashboard'),
            'Chancellor'         => redirect()->route('chancellor.dashboard'),
            'Vice Chancellor'    => redirect()->route('vice-chancellor.dashboard'),
            'Accounting Office'  => redirect()->route('accounting-office.dashboard'),
            default              => redirect()->route('prism.home'),
        };
    }

    /**
     * Instant demo login — only available outside production
     */
    public function demoLogin(string $role)
    {
        abort_if(app()->isProduction(), 403);

        $usernameMap = [
            'office-head'        => 'office_head',
            'finance-office'     => 'finance',
            'procurement-office' => 'procurement',
            'chancellor'         => 'chancellor',
            'vice-chancellor'    => 'vice_chancellor',
        ];

        $username = $usernameMap[$role] ?? null;
        abort_if(!$username, 404);

        $user = User::where('username', $username)->where('account_status', 'active')->firstOrFail();

        Auth::login($user);
        $user->update(['last_login_at' => now()]);

        $userRole = $user->roles()->first();
        abort_if(!$userRole, 403);

        return $this->redirectByRole($userRole->name);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('prism.home');
    }
}
