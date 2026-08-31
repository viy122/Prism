<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Self-service "edit your own info" — reachable by clicking your name in the
 * sidebar, any role. Deliberately never accepts role_id/office_id: those stay
 * admin-only (see PrismAdminController::updateUser).
 */
class PrismProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'employee_number' => 'nullable|string|max:100',
            'position_title'  => 'nullable|string|max:255',
            'avatar'          => 'nullable|image|max:2048',
            'current_password' => 'nullable|string',
            'new_password'      => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if (!empty($validated['new_password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['error' => 'Current password is incorrect.'], 422);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->name            = $validated['name'];
        $user->username        = $validated['username'];
        $user->email           = $validated['email'];
        $user->employee_number = $validated['employee_number'] ?? null;
        $user->position_title  = $validated['position_title'] ?? null;
        $user->save();

        return response()->json([
            'success'   => true,
            'name'      => $user->name,
            'avatarUrl' => $user->avatar_path ? Storage::url($user->avatar_path) : null,
        ]);
    }
}
