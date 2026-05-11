# PipraPay - Immediate Action Plan (May 12 - May 19, 2026)

**Week Focus:** Fix Tests, Complete Dashboard, Expand Testing

**Total Effort This Week:** 40 hours  
**Expected Outcome:** 84% → 100% test pass rate, Dashboard complete, 60+ tests

---

## 🎯 Priority 1: Fix Failing Tests (8 hours)

### Task 1.1: Identify All Failing Tests
```bash
cd /Volumes/Project/Personal\ Project/ZontroPay/PipraPay-Laravel\ \(Non\ SaaS\)/laravel-app

# Run tests with verbose output
php artisan test --verbose 2>&1 | grep -A5 "FAILED"
```

**Current Failing Tests (7 total):**
- `HybridRouteWiringTest::payment_link_route_dispatch` - 400 error
- (6 others - need to identify)

### Task 1.2: Fix Payment Link Route Test
**File:** `tests/Feature/HybridRouteWiringTest.php` (Line 101)

**Issue:** POST to `/payment-link/pl_123` returns 400 instead of 200

**Debug Steps:**
```php
// 1. Check route definition
php artisan route:list | grep payment-link

// 2. Check CheckoutController
// File: app/Http/Controllers/Payment/CheckoutController.php
// Method: paymentLink()

// 3. Verify request format
// The test sends: ['action-v2' => 'payment-link']
// Check if controller expects this

// 4. Check middleware
// Any auth/validation issues?
```

**Expected Fix:** Update CheckoutController to handle test payload properly

### Task 1.3: Add Missing Test Assertions
Review all 7 failing tests and add proper assertions or fix route handling.

**Acceptance Criteria:**
- ✅ All 45 tests passing
- ✅ No skipped tests  
- ✅ All assertions valid

---

## 🎯 Priority 2: Complete Dashboard Migration (20 hours)

### Task 2.1: Finish Dashboard Statistics Service (6 hours)
**File:** `app/Services/Admin/DashboardStatisticsService.php`

**Current State:** Service exists but incomplete

**What To Add:**

```php
<?php

namespace App\Services\Admin;

use App\Models\PpTransaction;
use App\Models\PpBrand;
use App\Models\PpGateway;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatisticsService
{
    /**
     * Get complete dashboard statistics for a brand
     */
    public function getStatistics(string $brandId): array
    {
        $brand = PpBrand::find($brandId);
        
        if (!$brand) {
            return $this->getEmptyStats();
        }

        return [
            'total_transactions' => $this->getTotalTransactions($brandId),
            'total_revenue' => $this->getTotalRevenue($brandId),
            'pending_settlements' => $this->getPendingSettlements($brandId),
            'completed_today' => $this->getCompletedToday($brandId),
            'growth_percentage' => $this->getGrowthPercentage($brandId),
            'top_gateways' => $this->getTopGateways($brandId),
            'recent_transactions' => $this->getRecentTransactions($brandId),
            'transaction_by_status' => $this->getTransactionsByStatus($brandId),
        ];
    }

    /**
     * Total transaction count
     */
    private function getTotalTransactions(string $brandId): int
    {
        return PpTransaction::where('brand_id', $brandId)->count();
    }

    /**
     * Total revenue (completed transactions only)
     */
    private function getTotalRevenue(string $brandId): float
    {
        $total = PpTransaction::where('brand_id', $brandId)
            ->where('status', 'completed')
            ->sum('amount');
        
        return (float) ($total ?? 0);
    }

    /**
     * Pending settlements
     */
    private function getPendingSettlements(string $brandId): float
    {
        $total = PpTransaction::where('brand_id', $brandId)
            ->where('status', 'pending')
            ->sum('amount');
        
        return (float) ($total ?? 0);
    }

    /**
     * Transactions completed today
     */
    private function getCompletedToday(string $brandId): int
    {
        return PpTransaction::where('brand_id', $brandId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Month-over-month growth percentage
     */
    private function getGrowthPercentage(string $brandId): float
    {
        $current = PpTransaction::where('brand_id', $brandId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->subMonth(), now()])
            ->sum('amount');
        
        $previous = PpTransaction::where('brand_id', $brandId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])
            ->sum('amount');
        
        if ($previous <= 0) {
            return 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Top 5 gateways by transaction volume
     */
    private function getTopGateways(string $brandId): array
    {
        return PpTransaction::where('brand_id', $brandId)
            ->select('gateway', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('gateway')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'gateway' => $item->gateway,
                'transactions' => $item->count,
                'total_amount' => (float) $item->total,
            ])
            ->toArray();
    }

    /**
     * Get recent transactions (last 10)
     */
    private function getRecentTransactions(string $brandId): array
    {
        return PpTransaction::where('brand_id', $brandId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'reference' => $t->ref,
                'amount' => (float) $t->amount,
                'status' => $t->status,
                'gateway' => $t->gateway,
                'created_at' => $t->created_at->toIso8601String(),
                'created_relative' => $t->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Transactions grouped by status
     */
    private function getTransactionsByStatus(string $brandId): array
    {
        $statuses = ['completed', 'pending', 'failed', 'cancelled'];
        $result = [];
        
        foreach ($statuses as $status) {
            $result[$status] = PpTransaction::where('brand_id', $brandId)
                ->where('status', $status)
                ->count();
        }
        
        return $result;
    }

    /**
     * Empty stats template
     */
    private function getEmptyStats(): array
    {
        return [
            'total_transactions' => 0,
            'total_revenue' => 0.0,
            'pending_settlements' => 0.0,
            'completed_today' => 0,
            'growth_percentage' => 0.0,
            'top_gateways' => [],
            'recent_transactions' => [],
            'transaction_by_status' => [
                'completed' => 0,
                'pending' => 0,
                'failed' => 0,
                'cancelled' => 0,
            ],
        ];
    }
}
```

**Acceptance Criteria:**
- ✅ All statistics methods implemented
- ✅ Return proper data types
- ✅ Error handling
- ✅ Can be called from controller

### Task 2.2: Wire Dashboard Controller (4 hours)
**File:** `app/Http/Controllers/Admin/DashboardController.php`

**Update Controller:**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardStatisticsService;
use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatisticsService $statisticsService
    ) {}

    /**
     * Show admin dashboard
     */
    public function index(Request $request): View
    {
        // Get current brand from session or user
        $brandId = session('pp_brand')['id'] ?? auth()->user()->default_brand_id;
        
        // Get statistics
        $statistics = $this->statisticsService->getStatistics($brandId);
        
        return view('admin.dashboard.index', [
            'statistics' => $statistics,
            'pageTitle' => 'Dashboard',
        ]);
    }
}
```

**Acceptance Criteria:**
- ✅ Controller loads data from service
- ✅ Passes stats to view
- ✅ Proper brand resolution
- ✅ Error handling

### Task 2.3: Create Dashboard Blade View (4 hours)
**File:** `resources/views/admin/dashboard/index.blade.php`

**Create view:**
```blade
@extends('layouts.admin')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p class="text-muted">Welcome back! Here's your business overview.</p>
    </div>

    <!-- Key Statistics Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="icon-transactions"></i>
            </div>
            <div class="stat-content">
                <h3>Total Revenue</h3>
                <p class="stat-value">
                    {{ format_currency($statistics['total_revenue']) }}
                </p>
                <span class="stat-change {{ $statistics['growth_percentage'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $statistics['growth_percentage'] > 0 ? '+' : '' }}{{ number_format($statistics['growth_percentage'], 1) }}%
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="icon-check"></i>
            </div>
            <div class="stat-content">
                <h3>Completed</h3>
                <p class="stat-value">
                    {{ $statistics['transaction_by_status']['completed'] }}
                </p>
                <span class="stat-small">Total transactions</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="icon-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Pending</h3>
                <p class="stat-value">
                    {{ format_currency($statistics['pending_settlements']) }}
                </p>
                <span class="stat-small">Awaiting settlements</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="icon-today"></i>
            </div>
            <div class="stat-content">
                <h3>Today</h3>
                <p class="stat-value">
                    {{ $statistics['completed_today'] }}
                </p>
                <span class="stat-small">Transactions</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-row">
        <div class="chart-card">
            <h3>Transactions by Status</h3>
            <div id="status-chart"></div>
        </div>

        <div class="chart-card">
            <h3>Top Gateways</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Gateway</th>
                        <th>Transactions</th>
                        <th>Volume</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statistics['top_gateways'] as $gateway)
                    <tr>
                        <td>{{ ucfirst($gateway['gateway']) }}</td>
                        <td>{{ $gateway['transactions'] }}</td>
                        <td>{{ format_currency($gateway['total_amount']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="transactions-section">
        <h2>Recent Transactions</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statistics['recent_transactions'] as $transaction)
                <tr>
                    <td>
                        <a href="{{ route('admin.transactions.show', $transaction['id']) }}">
                            {{ $transaction['reference'] }}
                        </a>
                    </td>
                    <td>{{ format_currency($transaction['amount']) }}</td>
                    <td>{{ ucfirst($transaction['gateway']) }}</td>
                    <td>
                        <span class="badge-{{ $transaction['status'] }}">
                            {{ ucfirst($transaction['status']) }}
                        </span>
                    </td>
                    <td>{{ $transaction['created_relative'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No transactions yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Initialize charts if needed
    const statusData = {!! json_encode($statistics['transaction_by_status']) !!};
    // Chart.js implementation...
</script>
@endpush
@endsection
```

**Acceptance Criteria:**
- ✅ All statistics displayed
- ✅ Proper formatting (currency, dates)
- ✅ Responsive design
- ✅ Error handling

### Task 2.4: Create Dashboard Tests (6 hours)
**File:** `tests/Feature/Admin/DashboardTest.php`

**Create comprehensive tests:**
```php
<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\PpAdmin;
use App\Models\PpBrand;
use App\Models\PpTransaction;
use App\Models\PpGateway;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->brand = PpBrand::factory()->create();
        $this->admin = PpAdmin::factory()->create([
            'default_brand_id' => $this->brand->id,
        ]);
    }

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertViewIs('admin.dashboard.index');
        $response->assertViewHas('statistics');
    }

    public function test_dashboard_shows_correct_statistics(): void
    {
        // Create test transactions
        PpTransaction::factory()->create([
            'brand_id' => $this->brand->id,
            'status' => 'completed',
            'amount' => 1000,
        ]);
        
        PpTransaction::factory()->create([
            'brand_id' => $this->brand->id,
            'status' => 'pending',
            'amount' => 500,
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $stats = $response->viewData('statistics');
        
        $this->assertEquals(2, $stats['total_transactions']);
        $this->assertEquals(1000, $stats['total_revenue']);
        $this->assertEquals(500, $stats['pending_settlements']);
    }

    public function test_completed_today_calculated_correctly(): void
    {
        PpTransaction::factory()->create([
            'brand_id' => $this->brand->id,
            'status' => 'completed',
            'created_at' => now(),
        ]);
        
        PpTransaction::factory()->create([
            'brand_id' => $this->brand->id,
            'status' => 'completed',
            'created_at' => now()->subDay(),
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $stats = $response->viewData('statistics');
        
        $this->assertEquals(1, $stats['completed_today']);
    }

    public function test_growth_percentage_calculated(): void
    {
        $currentMonth = PpTransaction::factory()->create([
            'brand_id' => $this->brand->id,
            'status' => 'completed',
            'amount' => 1000,
            'created_at' => now(),
        ]);
        
        $previousMonth = PpTransaction::factory()->create([
            'brand_id' => $this->brand->id,
            'status' => 'completed',
            'amount' => 500,
            'created_at' => now()->subMonths(2),
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $stats = $response->viewData('statistics');
        
        // Growth: (1000 - 500) / 500 * 100 = 100%
        $this->assertEquals(100, $stats['growth_percentage']);
    }

    public function test_top_gateways_listed(): void
    {
        PpTransaction::factory()->count(5)->create([
            'brand_id' => $this->brand->id,
            'gateway' => 'sslcommerz',
            'status' => 'completed',
        ]);
        
        PpTransaction::factory()->count(3)->create([
            'brand_id' => $this->brand->id,
            'gateway' => 'bkash',
            'status' => 'completed',
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $stats = $response->viewData('statistics');
        
        $this->assertCount(2, $stats['top_gateways']);
        $this->assertEquals('sslcommerz', $stats['top_gateways'][0]['gateway']);
        $this->assertEquals(5, $stats['top_gateways'][0]['transactions']);
    }

    public function test_recent_transactions_limited_to_10(): void
    {
        PpTransaction::factory()->count(15)->create([
            'brand_id' => $this->brand->id,
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $stats = $response->viewData('statistics');
        
        $this->assertCount(10, $stats['recent_transactions']);
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $response = $this->get('/admin/dashboard');
        
        $response->assertRedirect('/admin/login');
    }

    public function test_empty_statistics_when_no_transactions(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $stats = $response->viewData('statistics');
        
        $this->assertEquals(0, $stats['total_transactions']);
        $this->assertEquals(0, $stats['total_revenue']);
        $this->assertEmpty($stats['top_gateways']);
    }
}
```

**Acceptance Criteria:**
- ✅ 10+ tests passing
- ✅ All statistics tested
- ✅ Edge cases covered
- ✅ Authentication tested

---

## 🎯 Priority 3: Expand Test Coverage (12 hours)

### Task 3.1: Create Service Unit Tests (6 hours)

**Create:** `tests/Unit/Services/DashboardStatisticsServiceTest.php`

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Admin\DashboardStatisticsService;
use App\Models\PpBrand;
use App\Models\PpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardStatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardStatisticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DashboardStatisticsService::class);
    }

    public function test_service_returns_empty_for_nonexistent_brand(): void
    {
        $stats = $this->service->getStatistics('nonexistent');
        
        $this->assertEquals(0, $stats['total_transactions']);
        $this->assertEquals(0, $stats['total_revenue']);
    }

    public function test_service_calculates_all_metrics(): void
    {
        $brand = PpBrand::factory()->create();
        
        PpTransaction::factory()->count(5)->create([
            'brand_id' => $brand->id,
            'status' => 'completed',
            'amount' => 100,
        ]);
        
        $stats = $this->service->getStatistics($brand->id);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_transactions', $stats);
        $this->assertArrayHasKey('total_revenue', $stats);
        $this->assertArrayHasKey('growth_percentage', $stats);
        $this->assertArrayHasKey('top_gateways', $stats);
        $this->assertArrayHasKey('recent_transactions', $stats);
    }
}
```

### Task 3.2: Create Integration Tests (4 hours)

**Create:** `tests/Feature/Dashboard/CompleteFlowTest.php`

```php
<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\PpAdmin;
use App\Models\PpBrand;
use App\Models\PpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompleteFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_dashboard_flow(): void
    {
        $brand = PpBrand::factory()->create();
        $admin = PpAdmin::factory()->create(['default_brand_id' => $brand->id]);
        
        // Create test data
        PpTransaction::factory()->count(10)->create([
            'brand_id' => $brand->id,
            'status' => 'completed',
        ]);
        
        // Access dashboard
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        // Verify response
        $response->assertOk();
        $response->assertViewIs('admin.dashboard.index');
        
        // Verify all components loaded
        $stats = $response->viewData('statistics');
        $this->assertEquals(10, $stats['total_transactions']);
    }
}
```

### Task 3.3: Add More Integration Tests (2 hours)

Create additional tests for:
- Admin authentication flow
- Transaction listing
- Brand switching
- Permission checks

---

## 📋 Daily Checklist

### Monday (May 12)
- [ ] 2 hours: Debug and fix failing tests
- [ ] 2 hours: Understand current dashboard service
- [ ] 2 hours: Plan dashboard statistics implementation
- **Total: 6 hours**

### Tuesday (May 13)
- [ ] 4 hours: Implement dashboard statistics methods
- [ ] 2 hours: Test statistics calculations
- **Total: 6 hours**

### Wednesday (May 14)
- [ ] 2 hours: Fix remaining test issues
- [ ] 4 hours: Wire dashboard controller
- **Total: 6 hours**

### Thursday (May 15)
- [ ] 2 hours: Create dashboard Blade view
- [ ] 2 hours: Add styling/formatting
- [ ] 2 hours: Create dashboard feature tests
- **Total: 6 hours**

### Friday (May 16-17)
- [ ] 4 hours: Create service unit tests
- [ ] 2 hours: Create integration tests
- [ ] 2 hours: Fix any remaining issues
- **Total: 8 hours**

---

## ✅ Definition of Done

This week is complete when:

1. **Tests:** ✅ All 45 tests passing
   - ```bash
     php artisan test --verbose
     # Output: Tests: 45 passed
     ```

2. **Dashboard:** ✅ Complete and functional
   - Statistics calculating correctly
   - View displaying all data
   - Responsive design working

3. **Coverage:** ✅ 60+ tests created
   - Feature tests
   - Unit tests
   - Integration tests

4. **Documentation:** ✅ Updated
   - Code commented
   - Tests described
   - Progress logged

---

## 🚀 If Ahead of Schedule

If you complete these tasks before Friday:

1. Start **Priority 2.4: Admin Pages Migration**
   - Create admin page controllers
   - Migrate page queries
   - Replace legacy page rendering

2. Create **API Tests**
   - Test all API endpoints
   - Test error handling
   - Test rate limiting

3. Implement **Repository Pattern (Optional)**
   - Create data access interfaces
   - Implement with Eloquent

---

## 📞 Quick Reference Commands

```bash
# Run tests
php artisan test --verbose

# Run specific test
php artisan test tests/Feature/Admin/DashboardTest.php

# Check routes
php artisan route:list

# Debug queries
php artisan tinker

# Clear cache
php artisan cache:clear

# Run migrations
php artisan migrate

# Create test data
php artisan tinker
>>> App\Models\PpBrand::factory()->create()
```

---

**Expected Result:** 50-60% → 65-70% migration complete by Friday, May 17
