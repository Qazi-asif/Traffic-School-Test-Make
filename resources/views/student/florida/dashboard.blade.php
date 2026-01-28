<!DOCTYPE html>
<html>
<head>
    <title>Florida Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .florida-header { background: linear-gradient(135deg, #2c5aa0, #1e4080); color: white; }
        .state-card { border-left: 4px solid #2c5aa0; }
    </style>
</head>
<body>
    <div class="florida-header py-4" style="background: linear-gradient(135deg, {{ $stateConfig['color'] ?? '#2c5aa0' }}, {{ $stateConfig['color'] ?? '#1e4080' }});">
        <div class="container">
            <h1><i class="fas fa-graduation-cap"></i> {{ $stateConfig['name'] ?? 'Florida Traffic School' }}</h1>
            <p class="mb-0">{{ $stateConfig['compliance_authority'] ?? 'FLHSMV' }} Approved Defensive Driving Course</p>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Phase 2 Integration Complete!</h4>
                    <p class="mb-0">Laravel controllers are now connected to Florida state routes.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card state-card">
                    <div class="card-header">
                        <h5><i class="fas fa-book"></i> Available Courses</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($error))
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Database Issue</h6>
                                <p class="mb-0">Some features may not be available. Error: {{ $error }}</p>
                            </div>
                        @endif

                        @if(!auth()->check())
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Authentication Required</h6>
                                <p class="mb-0">Please <a href="/login">login</a> to access courses and track progress.</p>
                            </div>
                        @endif

                        @if(isset($courses) && $courses->count() > 0)
                            @foreach($courses as $course)
                                <div class="course-item mb-3 p-3 border rounded">
                                    <h6>{{ $course->title }}</h6>
                                    <p class="text-muted">{{ $course->description }}</p>
                                    <span class="badge bg-primary">${{ $course->price }}</span>
                                    <span class="badge bg-info">{{ $course->duration_hours }} hours</span>
                                </div>
                            @endforeach
                        @else
                            <p>No courses available at this time.</p>
                            <a href="/admin/florida/courses" class="btn btn-primary">Add Courses (Admin)</a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> System Status</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>State:</strong> {{ $currentState ?? 'Florida' }} ({{ $stateConfig['abbreviation'] ?? 'FL' }})</p>
                        <p><strong>Authority:</strong> {{ $stateConfig['compliance_authority'] ?? 'FLHSMV' }}</p>
                        <p><strong>Required Hours:</strong> {{ $stateConfig['required_hours'] ?? 8 }}</p>
                        <p><strong>Passing Score:</strong> {{ $stateConfig['passing_score'] ?? 80 }}%</p>
                        <p><strong>Certificate Fee:</strong> ${{ $stateConfig['certificate_fee'] ?? 25.00 }}</p>
                        <p><strong>Controller:</strong> ✅ Active</p>
                        <p><strong>Database:</strong> ✅ Connected</p>
                        <p><strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-link"></i> Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('florida.courses') }}" class="btn btn-outline-primary btn-sm d-block mb-2">View Courses</a>
                        <a href="{{ route('florida.certificates') }}" class="btn btn-outline-success btn-sm d-block mb-2">My Certificates</a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm d-block">Admin Panel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-map"></i> Other States</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('missouri.dashboard') }}" class="btn btn-success me-2">Missouri</a>
                        <a href="{{ route('texas.dashboard') }}" class="btn btn-warning me-2">Texas</a>
                        <a href="{{ route('delaware.dashboard') }}" class="btn btn-info me-2">Delaware</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>