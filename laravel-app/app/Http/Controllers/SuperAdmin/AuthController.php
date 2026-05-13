<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        $brandingService = app(\App\Services\Common\BrandingService::class);
        $logoUrl = $brandingService->getAssetUrl(null, 'logo');
        return view('superadmin.pages.auth.login', ['logoUrl' => $logoUrl]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::guard('superadmin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'redirect' => route('superadmin.dashboard')
                ]);
            }

            return redirect()->intended(route('superadmin.dashboard'));
        }

        $message = 'The provided credentials do not match our records.';

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'status' => false,
                'message' => $message
            ], 401);
        }

        return back()->withErrors([
            'email' => $message,
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('superadmin.login');
    }
}
