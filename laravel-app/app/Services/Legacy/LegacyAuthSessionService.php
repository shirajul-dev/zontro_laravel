<?php

namespace App\Services\Legacy;

use App\Models\PpAdmin;
use App\Models\PpBrowserLog;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;

class LegacyAuthSessionService
{
    public function sync(Request $request, StatefulGuard $guard): void
    {
        $adminCookie = (string) $request->cookie('pp_admin', '');
        $twoFaCookie = (string) $request->cookie('pp_2fa', '');

        // 1. If already authenticated in Laravel session, check if it still matches the cookie
        if ($guard->check()) {
            $sessionCookie = (string) session('piprapay.browser_cookie', '');
            
            // If we have a cookie and it matches what's in our session, we are good.
            if ($adminCookie !== '' && $adminCookie === $sessionCookie) {
                return;
            }

            // If cookies were cleared or changed, log out of Laravel to keep them in sync
            if ($adminCookie === '') {
                $guard->logout();
                session()->forget(['piprapay.authenticated', 'piprapay.admin_id', 'piprapay.browser_cookie']);
            }
        }

        // 2. If we have an admin cookie but no Laravel session, try to auto-login (Sync)
        if ($adminCookie !== '') {
            // Check if we already tried to sync this specific cookie in this session and failed
            if (session('piprapay.failed_sync_cookie') === $adminCookie) {
                return;
            }

            $browserLog = PpBrowserLog::where('cookie', $adminCookie)
                ->where('status', 'active')
                ->first();

            if ($browserLog !== null) {
                $admin = PpAdmin::where('a_id', (string) $browserLog->a_id)
                    ->where('status', 'active')
                    ->first();

                if ($admin !== null) {
                    $guard->login($admin);
                    
                    session([
                        'piprapay.authenticated' => true,
                        'piprapay.twofa_pending' => false,
                        'piprapay.admin_id' => (string) $admin->a_id,
                        'piprapay.browser_cookie' => $adminCookie,
                    ]);
                    
                    return;
                }
            }

            // Invalid cookie - cleanup
            $guard->logout();
            session(['piprapay.failed_sync_cookie' => $adminCookie]);
            session()->forget(['piprapay.authenticated', 'piprapay.twofa_pending', 'piprapay.admin_id', 'piprapay.browser_cookie']);
            return;
        }

        // 3. Handle 2FA Pending state
        if ($twoFaCookie !== '') {
            if (session('piprapay.2fa_pending') && session('piprapay.browser_cookie') === $twoFaCookie) {
                return;
            }

            $browserLog = PpBrowserLog::where('cookie', $twoFaCookie)
                ->where('status', 'active')
                ->first();

            if ($browserLog !== null) {
                $admin = PpAdmin::where('a_id', (string) $browserLog->a_id)
                    ->where('2fa_status', 'enable')
                    ->first();

                if ($admin) {
                    session([
                        'piprapay.authenticated' => false,
                        'piprapay.twofa_pending' => true,
                        'piprapay.admin_id' => (string) $admin->a_id,
                        'piprapay.browser_cookie' => $twoFaCookie,
                    ]);
                    return;
                }
            }
        }

        // 4. Default: No valid state
        if ($guard->check()) {
            $guard->logout();
        }
        session()->forget(['piprapay.authenticated', 'piprapay.twofa_pending', 'piprapay.admin_id', 'piprapay.browser_cookie']);
    }
}
