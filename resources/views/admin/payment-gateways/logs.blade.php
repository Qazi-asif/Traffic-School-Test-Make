<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $gateway->name }} - Activity Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <a href="{{ route('admin.payment-gateways.show', $gateway) }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left"></i> Back to {{ $gateway->name }}
            </a>
            <h2 class="mt-2"><i class="fas fa-history"></i> Activity Logs</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Action</th>
                                <th>User</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    <small>{{ $log->created_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                                </td>
                                <td>
                                    @if($log->action === 'activated')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Activated
                                    </span>
                                    @elseif($log->action === 'deactivated')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Deactivated
                                    </span>
                                    @elseif($log->action === 'settings_changed')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-edit"></i> Settings Changed
                                    </span>
                                    @elseif($log->action === 'test_connection')
                                    <span class="badge bg-info">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </span>
                                    @elseif($log->action === 'mode_changed')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-exchange-alt"></i> Mode Changed
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-info-circle"></i> {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->user)
                                    <i class="fas fa-user"></i> {{ $log->user->name }}
                                    @else
                                    <span class="text-muted"><i class="fas fa-robot"></i> System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->new_values)
                                    <button class="btn btn-sm btn-outline-info" onclick="toggleDetails({{ $log->id }})">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <div id="details-{{ $log->id }}" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                        <pre class="mb-0 small">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No activity logs found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleDetails(logId) {
        const details = document.getElementById('details-' + logId);
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
    }
    </script>
</body>
</html>
