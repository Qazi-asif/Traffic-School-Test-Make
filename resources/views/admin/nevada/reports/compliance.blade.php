<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nevada Compliance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
    
    <div style="margin-left: 280px; padding: 2rem;">
        <div class="container-fluid">
            <h1 class="mb-4">Nevada Compliance Report</h1>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/nevada/reports/compliance">
                        <div class="row">
                            <div class="col-md-4">
                                <label>From Date</label>
                                <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label>To Date</label>
                                <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-chart-bar"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Logs</h5>
                            <h2>{{ $report['total_logs'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Unique Users</h5>
                            <h2>{{ $report['unique_users'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Enrollments</h5>
                            <h2>{{ $report['unique_enrollments'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Date Range</h5>
                            <p class="mb-0">{{ $from->format('M d') }} - {{ $to->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Activity Breakdown by Type</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Activity Type</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['by_type'] as $type => $count)
                            <tr>
                                <td>{{ str_replace('_', ' ', ucfirst($type)) }}</td>
                                <td>{{ $count }}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($count / $report['total_logs']) * 100 }}%">
                                            {{ round(($count / $report['total_logs']) * 100, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
