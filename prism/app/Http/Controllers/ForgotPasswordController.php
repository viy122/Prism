<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService) {}

    public function showRequestForm()
    {
        return view('prism.auth.forgot-password');
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $this->passwordResetService->sendCode($data['email']);

        return redirect()
            ->route('password.reset.form', ['email' => $data['email']])
            ->with('status', 'If that email is registered, a 6-digit code has been sent to it.');
    }

    public function showResetForm(Request $request)
    {
        return view('prism.auth.reset-password', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $ok = $this->passwordResetService->verifyAndReset($data['email'], $data['code'], $data['password']);

        if (! $ok) {
            return back()->withErrors([
                'code' => 'That code is invalid or has expired. Please request a new one.',
            ])->onlyInput('email');
        }

        return redirect()->route('login')->with('status', 'Your password has been reset. Please sign in.');
    }
}
