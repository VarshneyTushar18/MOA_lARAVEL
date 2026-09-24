<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ConsoleAccountController extends Controller
{
    private const ALLOWED_RESET_EMAIL = 'philtbi533@gmail.com';

    private function isAllowedResetEmail(?string $email): bool
    {
        return strtolower(trim((string) $email)) === self::ALLOWED_RESET_EMAIL;
    }

    public function showChangePasswordForm()
    {
        return view('console.account.password');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = Auth::user();

        if (! $user || ! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->password = $validated['password'];
        $user->save();

        return redirect()->route('console.account.password')
            ->with('message', 'Password updated successfully.');
    }

    public function showForgotPasswordForm()
    {
        return view('console.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email', self::ALLOWED_RESET_EMAIL)));

        if ($email === '') {
            $email = self::ALLOWED_RESET_EMAIL;
        }

        if (! $this->isAllowedResetEmail($email)) {
            return back()->withErrors([
                'email' => 'Password reset is not available for this email address.',
            ]);
        }

        $status = Password::sendResetLink([
            'email' => self::ALLOWED_RESET_EMAIL,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('message', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('console.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ]);

        if (! $this->isAllowedResetEmail($request->input('email'))) {
            return back()->withErrors([
                'email' => 'Password reset is not available for this email address.',
            ]);
        }

        $status = Password::reset(
            array_merge($request->only('password', 'password_confirmation', 'token'), [
                'email' => self::ALLOWED_RESET_EMAIL,
            ]),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('console.login')->with('message', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
