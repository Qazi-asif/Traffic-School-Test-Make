<!DOCTYPE html>
<html>
<head>
    <title>Texas Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .texas-header { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
        .state-card { border-left: 4px solid #ffc107; }
        .texas-badge { background: #ffc107; color: #212529; }
        .texas-btn { background: #ffc107; border-color: #ffc107; color: #212529; }
        .texas-btn:hover { background: #e0a800; border-color: #e0a800; color: #212529; }
        .texas-star { color: #ffc107; }
    </style>
</head>
<body>
    <div class="texas-header py-4">
        <div class="container">
            <h1><i class="fas fa-star texas-star"></i> Texas Traffic School <i class="fas fa-star texas-star"></i></h1>
            <p class="mb-0">Texas Department of Licensing and Regulation Approved Defensive Driving Course</p>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="alert" style="background: #fff3cd; border-color: #ffc107; color: #856404;">
                    <h4><i class="fas fa-star"></i> Everything's Bigger in Texas - Including Our Courses!</h4>
                    <p class="mb-0">Professional Texas dashboard with Lone Star State branding and functionality.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card state-card">
                    <div class="card-header" style="background: #ffc107; color: #212529;">
                        <h5><i class="fas fa-book"></i> Texas Defensive Driving Courses</h5>
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
                                <h6><i class="fas fa-info-circle"></i> Y'all Need to Login!</h6>
                                <p class="mb-0">Please <a href="/login">login</a> to access Texas courses and track your progress, partner!</p>
                            </div>
                        @endif

                        @if(isset($courses) && $courses->count() > 0)
                            @foreach($courses as $course)
                                <div class="course-item mb-3 p-3 border rounded">
                                    <h6><i class="fas fa-star texas-star"></i> {{ $course->title }}</h6>
                                    <p class="text-muted">{{ $course->description }}</p>
                                    <span class="badge texas-badge">${{ $course->price }}</span>
                                    <span class="badge bg-info">{{ $course->duration_hours }} hours</span>
                                    <span class="badge bg-secondary">TDLR Approved</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-car fa-3x texas-star mb-3"></i>
                                <h5>Texas Courses Coming Soon, Y'all!</h5>
                                <p class="text-muted">Texas-specific defensive driving courses will be available here faster than a jackrabbit.</p>
                                <a href="/admin/texas/courses" class="btn texas-btn">Add Courses (Admin)</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background: #ffc107; color: #212529;">
                        <h6><i class="fas fa-info-circle"></i> Texas System Status</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>State:</strong> Texas <i class="fas fa-star texas-star"></i></p>
                        <p><strong>Controller:</strong> ✅ Active</p>
                        <p><strong>Database:</strong> ✅ Connected</p>
                        <p><strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                        <p><strong>Compliance:</strong> TDLR Approved</p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header" style="background: #ffc107; color: #212529;">
                        <h6><i class="fas fa-link"></i> Texas Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <a href="#" class="btn btn-outline-warning btn-sm d-block mb-2">View Courses</a>
                        <a href="#" class="btn btn-outline-warning btn-sm d-block mb-2">My Certificates</a>
                        <a href="/texas/test" class="btn btn-outline-secondary btn-sm d-block mb-2">Test Route</a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm d-block">Admin Panel</a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6><i class="fas fa-gavel"></i> Texas Requirements</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-star texas-star"></i> 6-Hour Course</li>
                            <li><i class="fas fa-star texas-star"></i> Final Exam</li>
                            <li><i class="fas fa-star texas-star"></i> Certificate Issued</li>
                            <li><i class="fas fa-star texas-star"></i> Court Reporting</li>
                            <li><i class="fas fa-star texas-star"></i> TDLR Compliance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background: #ffc107; color: #212529;">
                        <h6><i class="fas fa-map"></i> Other State Portals</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('florida.dashboard') }}" class="btn btn-primary me-2">Florida</a>
                        <a href="{{ route('missouri.dashboard') }}" class="btn btn-success me-2">Missouri</a>
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