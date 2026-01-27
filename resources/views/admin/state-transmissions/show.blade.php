<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>State Transmission Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }

        .status-pending { background-color: #ffc107 !important; color: #000 !important; }
        .status-success { background-color: #516425 !important; }
        .status-error { background-color: #dc3545 !important; }

        .system-flhsmv { background-color: #ff6b35 !important; }
        .system-tvcc { background-color: #4ecdc4 !important; }
        .system-ntsa { background-color: #45b7d1 !important; }
        .system-ccs { background-color: #96ceb4 !important; }
        .system-ctsi { background-color: #feca57 !important; color: #000 !important; }

        .info-label {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-value {
            margin-bottom: 1rem;
        }

        .json-container {
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4a90e2;
            border: 2px solid #fff;
        }

        .timeline-success::before { background: #516425 !important; }
        .timeline-error::before { background: #dc3545 !important; }
        .timeline-warning::before { background: #ffc107 !important; }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-file-alt me-2"></i>Transmission Details</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.state-transmissions.index') }}" class="text-decoration-none">
                                All State Transmissions
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Transmission #{{ $transmission->id }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                @if($transmission->status === 'pending')
                    <form method="POST" action="{{ route('admin.state-transmissions.send', $transmission->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Send Now
                        </button>
                    </form>
                @endif
                
                @if($transmission->status === 'error')
                    <form method="POST" action="{{ route('admin.state-transmissions.retry', $transmission->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-redo me-1"></i> Retry
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-label">Transmission ID</div>
                        <div class="info-value">#{{ $transmission->id }}</div>

                        <div class="info-label">State</div>
                        <div class="info-value">
                            <span class="badge bg-info">{{ $transmission->state }}</span>
                        </div>

                        <div class="info-label">System</div>
                        <div class="info-value">
                            <span class="badge system-{{ strtolower($transmission->system) }}">
                                {{ $transmission->system }}
                            </span>
                        </div>

                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge status-{{ $transmission->status }}">
                                {{ ucfirst($transmission->status) }}
                            </span>
                        </div>

                        <div class="info-label">Retry Count</div>
                        <div class="info-value">
                            <span class="badge bg-secondary">{{ $transmission->retry_count }}</span>
                        </div>

                        <div class="info-label">Created</div>
                        <div class="info-value">{{ $transmission->created_at->format('F j, Y g:i A') }}</div>

                        @if($transmission->sent_at)
                            <div class="info-label">Sent</div>
                            <div class="info-value">{{ $transmission->sent_at->format('F j, Y g:i A') }}</div>
                        @endif

                        @if($transmission->response_code)
                            <div class="info-label">Response Code</div>
                            <div class="info-value">
                                <code>{{ $transmission->response_code }}</code>
                            </div>
                        @endif

                        @if($transmission->response_message)
                            <div class="info-label">Response Message</div>
                            <div class="info-value">
                                <div class="alert alert-{{ $transmission->status === 'success' ? 'success' : 'danger' }} mb-0">
                                    {{ $transmission->response_message }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Student & Course Information -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student & Course</h5>
                    </div>
                    <div class="card-body">
                        @if($transmission->enrollment && $transmission->enrollment->user)
                            <div class="info-label">Student Name</div>
                            <div class="info-value">
                                <strong>{{ $transmission->enrollment->user->first_name }} {{ $transmission->enrollment->user->last_name }}</strong>
                            </div>

                            <div class="info-label">Email</div>
                            <div class="info-value">
                                <a href="mailto:{{ $transmission->enrollment->user->email }}" class="text-decoration-none">
                                    {{ $transmission->enrollment->user->email }}
                                </a>
                            </div>

                            <div class="info-label">Enrollment ID</div>
                            <div class="info-value">
                                <a href="{{ route('admin.enrollments.show', $transmission->enrollment->id) }}" class="text-decoration-none">
                                    #{{ $transmission->enrollment->id }}
                                </a>
                            </div>

                            @if($transmission->enrollment->course)
                                <div class="info-label">Course</div>
                                <div class="info-value">{{ $transmission->enrollment->course->title }}</div>

                                <div class="info-label">Course State</div>
                                <div class="info-value">
                                    <span class="badge bg-info">{{ $transmission->enrollment->course->state_code ?? 'N/A' }}</span>
                                </div>
                            @endif

                            @if($transmission->enrollment->completed_at)
                                <div class="info-label">Course Completed</div>
                                <div class="info-value">{{ $transmission->enrollment->completed_at->format('F j, Y g:i A') }}</div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Enrollment or user information not available.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Transmission Payload -->
        @if($transmission->payload_json)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-code me-2"></i>Transmission Payload</h5>
                </div>
                <div class="card-body">
                    <div class="json-container">
                        <pre>{{ is_string($transmission->payload_json) ? json_encode(json_decode($transmission->payload_json), JSON_PRETTY_PRINT) : json_encode($transmission->payload_json, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        @endif

        <!-- Transmission Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Transmission Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Transmission Created</h6>
                                <p class="text-muted mb-0">Initial transmission record created</p>
                            </div>
                            <small class="text-muted">{{ $transmission->created_at->format('M j, Y g:i A') }}</small>
                        </div>
                    </div>

                    @if($transmission->sent_at)
                        <div class="timeline-item timeline-{{ $transmission->status === 'success' ? 'success' : 'error' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Transmission {{ $transmission->status === 'success' ? 'Successful' : 'Failed' }}</h6>
                                    <p class="text-muted mb-0">
                                        @if($transmission->response_message)
                                            {{ $transmission->response_message }}
                                        @else
                                            Transmission {{ $transmission->status === 'success' ? 'completed successfully' : 'failed' }}
                                        @endif
                                    </p>
                                </div>
                                <small class="text-muted">{{ $transmission->sent_at->format('M j, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif

                    @if($transmission->retry_count > 0)
                        <div class="timeline-item timeline-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Retries Attempted</h6>
                                    <p class="text-muted mb-0">{{ $transmission->retry_count }} retry attempts made</p>
                                </div>
                                <small class="text-muted">{{ $transmission->updated_at->format('M j, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif

                    @if($transmission->status === 'pending')
                        <div class="timeline-item timeline-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Pending Transmission</h6>
                                    <p class="text-muted mb-0">Waiting to be processed</p>
                                </div>
                                <small class="text-muted">Now</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>