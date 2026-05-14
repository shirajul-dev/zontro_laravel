<style>
  #chart-gateway-statistics .apexcharts-legend.apexcharts-align-center.apx-legend-position-bottom{
      top: 263px !important;
  }
</style>

<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ config('app.name') }}</div>
                <h2 class="page-title">Dashboard</h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="page-pretitle">Last cron invocation</div>
                <h3>
                    @if(empty($last_cron))
                        Unknown
                    @else
                        {{ \Carbon\Carbon::parse($last_cron)->diffForHumans() }}
                    @endif
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row g-4">
            <!-- Total Payments -->
            <div class="col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body mb-0 pb-0">
                        <div class="card-title m-0 mb-1 fs-4">Total Payments</div>
                        <div class="h1 mb-0">{{ number_format($total_payments, 0) }}</div>
                    </div>
                    <div id="chart-total-payment"></div>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body mb-0 pb-0">
                        <div class="card-title m-0 mb-1 fs-4">Pending Payments</div>
                        <div class="h1 mb-0">{{ number_format($chart_pending_payments['data'][29] ?? 0, 0) }}</div> {{-- This is just the current count from stats but we used count() in controller for tile --}}
                    </div>
                    <div id="chart-pending-payment"></div>
                </div>
            </div>

            <!-- Unpaid Invoices -->
            <div class="col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body mb-0 pb-0">
                        <div class="card-title m-0 mb-1 fs-4">Unpaid Invoices</div>
                        <div class="h1 mb-0">{{ number_format($pending_invoices, 0) }}</div>
                    </div>
                    <div id="chart-unpaid-invoice"></div>
                </div>
            </div>

            <!-- Customers -->
            <div class="col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body mb-0 pb-0">
                        <div class="card-title m-0 mb-1 fs-4">Customers</div>
                        <div class="h1 mb-0">{{ number_format($total_customers, 0) }}</div>
                    </div>
                    <div id="chart-customer"></div>
                </div>
            </div>

            <!-- Transaction Statistics -->
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Transaction Statistics</h3>
                      <div class="card-actions btn-actions">
                        <div class="position-relative">
                            <span class="dashboard-transaction-statistics-loading"></span>
                            <svg onclick="toggleFilter('filterDropdown-transaction-statistics')" style="cursor:pointer"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icon-tabler-filter">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z"></path>
                            </svg>

                            <!-- Dropdown -->
                            <div id="filterDropdown-transaction-statistics" class="card shadow position-absolute end-0 mt-2 p-3" style="width: 300px; display:none; z-index:1050;">
                                <label class="form-label fw-bold mb-2">Filter By</label>

                                <select class="form-select mb-2" id="dateFilter-transaction-statistics" onchange="handleFilterChangeTransactionStatistics(this.value)">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This week</option>
                                    <option value="last_week">Last week</option>
                                    <option value="this_month">This month</option>
                                    <option value="last_month">Last month</option>
                                    <option value="this_year" selected>This year</option>
                                    <option value="previous_year">Previous year</option>
                                    <option value="custom">Custom Range</option>
                                </select>

                                <!-- Custom Range -->
                                <div id="customRange-transaction-statistics" class="d-none">
                                    <label class="form-label mt-2">Start Date</label>
                                    <input type="date" id="startDate-transaction-statistics" class="form-control">

                                    <label class="form-label mt-2">End Date</label>
                                    <input type="date" id="endDate-transaction-statistics" class="form-control">

                                    <button class="btn btn-primary mt-3 w-100" onclick="applyCustomRangeTransactionStatistics()">Apply</button>
                                </div>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                       <div id="chart-transaction-statistics" style="height: 303px !important; min-height: 303px !important;"></div>
                    </div>
                </div>
            </div>

            <!-- Gateway Statistics -->
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Gateway Statistics</h3>
                      <div class="card-actions btn-actions">
                        <div class="position-relative">
                            <span class="dashboard-gateway-statistics-loading"></span>
                            <svg onclick="toggleFilter('filterDropdown-gateway-statistics')" style="cursor:pointer"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icon-tabler-filter">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z"></path>
                            </svg>

                            <!-- Dropdown -->
                            <div id="filterDropdown-gateway-statistics" class="card shadow position-absolute end-0 mt-2 p-3" style="width: 300px; display:none; z-index:1050;">
                                <label class="form-label fw-bold mb-2">Filter By</label>

                                <select class="form-select mb-2" id="dateFilter-gateway-statistics" onchange="handleFilterChangeGatewayStatistics(this.value)">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This week</option>
                                    <option value="last_week">Last week</option>
                                    <option value="this_month">This month</option>
                                    <option value="last_month">Last month</option>
                                    <option value="this_year" selected>This year</option>
                                    <option value="previous_year">Previous year</option>
                                    <option value="custom">Custom Range</option>
                                </select>

                                <!-- Custom Range -->
                                <div id="customRange-gateway-statistics" class="d-none">
                                    <label class="form-label mt-2">Start Date</label>
                                    <input type="date" id="startDate-gateway-statistics" class="form-control">

                                    <label class="form-label mt-2">End Date</label>
                                    <input type="date" id="endDate-gateway-statistics" class="form-control">

                                    <button class="btn btn-primary mt-3 w-100" onclick="applyCustomRangeGatewayStatistics()">Apply</button>
                                </div>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                       <div id="chart-gateway-statistics" style="height: 303px !important;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function renderSparkline(id, labels, data, colorClass) {
        if (!window.ApexCharts) return;
        const color = `var(--tblr-${colorClass})`;

        new ApexCharts(document.getElementById(id), {
            chart: {
                type: "area",
                fontFamily: "inherit",
                height: 40,
                sparkline: { enabled: true },
                animations: { enabled: false }
            },
            dataLabels: { enabled: false },
            fill: {
                type: "solid",
                colors: [`color-mix(in srgb, transparent, ${color} 16%)`]
            },
            stroke: {
                width: 2,
                curve: "smooth",
                lineCap: "round"
            },
            series: [{ data: data }],
            tooltip: { theme: "dark" },
            grid: { strokeDashArray: 4 },
            xaxis: {
                type: "datetime",
                labels: { show: false },
                axisBorder: { show: false },
                tooltip: { enabled: false }
            },
            yaxis: { labels: { show: false } },
            labels: labels,
            colors: [color],
            legend: { show: false }
        }).render();
    }

    // Initialize Sparklines
    renderSparkline("chart-total-payment", @json($chart_total_payments['labels']), @json($chart_total_payments['data']), "primary");
    renderSparkline("chart-pending-payment", @json($chart_pending_payments['labels']), @json($chart_pending_payments['data']), "warning");
    renderSparkline("chart-unpaid-invoice", @json($chart_unpaid_invoices['labels']), @json($chart_unpaid_invoices['data']), "danger");
    renderSparkline("chart-customer", @json($chart_customers['labels']), @json($chart_customers['data']), "success");

    function toggleFilter(id) {
        const el = document.getElementById(id);
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    function load_dashboard_transaction_statistics() {
        const el = document.getElementById('filterDropdown-transaction-statistics');
        el.style.display = 'none';

        var date = $('#dateFilter-transaction-statistics').val();
        var start = $('#startDate-transaction-statistics').val();
        var end = $('#endDate-transaction-statistics').val();

        document.querySelector(".dashboard-transaction-statistics-loading").innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div>';

        $.ajax({
            type: 'POST',
            url: '{{ route("admin.dashboard.transaction-stats") }}',
            data: { _token: '{{ csrf_token() }}', date: date, start: start, end: end },
            dataType: 'json',
            success: function (res) {
                document.querySelector(".dashboard-transaction-statistics-loading").innerHTML = '';
                if (window.chartTransactionStatistics) window.chartTransactionStatistics.destroy();

                window.chartTransactionStatistics = new ApexCharts(document.getElementById("chart-transaction-statistics"), {
                    chart: { type: "line", height: 288, fontFamily: "inherit", toolbar: { show: false }, animations: { enabled: false } },
                    stroke: { width: 2, curve: "smooth", lineCap: "round" },
                    series: [
                        { name: "Total", data: res.total },
                        { name: "Complete", data: res.complete },
                        { name: "Pending", data: res.pending }
                    ],
                    xaxis: { type: "category", categories: res.labels, labels: { padding: 0 } },
                    yaxis: { labels: { padding: 4 } },
                    grid: { strokeDashArray: 4, padding: { top: -20, right: 0, left: -4, bottom: -4 } },
                    tooltip: { theme: "dark" },
                    legend: { show: true, position: "bottom" },
                    colors: ["var(--tblr-primary)", "var(--tblr-success)", "var(--tblr-warning)"]
                });
                window.chartTransactionStatistics.render();
                load_dashboard_gateway_statistics();
            }
        });
    }

    function load_dashboard_gateway_statistics() {
        const el = document.getElementById('filterDropdown-gateway-statistics');
        el.style.display = 'none';

        var date = $('#dateFilter-gateway-statistics').val();
        var start = $('#startDate-gateway-statistics').val();
        var end = $('#endDate-gateway-statistics').val();

        document.querySelector(".dashboard-gateway-statistics-loading").innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div>';

        $.ajax({
            type: 'POST',
            url: '{{ route("admin.dashboard.gateway-stats") }}',
            data: { _token: '{{ csrf_token() }}', date: date, start: start, end: end },
            dataType: 'json',
            success: function (res) {
                document.querySelector(".dashboard-gateway-statistics-loading").innerHTML = '';
                if (window.chartGatewayStatistics) window.chartGatewayStatistics.destroy();

                const data = res.gateway_labels.map(label => res.data[label] ? res.data[label].reduce((a, b) => a + b, 0) : 0);

                window.chartGatewayStatistics = new ApexCharts(document.getElementById("chart-gateway-statistics"), {
                    chart: { type: "donut", height: 290, fontFamily: "inherit", sparkline: { enabled: true }, animations: { enabled: false } },
                    series: data,
                    labels: res.gateway_labels,
                    colors: res.colors,
                    tooltip: { theme: "dark", fillSeriesColor: false },
                    grid: { strokeDashArray: 4 },
                    legend: { show: true, position: "bottom", offsetY: 12 }
                });
                window.chartGatewayStatistics.render();
            }
        });
    }

    function handleFilterChangeTransactionStatistics(value) {
        const custom = document.getElementById('customRange-transaction-statistics');
        if (value === 'custom') custom.classList.remove('d-none');
        else {
            custom.classList.add('d-none');
            load_dashboard_transaction_statistics();
        }
    }

    function applyCustomRangeTransactionStatistics() {
        load_dashboard_transaction_statistics();
    }

    function handleFilterChangeGatewayStatistics(value) {
        const custom = document.getElementById('customRange-gateway-statistics');
        if (value === 'custom') custom.classList.remove('d-none');
        else {
            custom.classList.add('d-none');
            load_dashboard_gateway_statistics();
        }
    }

    function applyCustomRangeGatewayStatistics() {
        load_dashboard_gateway_statistics();
    }

    $(document).ready(function() {
        load_dashboard_transaction_statistics();
    });
</script>
