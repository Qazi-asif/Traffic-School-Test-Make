<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-line"></i> Revenue Dashboard</h2>
            <div class="btn-group">
                <a href="{{ route('admin.revenue.by-state', request()->query()) }}" class="btn btn-outline-primary">
                    <i class="fas fa-map-marker-alt"></i> By State
                </a>
                <a href="{{ route('admin.revenue.by-course', request()->query()) }}" class="btn btn-outline-primary">
                    <i class="fas fa-book"></i> By Course
                </a>
                <a href="{{ route('admin.revenue.export', request()->query()) }}" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start" class="form-control" value="{{ $start->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end" class="form-control" value="{{ $end->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Today</h6>
                        <h3 class="mb-0 text-success">${{ number_format($stats['today']['net_revenue'], 2) }}</h3>
                        <small class="text-muted">{{ $stats['today']['transaction_count'] }} transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">This Week</h6>
                        <h3 class="mb-0 text-primary">${{ number_format($stats['this_week']['net_revenue'], 2) }}</h3>
                        <small class="text-muted">{{ $stats['this_week']['transaction_count'] }} transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">This Month</h6>
                        <h3 class="mb-0 text-info">${{ number_format($stats['this_month']['net_revenue'], 2) }}</h3>
                        <small class="text-muted">{{ $stats['this_month']['transaction_count'] }} transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">This Year</h6>
                        <h3 class="mb-0 text-warning">${{ number_format($stats['this_year']['net_revenue'], 2) }}</h3>
                        <small class="text-muted">{{ $stats['this_year']['transaction_count'] }} transactions</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Selected Period: {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <h6 class="text-muted">Gross Revenue</h6>
                                <h4>${{ number_format($currentPeriod['gross_revenue'], 2) }}</h4>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="text-muted">Refunds</h6>
                                <h4 class="text-danger">${{ number_format($currentPeriod['refunds'], 2) }}</h4>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="text-muted">Net Revenue</h6>
                                <h4 class="text-success">${{ number_format($currentPeriod['net_revenue'], 2) }}</h4>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Transactions</h6>
                                <h4>{{ number_format($currentPeriod['transaction_count']) }}</h4>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Average Order</h6>
                                <h4>${{ number_format($currentPeriod['average_order'], 2) }}</h4>
                            </div>
                        </div>
                        @if($comparison['change_percent'] != 0)
                            <div class="alert {{ $comparison['change_percent'] > 0 ? 'alert-success' : 'alert-danger' }} mt-3">
                                <i class="fas fa-{{ $comparison['change_percent'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($comparison['change_percent']) }}% {{ $comparison['change_percent'] > 0 ? 'increase' : 'decrease' }} 
                                vs previous period ({{ $comparison['change_percent'] > 0 ? '+' : '' }}${{ number_format($comparison['change'], 2) }})
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Revenue by Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Revenue Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top States</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>State</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($byState, 0, 10) as $state)
                                        <tr>
                                            <td>{{ $state['state'] }}</td>
                                            <td class="text-end">${{ number_format($state['revenue'], 2) }}</td>
                                            <td class="text-end">{{ $state['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top Courses</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($byCourse, 0, 10) as $course)
                                        <tr>
                                            <td>{{ $course['course'] }}</td>
                                            <td class="text-end">${{ number_format($course['revenue'], 2) }}</td>
                                            <td class="text-end">{{ $course['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($trend, 'period')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode(array_column($trend, 'revenue')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Payment Method Chart
        const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_column($byPaymentMethod, 'method')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($byPaymentMethod, 'revenue')) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    </script>
</body>
</html>
