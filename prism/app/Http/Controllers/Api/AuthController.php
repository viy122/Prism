<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const ROLE_MAP = [
        'system-admin'       => 'System Administrator',
        'office-head'        => 'Office Head / Dean',
        'finance-office'     => 'Budget Office',
        'procurement-office' => 'Procurement Office',
        'chancellor'         => 'Chancellor',
        'vice-chancellor'    => 'Vice Chancellor',
        'accounting-office'  => 'Accounting Office',
        'bac'                => 'BAC',
        'cashier'            => 'Cashier',
    ];

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with(['roles', 'office'])
            ->where('email', $request->email)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        if ($user->account_status !== 'active') {
            return response()->json(['message' => 'Your account is inactive. Please contact the administrator.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'office']);

        return response()->json(['user' => $this->formatUser($user)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    private function formatUser(User $user): array
    {
        $role   = $user->roles->first();
        $office = $user->office;

        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'username'        => $user->username ?? '',
            'employee_number' => $user->employee_number ?? '',
            'office_id'       => $user->office_id,
            'office'          => $office?->name ?? '',
            'office_code'     => $office?->code ?? '',
            'position_title'  => $user->position_title ?? '',
            'role'            => self::ROLE_MAP[$role?->code ?? ''] ?? ($role?->name ?? ''),
            'account_status'  => $user->account_status,
        ];
    }
}
