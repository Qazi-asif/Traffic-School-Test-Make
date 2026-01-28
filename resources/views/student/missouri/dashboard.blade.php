<!DOCTYPE html>
<html>
<head>
    <title>Missouri Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .missouri-header { background: linear-gradient(135deg, #28a745, #1e7e34); color: white; }
        .state-card { border-left: 4px solid #28a745; }
        .missouri-badge { background: #28a745; }
        .missouri-btn { background: #28a745; border-color: #28a745; }
        .missouri-btn:hover { background: #1e7e34; border-color: #1e7e34; }
    </style>
</head>
<body>
    <div class="missouri-header py-4">
        <div class="container">
            <h1><i class="fas fa-graduation-cap"></i> Missouri Traffic School</h1>
            <p class="mb-0">Missouri Department of Revenue Approved Driver Improvement Program</p>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Missouri State Portal Active!</h4>
                    <p class="mb-0">Professional Missouri dashboard with state-specific branding and functionality.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card state-card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-book"></i> Missouri Driver Improvement Courses</h5>
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
                                <p class="mb-0">Please <a href="/login">login</a> to access Missouri courses and track progress.</p>
                            </div>
                        @endif

                        @if(isset($courses) && $courses->count() > 0)
                            @foreach($courses as $course)
                                <div class="course-item mb-3 p-3 border rounded">
                                    <h6>{{ $course->title }}</h6>
                                    <p class="text-muted">{{ $course->description }}</p>
                                    <span class="badge missouri-badge">${{ $course->price }}</span>
                                    <span class="badge bg-info">{{ $course->duration_hours }} hours</span>
                                    <span class="badge bg-secondary">Missouri Approved</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-car fa-3x text-success mb-3"></i>
                                <h5>Missouri Courses Coming Soon</h5>
                                <p class="text-muted">Missouri-specific driver improvement courses will be available here.</p>
                                <a href="/admin/missouri/courses" class="btn missouri-btn">Add Courses (Admin)</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-info-circle"></i> Missouri System Status</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>State:</strong> Missouri</p>
                        <p><strong>Controller:</strong> ✅ Active</p>
                        <p><strong>Database:</strong> ✅ Connected</p>
                        <p><strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                        <p><strong>Compliance:</strong> MO DOR Approved</p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-link"></i> Missouri Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('missouri.courses') }}" class="btn btn-outline-success btn-sm d-block mb-2">View Courses</a>
                        <a href="#" class="btn btn-outline-success btn-sm d-block mb-2">My Certificates</a>
                        <a href="/missouri/test" class="btn btn-outline-secondary btn-sm d-block mb-2">Test Route</a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm d-block">Admin Panel</a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6><i class="fas fa-gavel"></i> Missouri Requirements</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success"></i> 8-Hour Course</li>
                            <li><i class="fas fa-check text-success"></i> Final Exam</li>
                            <li><i class="fas fa-check text-success"></i> Certificate Issued</li>
                            <li><i class="fas fa-check text-success"></i> Court Reporting</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-map"></i> Other State Portals</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('florida.dashboard') }}" class="btn btn-primary me-2">Florida</a>
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