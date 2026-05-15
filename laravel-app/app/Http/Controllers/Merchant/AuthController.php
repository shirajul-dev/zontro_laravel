<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\PasswordChangedNotification;

class AuthController extends Controller
{
    /**
     * Show the merchant login page.
     */
    public function showLogin(): \Illuminate\Contracts\View\View
    {
        return view('m::auth.login');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): \Illuminate\Contracts\View\View
    {
        return view('m::auth.forgot');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLink(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('merchants')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => __($status)]);
            }
            return back()->with(['status' => __($status)]);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => __($status)], 422);
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(string $token): \Illuminate\Contracts\View\View
    {
        return view('m::auth.reset', ['token' => $token]);
    }

    /**
     * Handle a reset password request.
     */
    public function resetPassword(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('merchants')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
                $user->notify(new PasswordChangedNotification());
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => __($status), 'redirect' => route('merchant.login')]);
            }
            return redirect()->route('merchant.login')->with('status', __($status));
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => __($status)], 422);
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginField => $request->username,
            'password' => $request->password,
        ];

        if (Auth::guard('merchant')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Welcome back!',
                    'redirect' => route('merchant.dashboard')
                ]);
            }

            return redirect()->intended(route('merchant.dashboard'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Log the merchant out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('merchant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('merchant.login');
    }
}
