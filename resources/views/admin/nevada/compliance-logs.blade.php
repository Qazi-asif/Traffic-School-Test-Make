<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nevada Compliance Logs - Admin</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Nevada Compliance Logs</h1>
                <a href="/admin/nevada/compliance-logs/export?{{ http_build_query(request()->all()) }}" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/nevada/compliance-logs">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Log Type</label>
                                <select name="log_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="login" {{ request('log_type') === 'login' ? 'selected' : '' }}>Login</option>
                                    <option value="chapter_start" {{ request('log_type') === 'chapter_start' ? 'selected' : '' }}>Chapter Start</option>
                                    <option value="chapter_complete" {{ request('log_type') === 'chapter_complete' ? 'selected' : '' }}>Chapter Complete</option>
                                    <option value="quiz_attempt" {{ request('log_type') === 'quiz_attempt' ? 'selected' : '' }}>Quiz Attempt</option>
                                    <option value="quiz_pass" {{ request('log_type') === 'quiz_pass' ? 'selected' : '' }}>Quiz Pass</option>
                                    <option value="quiz_fail" {{ request('log_type') === 'quiz_fail' ? 'selected' : '' }}>Quiz Fail</option>
                                    <option value="completion" {{ request('log_type') === 'completion' ? 'selected' : '' }}>Completion</option>
                                    <option value="certificate" {{ request('log_type') === 'certificate' ? 'selected' : '' }}>Certificate</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Enrollment ID</th>
                                <th>Log Type</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                <td>{{ $log->user->email ?? 'N/A' }}</td>
                                <td>{{ $log->enrollment_id ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $log->log_type }}</span>
                                </td>
                                <td>{{ $log->ip_address }}</td>
                                <td>
                                    @if($log->details)
                                        <button class="btn btn-sm btn-secondary" onclick="showDetails({{ json_encode($log->details) }})">
                                            View
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No logs found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="detailsContent"></pre>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetails(details) {
            document.getElementById('detailsContent').textContent = JSON.stringify(details, null, 2);
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        }
    </script>
</body>
</html>
