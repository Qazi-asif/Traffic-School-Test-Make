<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Content Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book-open"></i> Course Content Management</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            @forelse($courses as $course)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $course->title }}</h5>
                            <span class="badge bg-primary">{{ $course->state_code }}</span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">{{ Str::limit($course->description, 100) }}</p>
                            
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number">{{ $course->chapters->count() }}</div>
                                        <div class="stat-label">Chapters</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number">{{ $course->total_duration }}</div>
                                        <div class="stat-label">Minutes</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number">{{ $course->chapters->where('is_quiz', true)->count() }}</div>
                                        <div class="stat-label">Quizzes</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <a href="{{ route('admin.course-content.show', $course) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Manage Content
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h4>No Courses Found</h4>
                        <p class="text-muted">Create courses first to manage their content.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <x-footer />
    
    <style>
        .stat-item {
            padding: 0.5rem;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>