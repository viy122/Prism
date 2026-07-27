<?php

namespace App\Services;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    private const TABLE = 'password_reset_tokens';
    private const CODE_TTL_MINUTES = 10;

    /**
     * Generate and email a one-time reset code for the given address, if a
     * matching account exists. Silently no-ops otherwise so the response is
     * identical either way and doesn't reveal whether the email is registered.
     */
    public function sendCode(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        DB::table(self::TABLE)->where('email', $email)->delete();
        DB::table(self::TABLE)->insert([
            'email' => $email,
            'token' => Hash::make($code),
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new PasswordResetOtpMail($code, self::CODE_TTL_MINUTES));
    }

    /**
     * Verify the code and, if valid, set the new password. Returns whether
     * the reset succeeded so callers can surface a validation error.
     */
    public function verifyAndReset(string $email, string $code, string $newPassword): bool
    {
        $record = DB::table(self::TABLE)->where('email', $email)->first();

        if (! $record) {
            return false;
        }

        $expired = now()->diffInMinutes(Carbon::parse($record->created_at)) > self::CODE_TTL_MINUTES;

        if ($expired || ! Hash::check($code, $record->token)) {
            return false;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);

        DB::table(self::TABLE)->where('email', $email)->delete();

        return true;
    }
}
