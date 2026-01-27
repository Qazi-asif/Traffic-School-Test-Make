<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue by Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book"></i> Revenue by Course</h2>
            <div class="btn-group">
                <a href="{{ route('admin.revenue.dashboard', request()->query()) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="{{ route('admin.revenue.export', request()->query()) }}" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start" class="form-control" value="{{ $start->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end" class="form-control" value="{{ $end->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Revenue</h6>
                        <h3 class="mb-0">${{ number_format($stats['net_revenue'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Enrollments</h6>
                        <h3 class="mb-0">{{ number_format($stats['transaction_count']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Courses</h6>
                        <h3 class="mb-0">{{ count($byCourse) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Breakdown by Course</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Enrollments</th>
                                <th class="text-end">Avg Price</th>
                                <th class="text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byCourse as $course)
                                <tr>
                                    <td>{{ $course['course'] }}</td>
                                    <td class="text-end">${{ number_format($course['revenue'], 2) }}</td>
                                    <td class="text-end">{{ number_format($course['count']) }}</td>
                                    <td class="text-end">${{ number_format($course['revenue'] / $course['count'], 2) }}</td>
                                    <td class="text-end">{{ number_format(($course['revenue'] / $stats['net_revenue']) * 100, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th>Total</th>
                                <th class="text-end">${{ number_format($stats['net_revenue'], 2) }}</th>
                                <th class="text-end">{{ number_format($stats['transaction_count']) }}</th>
                                <th class="text-end">${{ number_format($stats['average_order'], 2) }}</th>
                                <th class="text-end">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
