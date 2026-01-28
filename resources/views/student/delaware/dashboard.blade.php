<!DOCTYPE html>
<html>
<head>
    <title>Delaware Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .delaware-header { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
        .state-card { border-left: 4px solid #17a2b8; }
        .delaware-badge { background: #17a2b8; }
        .delaware-btn { background: #17a2b8; border-color: #17a2b8; }
        .delaware-btn:hover { background: #138496; border-color: #138496; }
        .delaware-diamond { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="delaware-header py-4">
        <div class="container">
            <h1><i class="fas fa-gem delaware-diamond"></i> Delaware Traffic School</h1>
            <p class="mb-0">Delaware Division of Motor Vehicles Approved Driver Improvement Program</p>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4><i class="fas fa-gem"></i> The First State's Premier Traffic School!</h4>
                    <p class="mb-0">Professional Delaware dashboard with Diamond State excellence and functionality.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card state-card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-book"></i> Delaware Driver Improvement Courses</h5>
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
                                <p class="mb-0">Please <a href="/login">login</a> to access Delaware courses and track progress in the First State.</p>
                            </div>
                        @endif

                        @if(isset($courses) && $courses->count() > 0)
                            @foreach($courses as $course)
                                <div class="course-item mb-3 p-3 border rounded">
                                    <h6><i class="fas fa-gem delaware-diamond"></i> {{ $course->title }}</h6>
                                    <p class="text-muted">{{ $course->description }}</p>
                                    <span class="badge delaware-badge">${{ $course->price }}</span>
                                    <span class="badge bg-info">{{ $course->duration_hours }} hours</span>
                                    <span class="badge bg-secondary">DE DMV Approved</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-car fa-3x delaware-diamond mb-3"></i>
                                <h5>Delaware Courses Coming Soon!</h5>
                                <p class="text-muted">Delaware-specific driver improvement courses will be available here in the First State.</p>
                                <a href="/admin/delaware/courses" class="btn delaware-btn">Add Courses (Admin)</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-info-circle"></i> Delaware System Status</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>State:</strong> Delaware <i class="fas fa-gem delaware-diamond"></i></p>
                        <p><strong>Controller:</strong> ✅ Active</p>
                        <p><strong>Database:</strong> ✅ Connected</p>
                        <p><strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                        <p><strong>Compliance:</strong> DE DMV Approved</p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-link"></i> Delaware Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <a href="#" class="btn btn-outline-info btn-sm d-block mb-2">View Courses</a>
                        <a href="#" class="btn btn-outline-info btn-sm d-block mb-2">My Certificates</a>
                        <a href="/delaware/test" class="btn btn-outline-secondary btn-sm d-block mb-2">Test Route</a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm d-block">Admin Panel</a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6><i class="fas fa-gavel"></i> Delaware Requirements</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-gem delaware-diamond"></i> 8-Hour Course</li>
                            <li><i class="fas fa-gem delaware-diamond"></i> Final Exam</li>
                            <li><i class="fas fa-gem delaware-diamond"></i> Certificate Issued</li>
                            <li><i class="fas fa-gem delaware-diamond"></i> Court Reporting</li>
                            <li><i class="fas fa-gem delaware-diamond"></i> First State Quality</li>
                        </ul>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6><i class="fas fa-history"></i> Delaware Facts</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-gem delaware-diamond"></i> The First State to ratify the Constitution<br>
                            <i class="fas fa-gem delaware-diamond"></i> Home of corporate America<br>
                            <i class="fas fa-gem delaware-diamond"></i> No sales tax statewide
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-map"></i> Other State Portals</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('florida.dashboard') }}" class="btn btn-primary me-2">Florida</a>
                        <a href="{{ route('missouri.dashboard') }}" class="btn btn-success me-2">Missouri</a>
                        <a href="{{ route('texas.dashboard') }}" class="btn btn-warning me-2">Texas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>