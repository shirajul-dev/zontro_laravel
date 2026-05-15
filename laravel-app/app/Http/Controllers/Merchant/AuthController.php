<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

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
