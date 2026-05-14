<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasLegacyEnvironment;
use App\Models\PpAdmin;
use App\Services\Admin\AddonAdminActionService;
use App\Services\Admin\ApiAdminActionService;
use App\Services\Admin\BalanceVerificationAdminActionService;
use App\Services\Admin\BrandAdminActionService;
use App\Services\Admin\CustomerAdminActionService;
use App\Services\Admin\CurrencyAdminActionService;
use App\Services\Admin\DashboardStatisticsService;
use App\Services\Admin\DeviceAdminActionService;
use App\Services\Admin\DomainAdminActionService;
use App\Services\Admin\FaqAdminActionService;
use App\Services\Admin\GatewayAdminActionService;
use App\Services\Admin\InvoiceAdminActionService;
use App\Services\Admin\MerchantAdminActionService;
use App\Services\Admin\OptionalAdminActionService;
use App\Services\Admin\PaymentLinkAdminActionService;
use App\Services\Admin\ReportsAdminActionService;
use App\Services\Admin\SmsDataAdminActionService;
use App\Services\Admin\StaffAdminActionService;
use App\Services\Admin\SystemSettingsAdminActionService;
use App\Services\Admin\TransactionAdminActionService;
use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Migration\AdminLegacyFallbackTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NativeAdminActionController extends Controller
{
    use HasLegacyEnvironment;

    public function __construct(
        private readonly LegacyRuntimeService $legacyRuntimeService,
        private readonly AdminLegacyFallbackTracker $fallbackTracker,
        private readonly DashboardStatisticsService $dashboardStatisticsService,
        private readonly DeviceAdminActionService $deviceAdminActionService,
        private readonly SmsDataAdminActionService $smsDataAdminActionService,
        private readonly BalanceVerificationAdminActionService $balanceVerificationAdminActionService,
        private readonly FaqAdminActionService $faqAdminActionService,
        private readonly DomainAdminActionService $domainAdminActionService,
        private readonly CustomerAdminActionService $customerAdminActionService,
        private readonly TransactionAdminActionService $transactionAdminActionService,
        private readonly BrandAdminActionService $brandAdminActionService,
        private readonly ApiAdminActionService $apiAdminActionService,
        private readonly CurrencyAdminActionService $currencyAdminActionService,
        private readonly GatewayAdminActionService $gatewayAdminActionService,
        private readonly ReportsAdminActionService $reportsAdminActionService,
        private readonly InvoiceAdminActionService $invoiceAdminActionService,
        private readonly PaymentLinkAdminActionService $paymentLinkAdminActionService,
        private readonly StaffAdminActionService $staffAdminActionService,
        private readonly OptionalAdminActionService $optionalAdminActionService,
        private readonly SystemSettingsAdminActionService $systemSettingsAdminActionService,
        private readonly MerchantAdminActionService $merchantAdminActionService,
    )
    {
    }

    public function handle(Request $request, ?string $page_name = null)
    {
        $action = (string) $request->input('action', '');

        if (!config('piprapay.migration.native_admin_actions_enabled', true)) {
            $this->fallbackTracker->record('native_toggle_disabled', $action, $page_name, $request->method(), $request->path());
            $this->setupLegacyGlobals($request);
            return $this->legacyRuntimeService->dispatch($request, $page_name);
        }

        if ($action === 'set-default-brand') {
            return $this->handleSetDefaultBrand($request);
        }

        if ($action === 'my-account-profile-information') {
            return $this->handleProfileInformation($request);
        }

        if ($action === 'my-account-account-browser-sessions') {
            return $this->handleBrowserSessions($request);
        }

        if ($action === 'my-account-account-two-factor-authentication') {
            return $this->handleTwoFactorAuthentication($request);
        }

        if ($action === 'dashboard-transaction-statistics') {
            return $this->handleDashboardTransactionStatistics($request);
        }

        if ($action === 'dashboard-gateway-statistics') {
            return $this->handleDashboardGatewayStatistics($request);
        }

        if ($action === 'device-list') {
            return $this->handleDeviceList($request);
        }

        if ($action === 'device-connect-info') {
            return $this->handleDeviceConnectInfo($request);
        }

        if ($action === 'device-delete') {
            return $this->handleDeviceDelete($request);
        }

        if ($action === 'device-bulk-action') {
            return $this->handleDeviceBulkAction($request);
        }

        if ($action === 'device-create-demo') {
            return $this->handleDeviceCreateDemo($request);
        }

        if ($action === 'sms-data-list') {
            return $this->handleSmsDataList($request);
        }

        if ($action === 'sms-data-create') {
            return $this->handleSmsDataCreate($request);
        }

        if ($action === 'sms-data-info-byID') {
            return $this->handleSmsDataInfoById($request);
        }

        if ($action === 'sms-data-edit') {
            return $this->handleSmsDataEdit($request);
        }

        if ($action === 'sms-data-delete') {
            return $this->handleSmsDataDelete($request);
        }

        if ($action === 'sms-data-bulk-action') {
            return $this->handleSmsDataBulkAction($request);
        }

        if ($action === 'fix-schema-temporary-mhs') {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . env('DB_PREFIX', 'pp_') . "invoice` MODIFY `status` VARCHAR(20) NOT NULL");
                return response()->json(['status' => 'true', 'message' => 'Schema fixed.']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'false', 'message' => $e->getMessage()]);
            }
        }

        \Illuminate\Support\Facades\Log::debug('Admin Action incoming', [
            'action' => $action,
            'page' => $page_name,
            'method' => $request->method(),
            'csrf_token_sent' => $request->input('csrf_token') ?? $request->input('csrf_token_default')
        ]);

        if ($action === 'balance-verification-list') {
            return $this->handleBalanceVerificationList($request);
        }

        if ($action === 'balance-verification-bulk-action') {
            return $this->handleBalanceVerificationBulkAction($request);
        }

        if ($action === 'balance-verification-delete') {
            return $this->handleBalanceVerificationDelete($request);
        }

        if ($action === 'balance-verification-create') {
            return $this->handleBalanceVerificationCreate($request);
        }

        if ($action === 'balance-verification-iupdate') {
            return $this->handleBalanceVerificationIUpdate($request);
        }

        if ($action === 'balance-verification-info-byID') {
            return $this->handleBalanceVerificationInfoById($request);
        }

        if ($action === 'balance-verification-update') {
            return $this->handleBalanceVerificationUpdate($request);
        }

        if ($action === 'faq-list') {
            return $this->handleFaqList($request);
        }

        if ($action === 'faq-create') {
            return $this->handleFaqCreate($request);
        }

        if ($action === 'faq-info-byID') {
            return $this->handleFaqInfoById($request);
        }

        if ($action === 'faq-edit') {
            return $this->handleFaqEdit($request);
        }

        if ($action === 'faq-bulk-action') {
            return $this->handleFaqBulkAction($request);
        }

        if ($action === 'faq-delete') {
            return $this->handleFaqDelete($request);
        }

        if ($action === 'all-domain-list') {
            return $this->handleDomainList($request);
        }

        if ($action === 'domains-info-byID') {
            return $this->handleDomainInfoById($request);
        }

        if ($action === 'create-domains') {
            return $this->handleDomainCreate($request);
        }

        if ($action === 'domains-edit') {
            return $this->handleDomainEdit($request);
        }

        if ($action === 'domains-delete') {
            return $this->handleDomainDelete($request);
        }

        if ($action === 'domain-bulk-action') {
            return $this->handleDomainBulkAction($request);
        }

        if ($action === 'customer-list') {
            return $this->handleCustomerList($request);
        }

        if ($action === 'customers-create') {
            return $this->handleCustomerCreate($request);
        }

        if ($action === 'customers-bulk-action') {
            return $this->handleCustomerBulkAction($request);
        }

        if ($action === 'customers-delete') {
            return $this->handleCustomerDelete($request);
        }

        if ($action === 'customers-info-byID') {
            return $this->handleCustomerInfoById($request);
        }

        if ($action === 'customers-edit') {
            return $this->handleCustomerEdit($request);
        }

        if ($action === 'transaction-list') {
            return $this->handleTransactionList($request);
        }

        if ($action === 'transaction-bulk-action') {
            return $this->handleTransactionBulkAction($request);
        }

        if ($action === 'transaction-delete') {
            return $this->handleTransactionDelete($request);
        }

        if ($action === 'transaction-ipn') {
            return $this->handleTransactionIpn($request);
        }

        if ($action === 'all-brand-list') {
            return $this->handleBrandList($request);
        }

        if ($action === 'brand-bulk-action') {
            return $this->handleBrandBulkAction($request);
        }

        if ($action === 'create-new-brand') {
            return $this->handleCreateNewBrand($request);
        }

        if ($action === 'edit-brand') {
            return $this->handleEditBrand($request);
        }

        if ($action === 'brand-delete') {
            return $this->handleBrandDelete($request);
        }

        if ($action === 'api-create') {
            return $this->handleApiCreate($request);
        }

        if ($action === 'api-list') {
            return $this->handleApiList($request);
        }

        if ($action === 'api-info-byID') {
            return $this->handleApiInfoById($request);
        }

        if ($action === 'api-bulk-action') {
            return $this->handleApiBulkAction($request);
        }

        if ($action === 'api-delete') {
            return $this->handleApiDelete($request);
        }

        if ($action === 'api-edit') {
            return $this->handleApiEdit($request);
        }

        if ($action === 'currency-list') {
            return $this->handleCurrencyList($request);
        }

        if ($action === 'currency-edit') {
            return $this->handleCurrencyEdit($request);
        }

        if ($action === 'currency-info-byID') {
            return $this->handleCurrencyInfoById($request);
        }

        if ($action === 'currency-bulkImport') {
            return $this->handleCurrencyBulkImport($request);
        }

        if ($action === 'currency-rateSync') {
            return $this->handleCurrencyRateSync($request);
        }

        if ($action === 'currency-bulk-rateSync') {
            return $this->handleCurrencyBulkRateSync($request);
        }

        if ($action === 'gateway-create') {
            return $this->handleGatewayCreate($request);
        }

        if ($action === 'gateways-list') {
            return $this->handleGatewayList($request);
        }

        if ($action === 'gateways-delete') {
            return $this->handleGatewayDelete($request);
        }

        if ($action === 'gateways-bulk-action') {
            return $this->handleGatewayBulkAction($request);
        }

        if ($action === 'reports') {
            return $this->handleReports($request);
        }

        if ($action === 'invoice-list') {
            return $this->handleInvoiceList($request);
        }

        if ($action === 'invoice-bulk-action') {
            return $this->handleInvoiceBulkAction($request);
        }

        if ($action === 'theme-setting-update') {
            return $this->handleThemeSettingUpdate($request);
        }

        if ($action === 'gateway-setting-create') {
            return $this->handleGatewaySettingCreate($request);
        }

        if ($action === 'gateway-setting-update') {
            return $this->handleGatewaySettingUpdate($request);
        }

        if ($action === 'addon-setting-update') {
            return $this->handleAddonSettingUpdate($request);
        }

        if ($action === 'addon-configuration-update') {
            return $this->handleAddonConfigurationUpdate($request);
        }

        if ($action === 'system-settings-import') {
            return $this->handleSystemSettingsImport($request);
        }

        if ($action === 'general-setting') {
            return $this->handleGeneralSetting($request);
        }

        if ($action === 'invoice-create') {
            return $this->handleInvoiceCreate($request);
        }

        if ($action === 'invoice-edit') {
            return $this->handleInvoiceEdit($request);
        }

        if ($action === 'invoice-manageStatus') {
            return $this->handleInvoiceManageStatus($request);
        }

        if ($action === 'invoice-delete') {
            return $this->handleInvoiceDelete($request);
        }

        if ($action === 'paymentLink-list') {
            return $this->handlePaymentLinkList($request);
        }

        if ($action === 'paymentLink-create') {
            return $this->handlePaymentLinkCreate($request);
        }

        if ($action === 'paymentLink-edit') {
            return $this->handlePaymentLinkEdit($request);
        }

        if ($action === 'paymentLink-bulk-action') {
            return $this->handlePaymentLinkBulkAction($request);
        }

        if ($action === 'paymentLink-delete') {
            return $this->handlePaymentLinkDelete($request);
        }

        if ($action === 'paymentLink-defaultLinkCurrency') {
            return $this->handlePaymentLinkDefaultCurrency($request);
        }

        if ($action === 'staff-management-list') {
            return $this->handleStaffManagementList($request);
        }

        if ($action === 'staff-bulk-action') {
            return $this->handleStaffBulkAction($request);
        }

        if ($action === 'staff-delete') {
            return $this->handleStaffDelete($request);
        }

        if ($action === 'staff-permissions') {
            return $this->handleStaffPermissions($request);
        }

        if ($action === 'staff-permission-bulk-action') {
            return $this->handleStaffPermissionBulkAction($request);
        }

        if ($action === 'staff-permission-delete') {
            return $this->handleStaffPermissionDelete($request);
        }

        if ($action === 'staff-create') {
            return $this->handleStaffCreate($request);
        }

        if ($action === 'staff-update') {
            return $this->handleStaffUpdate($request);
        }

        if ($action === 'staff-brand-add') {
            return $this->handleStaffBrandAdd($request);
        }

        if ($action === 'staff-update-permission') {
            return $this->handleStaffUpdatePermission($request);
        }

        if ($action === 'activities-list') {
            return $this->handleActivitiesList($request);
        }

        if ($action === 'addons-create') {
            return $this->handleAddonsCreate($request);
        }

        if ($action === 'addons-list') {
            return $this->handleAddonsList($request);
        }

        if ($action === 'addons-delete') {
            return $this->handleAddonsDelete($request);
        }

        if ($action === 'addons-bulk-action') {
            return $this->handleAddonsBulkAction($request);
        }

        if ($action === 'themes-new-active') {
            return $this->handleThemesNewActive($request);
        }

        if ($action === 'merchant-create') {
            return $this->handleMerchantCreate($request);
        }

        if ($action === 'merchant-bulk-action') {
            return $this->handleMerchantBulkAction($request);
        }

        if ($action === 'geneal-application-settings') {
            return $this->handleGeneralApplicationSettings($request);
        }

        if ($action === 'cron-job-command-generate') {
            return $this->handleCronJobCommandGenerate($request);
        }

        if ($action === 'system-settings-update-setting') {
            return $this->handleSystemSettingsUpdateSetting($request);
        }

        if ($action === 'system-settings-update-check') {
            return $this->handleSystemSettingsUpdateCheck($request);
        }

        if ($action === 'system-settings-update-download') {
            return $this->handleSystemSettingsUpdateDownload($request);
        }

        if ($action === 'system-settings-update-install') {
            return $this->handleSystemSettingsUpdateInstall($request);
        }

        $this->fallbackTracker->record('unknown_action', $action, $page_name, $request->method(), $request->path());

        return response()->json([
            'status' => 'false',
            'title' => 'Invalid Action',
            'message' => 'The requested action is not available.',
        ], 200);
    }

    private function handleSetDefaultBrand(Request $request): JsonResponse
    {

        // Laravel natively manages CSRF via VerifyCsrfToken middleware

        $newCsrfToken = (string) csrf_token();
        $brandId = trim((string) $request->input('brand_id', ''));

        if ($brandId === '') {
            return $this->failure('Incomplete Information', 'Please fill in all required fields before proceeding.', $newCsrfToken);
        }

        $admin = Auth::guard('pp_admin')->user();

        if (!$admin instanceof PpAdmin) {
            $adminCookie = (string) $request->cookie('pp_admin', '');

            if ($adminCookie !== '') {
                $browserLog = DB::table('pp_browser_log')
                    ->where('cookie', $adminCookie)
                    ->where('status', 'active')
                    ->first();

                if ($browserLog) {
                    $admin = PpAdmin::query()
                        ->where('a_id', (string) $browserLog->a_id)
                        ->where('status', 'active')
                        ->first();
                }
            }
        }

        if (!$admin instanceof PpAdmin) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $hasAccess = DB::table('pp_permission')
            ->where('a_id', (string) $admin->a_id)
            ->where('status', 'active')
            ->where('brand_id', $brandId)
            ->exists();

        if (!$hasAccess) {
            return $this->failure('Brand Access Failed', 'You don’t have permission to manage brands. Contact your admin.', $newCsrfToken);
        }

        return response()->json([
            'status' => 'true',
            'csrf_token' => $newCsrfToken,
        ])->cookie(
            'pp_brand',
            $brandId,
            60 * 24 * 30,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'Lax'
        );
    }

    private function handleProfileInformation(Request $request): JsonResponse
    {
        // Laravel natively manages CSRF

        $newCsrfToken = (string) csrf_token();
        $admin = $this->resolveAdmin($request);

        if (!$admin instanceof PpAdmin) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!empty(config('piprapay.demo_mode', false))) {
            return $this->failure('Demo Restriction', 'This feature is disabled in the demo version.', $newCsrfToken);
        }

        $fullName = trim((string) $request->input('fullname', ''));
        $username = trim((string) $request->input('username', ''));
        $emailAddress = trim((string) $request->input('email-address', ''));
        $password = (string) $request->input('password', '');
        $verificationCode = trim((string) $request->input('my-two-step-verify-code', ''));

        if ($fullName === '' || $username === '' || $emailAddress === '') {
            return $this->failure('Incomplete Information', 'Please fill in all required fields before proceeding.', $newCsrfToken);
        }

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return $this->failure('Invalid Email', 'Please enter a valid email address.', $newCsrfToken);
        }

        if (!$this->validateProfileVerification($admin, $verificationCode)) {
            return $this->failure('Verification Failed', 'The code/password you entered is incorrect. Please try again.', $newCsrfToken);
        }

        if ($username !== (string) $admin->username) {
            $usernameExists = PpAdmin::query()
                ->where('username', $username)
                ->where('id', '!=', (int) $admin->id)
                ->exists();

            if ($usernameExists) {
                return $this->failure('Incomplete Information', 'Username already exits.', $newCsrfToken);
            }
        }

        if ($emailAddress !== (string) $admin->email) {
            $emailExists = PpAdmin::query()
                ->where('email', $emailAddress)
                ->where('id', '!=', (int) $admin->id)
                ->exists();

            if ($emailExists) {
                return $this->failure('Incomplete Information', 'Email Address already exits.', $newCsrfToken);
            }
        }

        if ($password === '') {
            $hashedPassword = (string) $admin->password;
            $hashedTempPassword = (string) $admin->temp_password;
        } else {
            $hashedPassword = Hash::make($password);
            $hashedTempPassword = Hash::make($this->generateStrongPassword(8));
        }

        $admin->update([
            'full_name' => $fullName,
            'username' => $username,
            'email' => $emailAddress,
            'password' => $hashedPassword,
            'temp_password' => $hashedTempPassword,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'status' => 'true',
            'title' => 'Profile Updated',
            'message' => 'Your profile information has been updated successfully.',
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleBrowserSessions(Request $request): JsonResponse
    {
        // Laravel natively manages CSRF

        $newCsrfToken = (string) csrf_token();
        $admin = $this->resolveAdmin($request);

        if (!$admin instanceof PpAdmin) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $verificationCode = trim((string) $request->input('my-two-step-verify-code', ''));

        if (!$this->validateProfileVerification($admin, $verificationCode)) {
            return $this->failure('Verification Failed', 'The code/password you entered is incorrect. Please try again.', $newCsrfToken);
        }

        $currentCookie = (string) $request->cookie('pp_admin', '');

        if ($currentCookie === '') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        DB::table('pp_browser_log')
            ->where('a_id', (string) $admin->a_id)
            ->where('cookie', '!=', $currentCookie)
            ->update([
                'status' => 'expired',
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'status' => 'true',
            'title' => 'Logged Out Successfully',
            'message' => 'You have been logged out of all other browser sessions.',
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleTwoFactorAuthentication(Request $request): JsonResponse
    {
        // Laravel natively manages CSRF

        $newCsrfToken = (string) csrf_token();
        $admin = $this->resolveAdmin($request);

        if (!$admin instanceof PpAdmin) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $authCode = trim((string) $request->input('auth-code', ''));

        if ($authCode === '') {
            return $this->failure('Incomplete Information', 'Please fill in all required fields before proceeding.', $newCsrfToken);
        }

        if (!$this->validateProfileVerification($admin, $authCode)) {
            return $this->failure('Verification Failed', 'The code you entered is incorrect. Please try again.', $newCsrfToken);
        }

        $faStatus = ((string) $admin->{'2fa_status'} === 'enable') ? 'disable' : 'enable';

        $admin->update([
            '2fa_status' => $faStatus,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        if ($faStatus === 'disable') {
            return response()->json([
                'status' => 'true',
                'title' => 'Two-Factor Authentication Disabled',
                'message' => 'Two-factor authentication has been successfully disabled for your account.',
                'csrf_token' => $newCsrfToken,
            ]);
        }

        return response()->json([
            'status' => 'true',
            'title' => 'Two-Factor Authentication Enabled',
            'message' => 'Two-factor authentication has been successfully enabled for your account.',
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleDashboardTransactionStatistics(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasDashboardPageAccess($legacy)) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $stats = $this->dashboardStatisticsService->transactionStatistics(
            $brandId,
            trim((string) $request->input('date', 'this_year')),
            trim((string) $request->input('start', '')),
            trim((string) $request->input('end', '')),
        );

        return response()->json([
            'status' => 'true',
            'labels' => $stats['labels'],
            'total' => $stats['total'],
            'complete' => $stats['complete'],
            'pending' => $stats['pending'],
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleDashboardGatewayStatistics(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasDashboardPageAccess($legacy)) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $stats = $this->dashboardStatisticsService->gatewayStatistics(
            $brandId,
            trim((string) $request->input('date', 'this_year')),
            trim((string) $request->input('start', '')),
            trim((string) $request->input('end', '')),
        );

        return response()->json([
            'status' => 'true',
            'labels' => $stats['labels'],
            'keys' => $stats['keys'],
            'gateway_labels' => $stats['gateway_labels'],
            'data' => $stats['data'],
            'colors' => $stats['colors'],
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleDeviceList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->deviceAdminActionService->list($request->all(), $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDeviceConnectInfo(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'connect')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $ownerId = trim((string) $request->cookie('pp_admin', ''));
        if ($ownerId === '') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->deviceAdminActionService->connectInfo($ownerId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDeviceDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $this->deviceAdminActionService->delete($itemId);

        return response()->json([
            'status' => 'true',
            'title' => 'Device Deleted',
            'message' => 'The selected device have been deleted successfully.',
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleDeviceCreateDemo(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!empty(config('piprapay.demo_mode', false))) {
            // Check demo mode is allowed in config
        } else {
             // In some cases we might want to check another way, but for now let's stick to config
        }

        $ownerId = trim((string) $request->cookie('pp_admin', ''));
        if ($ownerId === '') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $name = trim((string) $request->input('name', ''));
        $model = trim((string) $request->input('model', ''));
        $androidLevel = trim((string) $request->input('android_level', ''));

        if ($name === '' || $model === '') {
            return $this->failure('Incomplete Information', 'Please fill in all required fields before proceeding.', $newCsrfToken);
        }

        $result = $this->deviceAdminActionService->createDemo($ownerId, $name, $model, $androidLevel);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDeviceBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        if (!is_array($selectedIds) || $selectedIds === []) {
            return $this->failure('Request Failed', 'No devices selected.', $newCsrfToken);
        }

        if ($actionId === 'deleted' && $this->hasModulePermission($legacy, 'device', 'delete')) {
            $this->deviceAdminActionService->bulkDelete($selectedIds);
        }

        return response()->json([
            'status' => 'true',
            'title' => 'Devices ' . $actionId,
            'message' => 'The selected devices have been ' . $actionId . ' successfully.',
            'csrf_token' => $newCsrfToken,
        ]);
    }

    private function handleSmsDataList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'sms_data')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->smsDataAdminActionService->list($request->all(), $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSmsDataCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'sms_data')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'sms_data', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->smsDataAdminActionService->create($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSmsDataInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'sms_data')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'sms_data', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->smsDataAdminActionService->infoById($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSmsDataEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'sms_data')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'sms_data', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->smsDataAdminActionService->edit($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSmsDataDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'sms_data')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'sms_data', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->smsDataAdminActionService->delete($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSmsDataBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'sms_data')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        if ($actionId === 'deleted' && !$this->hasModulePermission($legacy, 'sms_data', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if ($actionId !== 'deleted' && !$this->hasModulePermission($legacy, 'sms_data', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->smsDataAdminActionService->bulkAction($actionId, $selectedIds);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->balanceVerificationAdminActionService->list($request->all(), $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->balanceVerificationAdminActionService->bulkAction($actionId, $selectedIds);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->balanceVerificationAdminActionService->delete($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->balanceVerificationAdminActionService->create($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationIUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $balance = trim((string) $request->input('balance', ''));
        $result = $this->balanceVerificationAdminActionService->incrementalUpdate($itemId, $balance);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->balanceVerificationAdminActionService->infoById($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBalanceVerificationUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'device')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'device', 'balance_verification_for')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->balanceVerificationAdminActionService->update($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleFaqList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'faq_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->faqAdminActionService->list($request->all(), $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleFaqCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'faq_settings', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->faqAdminActionService->create($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleFaqInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'faq_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->faqAdminActionService->infoById($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleFaqEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'faq_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->faqAdminActionService->edit($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleFaqBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'faq_settings', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->faqAdminActionService->bulkAction($actionId, $selectedIds, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleFaqDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'faq_settings', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->faqAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDomainList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'domains')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->domainAdminActionService->list($request->all(), $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDomainInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'domains')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'domains', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->domainAdminActionService->infoById($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDomainCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'domains')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'domains', 'whitelist')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->domainAdminActionService->create($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDomainEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'domains')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'domains', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->domainAdminActionService->edit($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDomainDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'domains')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'domains', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = (int) $request->input('ItemID', 0);
        $result = $this->domainAdminActionService->delete($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleDomainBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'domains')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->domainAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $this->hasModulePermission($legacy, 'domains', 'delete'),
            $this->hasModulePermission($legacy, 'domains', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCustomerList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'customers')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->customerAdminActionService->list($request->all(), $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCustomerCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'customers')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'customers', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->customerAdminActionService->create($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCustomerBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'customers')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->customerAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $brandId,
            $this->hasModulePermission($legacy, 'customers', 'delete'),
            $this->hasModulePermission($legacy, 'customers', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCustomerDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'customers')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'customers', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->customerAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCustomerInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'customers')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'customers', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->customerAdminActionService->infoById($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCustomerEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'customers')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'customers', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->customerAdminActionService->edit($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleTransactionList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'transaction')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->transactionAdminActionService->list($request->all(), $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleTransactionBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'transaction')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->transactionAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $brandId,
            $this->brandTimezone($legacy),
            $this->hasModulePermission($legacy, 'transaction', 'delete'),
            $this->hasModulePermission($legacy, 'transaction', 'approve'),
            $this->hasModulePermission($legacy, 'transaction', 'refund'),
            $this->hasModulePermission($legacy, 'transaction', 'cancel'),
            $this->hasModulePermission($legacy, 'transaction', 'send_ipn')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleTransactionDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'transaction')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'transaction', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->transactionAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleTransactionIpn(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'transaction')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'transaction', 'send_ipn')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->transactionAdminActionService->sendIpn($itemId, $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBrandList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brands')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentBrandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($currentBrandId === '' || $currentBrandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $userType = (string) ($legacy['global_user_response']['response'][0]['user_type'] ?? 'staff');
        $result = $this->brandAdminActionService->list($request->all(), $currentBrandId, $this->brandTimezone($legacy), $userType);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCreateNewBrand(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'brands')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'brands', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $adminRole = (string) ($legacy['admin']->role ?? '');
        $adminAId = (string) ($legacy['admin']->a_id ?? '');

        $result = $this->brandAdminActionService->createBrand($request->all(), $adminRole, $adminAId);

        // If it's a JsonResponse, we just inject the csrf_token
        if ($result instanceof JsonResponse) {
            $data = $result->getData(true);
            $data['csrf_token'] = $newCsrfToken;
            $result->setData($data);
            return $result;
        }

        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleEditBrand(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'brands')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'brands', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $userRole = strtolower($legacy['global_user_response']['response'][0]['role'] ?? 'staff');
        $isSuper = ($userRole === 'admin' || $userRole === 'root');

        $result = $this->brandAdminActionService->editBrand($request->all(), $globalBrandId, $isSuper);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBrandBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'brands')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentBrandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($currentBrandId === '' || $currentBrandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->brandAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $currentBrandId,
            $this->hasModulePermission($legacy, 'brands', 'delete')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleBrandDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'brands')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'brands', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentBrandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($currentBrandId === '' || $currentBrandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->brandAdminActionService->delete($itemId, $currentBrandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleApiCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->apiAdminActionService->create($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleApiList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->apiAdminActionService->list($request->all(), $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleApiInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->apiAdminActionService->infoById($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleApiBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->apiAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $brandId,
            $this->hasModulePermission($legacy, 'api_settings', 'delete'),
            $this->hasModulePermission($legacy, 'api_settings', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleApiDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->apiAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleApiEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'api_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->apiAdminActionService->edit($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCurrencyList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'currency_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $brandCurrencyCode = (string) ($legacy['global_response_brand']['response'][0]['currency_code'] ?? '');
        if ($brandId === '' || $brandId === '--' || $brandCurrencyCode === '' || $brandCurrencyCode === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->currencyAdminActionService->list(
            $request->all(),
            $brandId,
            $this->brandTimezone($legacy),
            $brandCurrencyCode
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCurrencyEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'currency_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->currencyAdminActionService->edit($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCurrencyInfoById(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'currency_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->currencyAdminActionService->infoById($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCurrencyBulkImport(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'currency_settings', 'import')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->currencyAdminActionService->bulkImport($brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCurrencyRateSync(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'currency_settings', 'sync_rate')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $brandCurrencyCode = (string) ($legacy['global_response_brand']['response'][0]['currency_code'] ?? '');
        if ($brandId === '' || $brandId === '--' || $brandCurrencyCode === '' || $brandCurrencyCode === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->currencyAdminActionService->rateSync($itemId, $brandId, $brandCurrencyCode);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCurrencyBulkRateSync(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'currency_settings', 'sync_rate')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $brandCurrencyCode = (string) ($legacy['global_response_brand']['response'][0]['currency_code'] ?? '');
        if ($brandId === '' || $brandId === '--' || $brandCurrencyCode === '' || $brandCurrencyCode === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->currencyAdminActionService->bulkRateSync($brandId, $brandCurrencyCode);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGatewayCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'gateways')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'gateways', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $gateway = trim((string) $request->input('gateway', ''));
        $result = $this->gatewayAdminActionService->create($gateway, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGatewayList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'gateways')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->gatewayAdminActionService->list($request->all(), $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGatewayDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'gateways')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'gateways', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->gatewayAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGatewayBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'gateways')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->gatewayAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $brandId,
            $this->hasModulePermission($legacy, 'gateways', 'delete'),
            $this->hasModulePermission($legacy, 'gateways', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleReports(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'reports')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $brandCurrencyCode = (string) ($legacy['global_response_brand']['response'][0]['currency_code'] ?? '');
        if ($brandId === '' || $brandId === '--' || $brandCurrencyCode === '' || $brandCurrencyCode === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->reportsAdminActionService->generate($request->all(), $brandId, $brandCurrencyCode);
        $result['csrf_token'] = $newCsrfToken;



        return response()->json($result);
    }

    private function handleInvoiceList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'invoice')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->invoiceAdminActionService->list($request->all(), $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleInvoiceCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'invoice')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'invoice', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $globalBrandTimezone = (string) ($legacy['brand']->timezone ?? '--');

        $result = $this->invoiceAdminActionService->createInvoice($request->all(), $globalBrandId, $globalBrandTimezone);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleInvoiceEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'invoice')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'invoice', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $globalBrandTimezone = (string) ($legacy['brand']->timezone ?? '--');

        $result = $this->invoiceAdminActionService->editInvoice($request->all(), $globalBrandId, $globalBrandTimezone);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleInvoiceManageStatus(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'invoice')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'invoice', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $globalBrandTimezone = (string) ($legacy['brand']->timezone ?? '--');

        $result = $this->invoiceAdminActionService->manageInvoiceStatus($request->all(), $globalBrandId, $globalBrandTimezone);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleInvoiceBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'invoice')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->invoiceAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $brandId,
            $this->hasModulePermission($legacy, 'invoice', 'delete')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleInvoiceDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'invoice')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'invoice', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->invoiceAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleThemeSettingUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'theme_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $themeSlug = (string) ($legacy['brand']->theme ?? '');
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        $result = $this->systemSettingsAdminActionService->updateThemeSettings(
            $request->all(),
            $request->allFiles(),
            $globalBrandId,
            $themeSlug,
            $siteUrl
        );

        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGatewaySettingCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'gateways')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'gateways', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        $result = $this->gatewayAdminActionService->createGatewaySetting(
            $request->all(),
            $request->allFiles(),
            $globalBrandId,
            $siteUrl
        );

        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGatewaySettingUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'gateways')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'gateways', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        $result = $this->gatewayAdminActionService->updateGatewaySetting(
            $request->all(),
            $request->allFiles(),
            $globalBrandId,
            $siteUrl
        );

        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleAddonSettingUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'addons')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'addons', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->addonAdminActionService->updateAddonSetting($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleAddonConfigurationUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'addons')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'addons', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        $result = $this->addonAdminActionService->updateAddonConfiguration(
            $request->all(),
            $request->allFiles(),
            $siteUrl
        );

        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }


    private function handleSystemSettingsImport(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_import')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->importSystemSettings($request->allFiles());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGeneralSetting(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'brand_settings', 'view')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'brand_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        $result = $this->brandAdminActionService->updateGeneralSetting(
            $request->all(),
            $request->allFiles(),
            $globalBrandId,
            $siteUrl
        );

        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handlePaymentLinkList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'payment_link')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->paymentLinkAdminActionService->list($request->all(), $brandId, $this->brandTimezone($legacy));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handlePaymentLinkCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'payment_link')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'payment_link', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');

        $result = $this->paymentLinkAdminActionService->createPaymentLink($request->all(), $globalBrandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handlePaymentLinkEdit(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'payment_link')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'payment_link', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $globalBrandId = (string) ($legacy['brand']->brand_id ?? '');

        $result = $this->paymentLinkAdminActionService->editPaymentLink($request->all(), $globalBrandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handlePaymentLinkBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'payment_link')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->paymentLinkAdminActionService->bulkAction(
            $actionId,
            $selectedIds,
            $brandId,
            $this->hasModulePermission($legacy, 'payment_link', 'delete'),
            $this->hasModulePermission($legacy, 'payment_link', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handlePaymentLinkDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'payment_link')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'payment_link', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->paymentLinkAdminActionService->delete($itemId, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handlePaymentLinkDefaultCurrency(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'payment_link')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'payment_link', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $defaultCurrency = trim((string) $request->input('DefaultCurrency', ''));
        $result = $this->paymentLinkAdminActionService->updateDefaultCurrency($defaultCurrency, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffManagementList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');
        if ($currentAdminAId === '' || $currentAdminAId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $currentBrandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $userType = (string) ($legacy['global_user_response']['response'][0]['user_type'] ?? 'staff');
        $result = $this->staffAdminActionService->staffList($request->all(), $currentAdminAId, $this->brandTimezone($legacy), $currentBrandId, $userType);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');
        if ($currentAdminAId === '' || $currentAdminAId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->staffAdminActionService->staffBulkAction(
            $actionId,
            $selectedIds,
            $currentAdminAId,
            $this->hasModulePermission($legacy, 'staff', 'delete'),
            $this->hasModulePermission($legacy, 'staff', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');
        if ($currentAdminAId === '' || $currentAdminAId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->staffAdminActionService->staffDelete($itemId, $currentAdminAId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffPermissions(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'view_permission_list')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');
        $currentAdminId = (int) ($legacy['global_user_response']['response'][0]['id'] ?? 0);
        if ($currentAdminAId === '' || $currentAdminAId === '--' || $currentAdminId <= 0) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $targetAId = trim((string) $request->input('a_id', ''));
        $result = $this->staffAdminActionService->permissionList(
            $request->all(),
            $targetAId,
            $currentAdminAId,
            $currentAdminId,
            $this->brandTimezone($legacy)
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffPermissionBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');
        $currentAdminId = (int) ($legacy['global_user_response']['response'][0]['id'] ?? 0);
        if ($currentAdminAId === '' || $currentAdminAId === '--' || $currentAdminId <= 0) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->staffAdminActionService->permissionBulkAction(
            $actionId,
            $selectedIds,
            $currentAdminId,
            $currentAdminAId,
            $this->hasModulePermission($legacy, 'staff', 'delete_permission_of'),
            $this->hasModulePermission($legacy, 'staff', 'edit_permission')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffPermissionDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'delete_permission')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $currentAdminId = (int) ($legacy['global_user_response']['response'][0]['id'] ?? 0);
        if ($currentAdminId <= 0) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->staffAdminActionService->permissionDelete($itemId, $currentAdminId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->staffAdminActionService->staffCreate($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffUpdate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $ownerAId = (string) ($legacy['admin']->a_id ?? '');

        $result = $this->staffAdminActionService->staffUpdate($request->all(), $ownerAId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffBrandAdd(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'add_permission')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $ownerAId = (string) ($legacy['admin']->a_id ?? '');

        $result = $this->staffAdminActionService->staffBrandAdd($request->all(), $ownerAId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleStaffUpdatePermission(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'staff_management')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'staff', 'edit_permission')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $ownerAId = (string) ($legacy['admin']->a_id ?? '');

        $result = $this->staffAdminActionService->staffUpdatePermission($request->all(), $ownerAId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleActivitiesList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');
        if ($currentAdminAId === '' || $currentAdminAId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->optionalAdminActionService->activitiesList(
            $request->all(),
            $currentAdminAId,
            (string) $request->cookie('pp_admin', ''),
            $this->brandTimezone($legacy)
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleAddonsCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'addons')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'addons', 'create')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $addon = trim((string) $request->input('addon', ''));
        $result = $this->optionalAdminActionService->createAddon($addon);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleAddonsList(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'addons')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->optionalAdminActionService->addonsList($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleAddonsDelete(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'addons')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'addons', 'delete')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $itemId = trim((string) $request->input('ItemID', ''));
        $result = $this->optionalAdminActionService->deleteAddon($itemId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleAddonsBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'addons')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $actionId = trim((string) $request->input('actionID', ''));
        $selectedIds = json_decode((string) $request->input('selected_ids', '[]'), true);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [];

        $result = $this->optionalAdminActionService->addonsBulkAction(
            $actionId,
            $selectedIds,
            $this->hasModulePermission($legacy, 'addons', 'delete'),
            $this->hasModulePermission($legacy, 'addons', 'edit')
        );
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleThemesNewActive(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'brand_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'theme_settings', 'edit')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $slug = trim((string) $request->input('slug', ''));
        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        if ($brandId === '' || $brandId === '--') {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        $result = $this->optionalAdminActionService->activateTheme($slug, $brandId);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleGeneralApplicationSettings(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_general')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->updateGeneralSettings($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleCronJobCommandGenerate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_cron')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->generateCronCommand();
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSystemSettingsUpdateSetting(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!empty(config('piprapay.demo_mode', false))) {
            return $this->failure('Demo Restriction', 'This feature is disabled in the demo version.', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_update')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->updateUpdateSetting($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSystemSettingsUpdateCheck(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!empty(config('piprapay.demo_mode', false))) {
            return $this->failure('Demo Restriction', 'This feature is disabled in the demo version.', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_update')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->checkForUpdate((array) ($legacy['piprapay_current_version'] ?? []));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSystemSettingsUpdateDownload(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!empty(config('piprapay.demo_mode', false))) {
            return $this->failure('Demo Restriction', 'This feature is disabled in the demo version.', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_update')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->downloadUpdate((array) ($legacy['piprapay_current_version'] ?? []));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleSystemSettingsUpdateInstall(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!empty(config('piprapay.demo_mode', false))) {
            return $this->failure('Demo Restriction', 'This feature is disabled in the demo version.', $newCsrfToken);
        }

        if (!$this->hasPageAccess($legacy, 'system_settings')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        if (!$this->hasModulePermission($legacy, 'system_settings', 'manage_update')) {
            return $this->failure('Access denied', 'You need permission to perform this action. Please contact the admin.', $newCsrfToken);
        }

        $result = $this->systemSettingsAdminActionService->installUpdate((array) ($legacy['piprapay_current_version'] ?? []));
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function isLegacyUserAuthorized(array $legacy): bool
    {
        return (bool) ($legacy['global_user_response']['status'] ?? false);
    }

    private function hasDashboardPageAccess(array $legacy): bool
    {
        $permissions = $this->permissionArray($legacy);
        if ($permissions === null) {
            return false;
        }

        $role = (string) ($legacy['global_user_response']['response'][0]['role'] ?? 'staff');

        if (function_exists('canAccessPage')) {
            return canAccessPage($permissions, 'dashboard', $role);
        }

        return true;
    }

    private function hasPageAccess(array $legacy, string $page): bool
    {
        $permissions = $this->permissionArray($legacy);
        if ($permissions === null) {
            return false;
        }

        $role = (string) ($legacy['global_user_response']['response'][0]['role'] ?? 'staff');
        if (function_exists('canAccessPage')) {
            return canAccessPage($permissions, $page, $role);
        }

        return true;
    }

    private function hasModulePermission(array $legacy, string $module, string $action): bool
    {
        $permissions = $this->permissionArray($legacy);
        if ($permissions === null) {
            return false;
        }

        $role = (string) ($legacy['global_user_response']['response'][0]['role'] ?? 'staff');
        if (function_exists('hasPermission')) {
            return hasPermission($permissions, $module, $action, $role);
        }

        return true;
    }

    private function permissionArray(array $legacy): ?array
    {
        $permissionJson = (string) ($legacy['global_response_permission']['response'][0]['permission'] ?? '');
        if ($permissionJson === '') {
            return null;
        }

        $permissions = json_decode($permissionJson, true);

        return is_array($permissions) ? $permissions : null;
    }

    private function brandTimezone(array $legacy): string
    {
        return (string) ($legacy['global_response_brand']['response'][0]['timezone'] ?? 'Asia/Dhaka');
    }

    private function validateProfileVerification(PpAdmin $admin, string $verificationCode): bool
    {
        if ($verificationCode === '') {
            return false;
        }

        if ((string) $admin->{'2fa_status'} === 'enable') {
            $this->ensureGoogleAuthenticatorLoaded();

            $secret = (string) ($admin->{'2fa_secret'} ?? '');

            if ($secret === '' || $secret === '--') {
                return false;
            }

            $authenticator = new \PHPGangsta_GoogleAuthenticator();

            return $authenticator->verifyCode($secret, $verificationCode, 2);
        }

        return Hash::check($verificationCode, (string) $admin->password);
    }

    private function generateStrongPassword(int $length = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%&!';

        return substr(str_shuffle(str_repeat($chars, 5)), 0, $length);
    }

    private function ensureGoogleAuthenticatorLoaded(): void
    {
        if (!class_exists('PHPGangsta_GoogleAuthenticator')) {
            require_once app_path('Support/SDK/GoogleAuthenticator.php');
        }
    }


    private function handleMerchantBulkAction(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'merchants')) {
            return $this->failure('Access denied', 'You need permission to perform this action.', $newCsrfToken);
        }

        $actionId = (string) $request->input('action_id', '');
        $selectedIds = (array) $request->input('selected_ids', []);

        $result = $this->merchantAdminActionService->bulkAction($actionId, $selectedIds);
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function handleMerchantCreate(Request $request): JsonResponse
    {
        $newCsrfToken = (string) csrf_token();
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->isLegacyUserAuthorized($legacy)) {
            return $this->failure('Request Failed', 'Invalid request', $newCsrfToken);
        }

        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'merchants')) {
            return $this->failure('Access denied', 'You need permission to perform this action.', $newCsrfToken);
        }

        $result = $this->merchantAdminActionService->create($request->all());
        $result['csrf_token'] = $newCsrfToken;

        return response()->json($result);
    }

    private function isSuperAdmin(array $legacy): bool
    {
        return ($legacy['global_user_response']['response'][0]['user_type'] ?? '') === 'superadmin';
    }

    private function failure(string $title, string $message, string $csrfToken): JsonResponse
    {
        return response()->json([
            'status' => 'false',
            'title' => $title,
            'message' => $message,
            'csrf_token' => $csrfToken,
        ]);
    }
}
