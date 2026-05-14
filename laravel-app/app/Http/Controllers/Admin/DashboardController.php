<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasLegacyEnvironment;
use App\Services\Admin\DashboardStatisticsService;
use App\Models\PpTransaction;
use App\Models\PpCustomer;
use App\Models\PpGateway;
use App\Models\PpInvoice;
use App\Models\PpEnv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use HasLegacyEnvironment;

    public function __construct(
        private readonly DashboardStatisticsService $statsService
    ) {}

    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {

        if (!Auth::guard('pp_admin')->check()) {
            return redirect()->route('native.auth.login');
        }

        $legacy = $this->setupLegacyGlobals($request);
        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $lastCron = PpEnv::where('brand_id', 'both')->where('option_name', 'last-cron-invocation')->value('value');
        if ($lastCron === '--' || empty($lastCron)) {
            $lastCron = null;
        }

        // Fetch tile statistics natively
        $stats = [
            'total_payments' => PpTransaction::where('brand_id', $brandId)->whereNotIn('status', ['initiated', 'expired'])->count(),
            'total_customers' => PpCustomer::where('brand_id', $brandId)->count(),
            'total_gateways' => PpGateway::where('brand_id', $brandId)->where('status', 'active')->count(),
            'pending_invoices' => PpInvoice::where('brand_id', $brandId)->where('status', 'unpaid')->count(),
            'last_cron' => $lastCron,

            // Sparkline Data
            'chart_total_payments' => $this->getSparklineData(PpTransaction::class, $brandId, null, ['initiated', 'expired']),
            'chart_pending_payments' => $this->getSparklineData(PpTransaction::class, $brandId, 'pending'),
            'chart_unpaid_invoices' => $this->getSparklineData(PpInvoice::class, $brandId, 'unpaid'),
            'chart_customers' => $this->getSparklineData(PpCustomer::class, $brandId),
        ];

        $data = array_merge($legacy, $stats, [
            'csrfToken' => csrf_token(),
        ]);

        // If it's an AJAX content request (from load_content in JS)
        if ($request->has('content') || $request->ajax()) {
            return view('admin.dashboard', $data);
        }

        // Full page load returns the shell layout
        return view('admin.layouts.app', $data);
    }

    private function getSparklineData(string $modelClass, string $brandId, ?string $status = null, array $excludeStatus = []): array
    {
        $labels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[$date] = 0;
        }

        $query = $modelClass::where('brand_id', $brandId)
            ->where('created_date', '>=', now()->subDays(29)->startOfDay());

        if ($status) {
            $query->where('status', $status);
        }
        if ($excludeStatus) {
            $query->whereNotIn('status', $excludeStatus);
        }

        $results = $query->selectRaw('DATE(created_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->get();

        foreach ($results as $row) {
            if (isset($labels[$row->day])) {
                $labels[$row->day] = (int) $row->total;
            }
        }

        return [
            'labels' => array_keys($labels),
            'data' => array_values($labels),
        ];
    }

    /**
     * Fetch transaction statistics for the dashboard chart.
     */
    public function transactionStatistics(Request $request): JsonResponse
    {
        $legacy = $this->setupLegacyGlobals($request);
        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');

        $result = $this->statsService->transactionStatistics(
            $brandId,
            (string) $request->input('date', 'this_year'),
            (string) $request->input('start', ''),
            (string) $request->input('end', '')
        );
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Fetch gateway statistics for the dashboard chart.
     */
    public function gatewayStatistics(Request $request): JsonResponse
    {
        $legacy = $this->setupLegacyGlobals($request);
        $brandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');

        $result = $this->statsService->gatewayStatistics(
            $brandId,
            (string) $request->input('date', 'this_year'),
            (string) $request->input('start', ''),
            (string) $request->input('end', '')
        );
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }
}
