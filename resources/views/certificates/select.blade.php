<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Certificate - Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="row">
            <div class="col-md-12">
                <h1><i class="fas fa-certificate"></i> Select Certificate to Generate</h1>
                <p class="text-muted">Choose a course to generate your certificate</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Your Enrolled Courses</h5>
                    </div>
                    <div class="card-body">
                        @if($enrollments->count() > 0)
                            @foreach($enrollments as $enrollment)
                            <div class="border rounded p-3 mb-3 {{ $enrollment->access_revoked ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">
                                            {{ $enrollment->course?->title }}
                                            @if($enrollment->access_revoked)
                                                <span class="badge bg-danger ms-2">Access Revoked</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            <strong>Enrolled:</strong> {{ $enrollment->created_at->format('M d, Y') }}
                                            @if($enrollment->completed_at)
                                                <br><strong>Completed:</strong> {{ $enrollment->formatted_completed_at ?? $enrollment->completed_at }}
                                            @endif
                                            <br><strong>Status:</strong> {{ ucfirst($enrollment->status ?? 'enrolled') }}
                                            @if($enrollment->access_revoked)
                                                <br><strong class="text-danger">Certificate Downloaded:</strong> 
                                                {{ $enrollment->formatted_access_revoked_at ?? ($enrollment->access_revoked_at ? 'Yes' : 'Yes') }}
                                            @endif
                                        </small>
                                    </div>
                                    <div>
                                        @if($enrollment->access_revoked)
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-check me-2"></i>Already Downloaded
                                            </button>
                                        @else
                                            <a href="{{ url('/generate-certificate/' . $enrollment->id) }}" 
                                               class="btn btn-primary">
                                                <i class="fas fa-certificate me-2"></i>Generate Certificate
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                <h5>No Enrolled Courses</h5>
                                <p class="text-muted">You haven't enrolled in any courses yet.</p>
                                <a href="{{ url('/courses') }}" class="btn btn-primary">Browse Courses</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12 text-center">
                <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
