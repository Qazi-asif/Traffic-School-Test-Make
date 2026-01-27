<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Detail - Nevada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
    
    <div style="margin-left: 280px; padding: 2rem;">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Student Detail</h1>
                <a href="/admin/nevada/students" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </a>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Student Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</p>
                            <p><strong>Email:</strong> {{ $enrollment->user->email }}</p>
                            <p><strong>Phone:</strong> {{ $enrollment->user->phone ?? 'N/A' }}</p>
                            @if($enrollment->nevadaStudent)
                            <hr>
                            <p><strong>DMV Number:</strong> {{ $enrollment->nevadaStudent->nevada_dmv_number ?? 'N/A' }}</p>
                            <p><strong>Court Case:</strong> {{ $enrollment->nevadaStudent->court_case_number ?? 'N/A' }}</p>
                            <p><strong>Court Name:</strong> {{ $enrollment->nevadaStudent->court_name ?? 'N/A' }}</p>
                            <p><strong>Citation Date:</strong> {{ $enrollment->nevadaStudent->citation_date?->format('M d, Y') ?? 'N/A' }}</p>
                            <p><strong>Due Date:</strong> {{ $enrollment->nevadaStudent->due_date?->format('M d, Y') ?? 'N/A' }}</p>
                            <p><strong>Offense Code:</strong> {{ $enrollment->nevadaStudent->offense_code ?? 'N/A' }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Enrollment Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Course:</strong> {{ $enrollment->course->title }}</p>
                            <p><strong>Enrollment ID:</strong> {{ $enrollment->id }}</p>
                            <p><strong>Enrolled:</strong> {{ $enrollment->enrolled_at?->format('M d, Y H:i') }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($enrollment->status) }}</span></p>
                            <p><strong>Payment Status:</strong> <span class="badge bg-{{ $enrollment->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($enrollment->payment_status) }}</span></p>
                            <p><strong>Progress:</strong> {{ $enrollment->progress_percentage ?? 0 }}%</p>
                            @if($enrollment->completed_at)
                            <p><strong>Completed:</strong> {{ $enrollment->completed_at->format('M d, Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(count($validationErrors) > 0)
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Validation Issues</h5>
                <ul class="mb-0">
                    @foreach($validationErrors as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5>Activity Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Activity Type</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLog as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $log->log_type === 'completion' ? 'success' : 
                                            ($log->log_type === 'quiz_fail' ? 'danger' : 'info') 
                                        }}">
                                            {{ str_replace('_', ' ', ucfirst($log->log_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>
                                        @if($log->details)
                                            @if(isset($log->details['chapter_title']))
                                                Chapter: {{ $log->details['chapter_title'] }}
                                            @endif
                                            @if(isset($log->details['score']))
                                                Score: {{ $log->details['score'] }}%
                                            @endif
                                            @if(isset($log->details['time_spent']))
                                                Time: {{ round($log->details['time_spent'] / 60) }} min
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No activity recorded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
