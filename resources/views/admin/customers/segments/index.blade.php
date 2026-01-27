<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Segments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-users-cog"></i> Customer Segments</h2>
            <p class="text-muted">Analyze and manage customer enrollment segments</p>
        </div>

        <!-- Segment Cards Grid -->
        <div class="row g-4 mb-4">
            <!-- Completed This Month -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.completed-monthly') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Completed This Month</p>
                                    <h3 class="text-success mb-0">{{ $counts['completed_this_month'] }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Paid, Not Completed -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.paid-incomplete') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Paid, Not Completed</p>
                                    <h3 class="text-primary mb-0">{{ $counts['paid_incomplete'] }}</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-dollar-sign fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- In Progress -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.in-progress') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">In Progress</p>
                                    <h3 class="text-info mb-0">{{ $counts['in_progress'] }}</h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-spinner fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Abandoned -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.abandoned') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Abandoned (30+ days)</p>
                                    <h3 class="text-warning mb-0">{{ $counts['abandoned'] }}</h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Expiring Soon -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.expiring-soon') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Expiring Soon (7 days)</p>
                                    <h3 class="text-danger mb-0">{{ $counts['expiring_soon'] }}</h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Expired -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.expired') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Expired Recently</p>
                                    <h3 class="text-secondary mb-0">{{ $counts['expired'] }}</h3>
                                </div>
                                <div class="bg-secondary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-times-circle fa-2x text-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Never Started -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.never-started') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Never Started</p>
                                    <h3 class="text-dark mb-0">{{ $counts['never_started'] }}</h3>
                                </div>
                                <div class="bg-dark bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-pause-circle fa-2x text-dark"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Struggling -->
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.customers.struggling') }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Struggling (Quiz)</p>
                                    <h3 class="text-danger mb-0">{{ $counts['struggling'] }}</h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-question-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Monthly Completion Trend Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title mb-4"><i class="fas fa-chart-line"></i> Monthly Completion Trend</h4>
                <div class="row">
                    @foreach($trend as $month)
                    <div class="col text-center">
                        <div class="mb-2">
                            <div class="bg-primary" style="height: {{ $month['count'] > 0 ? ($month['count'] / $trend->max('count') * 200) : 5 }}px; width: 100%; border-radius: 4px 4px 0 0;"></div>
                        </div>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $month['month'] }}</small>
                        <strong class="d-block">{{ $month['count'] }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Saved Custom Segments -->
        @if($savedSegments->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4"><i class="fas fa-bookmark"></i> Saved Custom Segments</h4>
                <div class="list-group">
                    @foreach($savedSegments as $segment)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ $segment->name }}</h6>
                            <p class="mb-0 text-muted small">{{ $segment->description }}</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.customers.custom-segment', $segment->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <form action="{{ route('admin.customers.segments.delete', $segment->id) }}" method="POST" onsubmit="return confirm('Delete this segment?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
