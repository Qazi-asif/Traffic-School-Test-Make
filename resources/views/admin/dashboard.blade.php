<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Multi-State Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header { background: linear-gradient(135deg, #6c757d, #495057); color: white; }
        .state-card { border-radius: 10px; transition: transform 0.2s; }
        .state-card:hover { transform: translateY(-2px); }
        .florida-card { border-left: 4px solid #2c5aa0; }
        .missouri-card { border-left: 4px solid #28a745; }
        .texas-card { border-left: 4px solid #ffc107; }
        .delaware-card { border-left: 4px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="admin-header py-4">
        <div class="container">
            <h1><i class="fas fa-tachometer-alt"></i> Multi-State Traffic School Admin</h1>
            <p class="mb-0">Centralized management for all state operations</p>
        </div>
    </div>

    <div class="container mt-4">
        @if(isset($message))
            <div class="alert alert-warning">
                <h4><i class="fas fa-exclamation-triangle"></i> Authentication Notice</h4>
                <p class="mb-0">{{ $message }}. <a href="/login">Login here</a> to access full admin features.</p>
            </div>
        @else
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Phase 2 Integration Complete!</h4>
                <p class="mb-0">Admin dashboard is now connected with real Laravel controllers.</p>
            </div>
        @endif

        <div class="row">
            <!-- Florida Card -->
            <div class="col-md-3 mb-4">
                <div class="card state-card florida-card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-map-marker-alt"></i> Florida</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Courses:</strong> {{ $stats['florida']['courses'] ?? 0 }}</p>
                        <p><strong>Active:</strong> {{ $stats['florida']['active_courses'] ?? 0 }}</p>
                        <a href="{{ route('admin.florida.courses.index') }}" class="btn btn-primary btn-sm">Manage Courses</a>
                        <a href="{{ route('florida.dashboard') }}" class="btn btn-outline-primary btn-sm">View Portal</a>
                    </div>
                </div>
            </div>

            <!-- Missouri Card -->
            <div class="col-md-3 mb-4">
                <div class="card state-card missouri-card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-map-marker-alt"></i> Missouri</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Courses:</strong> {{ $stats['missouri']['courses'] ?? 0 }}</p>
                        <p><strong>Active:</strong> {{ $stats['missouri']['active_courses'] ?? 0 }}</p>
                        <a href="{{ route('admin.missouri.courses.index') }}" class="btn btn-success btn-sm">Manage Courses</a>
                        <a href="{{ route('missouri.dashboard') }}" class="btn btn-outline-success btn-sm">View Portal</a>
                    </div>
                </div>
            </div>

            <!-- Texas Card -->
            <div class="col-md-3 mb-4">
                <div class="card state-card texas-card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-map-marker-alt"></i> Texas</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Courses:</strong> {{ $stats['texas']['courses'] ?? 0 }}</p>
                        <p><strong>Active:</strong> {{ $stats['texas']['active_courses'] ?? 0 }}</p>
                        <a href="#" class="btn btn-warning btn-sm">Manage Courses</a>
                        <a href="{{ route('texas.dashboard') }}" class="btn btn-outline-warning btn-sm">View Portal</a>
                    </div>
                </div>
            </div>

            <!-- Delaware Card -->
            <div class="col-md-3 mb-4">
                <div class="card state-card delaware-card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-map-marker-alt"></i> Delaware</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Courses:</strong> {{ $stats['delaware']['courses'] ?? 0 }}</p>
                        <p><strong>Active:</strong> {{ $stats['delaware']['active_courses'] ?? 0 }}</p>
                        <a href="#" class="btn btn-info btn-sm">Manage Courses</a>
                        <a href="{{ route('delaware.dashboard') }}" class="btn btn-outline-info btn-sm">View Portal</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> System Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="text-primary">{{ $stats['total_users'] ?? 0 }}</h3>
                                <p>Total Users</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-success">{{ 
                                    ($stats['florida']['courses'] ?? 0) + 
                                    ($stats['missouri']['courses'] ?? 0) + 
                                    ($stats['texas']['courses'] ?? 0) + 
                                    ($stats['delaware']['courses'] ?? 0) 
                                }}</h3>
                                <p>Total Courses</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-info">4</h3>
                                <p>Active States</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-warning">✅</h3>
                                <p>System Status</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-cogs"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <a href="/admin/test" class="btn btn-outline-secondary me-2">Test Admin Route</a>
                        <a href="/florida/test" class="btn btn-outline-primary me-2">Test Florida</a>
                        <a href="/missouri/test" class="btn btn-outline-success me-2">Test Missouri</a>
                        <a href="/texas/test" class="btn btn-outline-warning me-2">Test Texas</a>
                        <a href="/delaware/test" class="btn btn-outline-info me-2">Test Delaware</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>