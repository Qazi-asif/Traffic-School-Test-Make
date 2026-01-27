<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Court Mailing Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-envelope"></i> Court Mailing Dashboard</h2>
            <p class="text-muted">Track physical mail sent to courts and customers</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.mail-court.pending') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Pending</p>
                                    <h3 class="text-warning mb-0">{{ $stats['pending'] }}</h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.mail-court.printed') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Ready to Mail</p>
                                    <h3 class="text-info mb-0">{{ $stats['printed'] }}</h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-print fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.mail-court.mailed') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">In Transit</p>
                                    <h3 class="text-primary mb-0">{{ $stats['mailed'] }}</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-shipping-fast fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.mail-court.completed') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Delivered This Month</p>
                                    <h3 class="text-success mb-0">{{ $stats['delivered_this_month'] }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.mail-court.returned') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Returned</p>
                                    <h3 class="text-danger mb-0">{{ $stats['returned'] }}</h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-undo fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-bolt"></i> Quick Actions</h5>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.mail-court.pending') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> View Pending Queue
                    </a>
                    <a href="{{ route('admin.mail-court.batches') }}" class="btn btn-info">
                        <i class="fas fa-layer-group"></i> Manage Batches
                    </a>
                    <a href="{{ route('admin.mail-court.reports') }}" class="btn btn-secondary">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-history"></i> Recent Activity</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Court</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $mailing)
                            <tr>
                                <td>{{ $mailing->enrollment->user->name ?? 'N/A' }}</td>
                                <td>{{ $mailing->court->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($mailing->mailing_type) }}</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'printed' => 'info',
                                            'mailed' => 'primary',
                                            'delivered' => 'success',
                                            'returned' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$mailing->status] ?? 'secondary' }}">
                                        {{ ucfirst($mailing->status) }}
                                    </span>
                                </td>
                                <td>{{ $mailing->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.mail-court.show', $mailing->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No recent activity</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
