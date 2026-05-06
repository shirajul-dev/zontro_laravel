<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpAdmin;
use App\Models\PpBrowserLog;
use App\Models\PpPermission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    /**
     * Display the login page.
     */
    public function login(Request $request): View|RedirectResponse
    {
        if ($this->isAuthenticated()) {
            return redirect()->route('admin.dashboard.index');
        }

        if ($this->isTwoFactorPending()) {
            return redirect()->route('admin.2fa.index');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle login request (AJAX).
     */
    public function loginRequest(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->username);
        $password = $request->password;

        $admin = PpAdmin::where('email', $username)
            ->orWhere('username', $username)
            ->first();

        if (!$admin) {
            return $this->errorResponse('Login Failed', 'The email/username or password you entered is incorrect.');
        }

        // Verify password (supporting legacy temp_password)
        $validPassword = Hash::check($password, (string) $admin->password)
            || ($admin->temp_password && Hash::check($password, (string) $admin->temp_password));

        if (!$validPassword) {
            return $this->errorResponse('Login Failed', 'The email/username or password you entered is incorrect.');
        }

        if ((string) $admin->status !== 'active') {
            return $this->errorResponse('Login Failed', 'Your account has been suspended. Please contact your admin.');
        }

        // Check permissions
        $permission = PpPermission::where('a_id', (string) $admin->a_id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if (!$permission) {
            return $this->errorResponse('Login Failed', 'You don’t have permission to manage any brands.');
        }

        // Handle 2FA
        if ((string) $admin->{'2fa_status'} === 'enable') {
            $token = $this->createBrowserSession($admin, $request);

            session([
                'piprapay.2fa_pending' => true,
                'piprapay.admin_id' => (string) $admin->a_id,
                'piprapay.browser_cookie' => $token
            ]);

            return response()->json([
                'status' => 'true',
                'target' => route('admin.2fa.index'),
                'csrf_token' => csrf_token(),
            ])->cookie('pp_2fa', $token, 60 * 24, '/', null, $request->isSecure(), true, false, 'Lax');
        }

        // Direct Login
        return $this->performLogin($admin, $permission, $request);
    }

    /**
     * Display 2FA page.
     */
    public function twoFactor(Request $request): View|RedirectResponse
    {
        if ($this->isAuthenticated()) {
            return redirect()->route('admin.dashboard.index');
        }

        if (!$this->isTwoFactorPending()) {
            return redirect()->route('native.auth.login');
        }

        return view('admin.auth.2fa');
    }

    /**
     * Handle 2FA verification request (AJAX).
     */
    public function twoFactorVerify(Request $request): JsonResponse
    {
        if (!$this->isTwoFactorPending()) {
            return $this->errorResponse('Session Expired', 'Your session has expired. Please login again.');
        }

        $code = trim(implode('', [
            (string) $request->input('code_one', ''),
            (string) $request->input('code_two', ''),
            (string) $request->input('code_three', ''),
            (string) $request->input('code_four', ''),
            (string) $request->input('code_five', ''),
            (string) $request->input('code_six', ''),
        ]));

        if (strlen($code) !== 6) {
            return $this->errorResponse('Incomplete Code', 'Please enter the 6-digit verification code.');
        }

        $adminId = session('piprapay.admin_id');
        $admin = PpAdmin::where('a_id', $adminId)->first();

        if (!$admin || (string) $admin->status !== 'active') {
            return $this->errorResponse('Verification Failed', 'Admin account not found or suspended.');
        }

        $this->ensureGoogleAuthenticatorLoaded();
        $authenticator = new \PHPGangsta_GoogleAuthenticator();

        if (!$authenticator->verifyCode((string) $admin->{'2fa_secret'}, $code, 2)) {
            return $this->errorResponse('Verification Failed', 'The code you entered is incorrect.');
        }

        $permission = PpPermission::where('a_id', (string) $admin->a_id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        // Success - Login
        session()->forget('piprapay.2fa_pending');
        return $this->performLogin($admin, $permission, $request)
            ->withoutCookie('pp_2fa');
    }

    /**
     * Display forgot password page.
     */
    public function forgot(Request $request): View|RedirectResponse
    {
        if ($this->isAuthenticated()) {
            return redirect()->route('admin.dashboard.index');
        }

        return view('admin.auth.forgot');
    }

    /**
     * Handle forgot password request (AJAX).
     */
    public function forgotRequest(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $admin = PpAdmin::where('email', $request->email)
            ->where('status', 'active')
            ->first();

        if (!$admin) {
            // Security: don't reveal if email exists
            return response()->json([
                'status' => 'true',
                'title' => 'Email Sent',
                'message' => 'If an account exists for this email, you will receive instructions shortly.',
                'csrf_token' => csrf_token()
            ]);
        }

        if ((int) $admin->reset_limit <= 0) {
            return $this->errorResponse('Reset Limited', 'You have reached the maximum number of reset attempts.');
        }

        $newPassword = Str::random(10);
        $admin->update([
            'temp_password' => Hash::make($newPassword),
            'reset_limit' => (int) $admin->reset_limit - 1,
        ]);

        $this->sendPasswordEmail($admin, $newPassword);

        return response()->json([
            'status' => 'true',
            'title' => 'Success',
            'message' => 'A temporary password has been sent to your email address.',
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $adminCookie = $request->cookie('pp_admin');
        if ($adminCookie) {
            PpBrowserLog::where('cookie', $adminCookie)->update(['status' => 'inactive']);
        }

        Auth::guard('pp_admin')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('native.auth.login')
            ->withoutCookie('pp_admin')
            ->withoutCookie('pp_brand')
            ->withoutCookie('pp_2fa');
    }

    /**
     * Internal: Perform the actual login and set cookies.
     */
    private function performLogin(PpAdmin $admin, PpPermission $permission, Request $request): JsonResponse
    {
        Auth::guard('pp_admin')->login($admin);

        $token = session('piprapay.browser_cookie') ?: $this->createBrowserSession($admin, $request);

        session([
            'piprapay.authenticated' => true,
            'piprapay.admin_id' => (string) $admin->a_id,
            'piprapay.browser_cookie' => $token
        ]);

        return response()->json([
            'status' => 'true',
            'title' => 'Login Successful',
            'message' => 'Redirecting to dashboard...',
            'target' => route('admin.dashboard.index'),
            'csrf_token' => csrf_token(),
        ])->cookie('pp_admin', $token, 60 * 24 * 30, '/', null, $request->isSecure(), true, false, 'Lax')
            ->cookie('pp_brand', (string) $permission->brand_id, 60 * 24 * 30, '/', null, $request->isSecure(), true, false, 'Lax');
    }

    /**
     * Internal: Create a entry in pp_browser_log and return the token.
     */
    private function createBrowserSession(PpAdmin $admin, Request $request): string
    {
        $token = bin2hex(random_bytes(16));

        PpBrowserLog::create([
            'a_id' => (string) $admin->a_id,
            'cookie' => $token,
            'browser' => $this->getBrowserName($request->userAgent()),
            'device' => $this->getDeviceName($request->userAgent()),
            'ip' => $request->ip() ?: '127.0.0.1',
            'status' => 'active',
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    private function isAuthenticated(): bool
    {
        return Auth::guard('pp_admin')->check();
    }

    private function isTwoFactorPending(): bool
    {
        return session('piprapay.2fa_pending') === true;
    }

    private function errorResponse(string $title, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'false',
            'title' => $title,
            'message' => $message,
            'csrf_token' => csrf_token(),
        ]);
    }

    private function sendPasswordEmail(PpAdmin $admin, string $password): void
    {
        try {
            Mail::raw("Your temporary password is: $password\n\nPlease change it after logging in.", function ($message) use ($admin) {
                $message->to((string) $admin->email)->subject('PipraPay Password Reset');
            });
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function ensureGoogleAuthenticatorLoaded(): void
    {
        if (!class_exists('PHPGangsta_GoogleAuthenticator')) {
            require_once public_path('pp-media/sdk/GoogleAuthenticator.php');
        }
    }

    private function getBrowserName(string $ua): string
    {
        $ua = strtolower($ua);
        if (\Illuminate\Support\Str::contains($ua, 'edg')) return 'Edge';
        if (\Illuminate\Support\Str::contains($ua, 'chrome')) return 'Chrome';
        if (\Illuminate\Support\Str::contains($ua, 'firefox')) return 'Firefox';
        if (\Illuminate\Support\Str::contains($ua, 'safari')) return 'Safari';
        return 'Other';
    }

    private function getDeviceName(string $ua): string
    {
        $ua = strtolower($ua);
        if (\Illuminate\Support\Str::contains($ua, ['mobile', 'android', 'iphone'])) return 'Mobile';
        return 'Desktop';
    }
}
