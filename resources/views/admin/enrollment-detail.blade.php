<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-graduate"></i> Enrollment Details</h2>
            <a href="/admin/enrollments" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Enrollments
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user"></i> Student Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</p>
                        <p><strong>Email:</strong> {{ $enrollment->user->email }}</p>
                        <p><strong>Phone:</strong> {{ $enrollment->user->phone ?? 'N/A' }}</p>
                        <p><strong>Enrollment ID:</strong> {{ $enrollment->id }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-book"></i> Course Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Course:</strong> {{ $enrollment->floridaCourse->title ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge {{ $enrollment->status === 'completed' ? 'bg-success' : ($enrollment->status === 'active' ? 'bg-primary' : 'bg-secondary') }}">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </p>
                        <p><strong>Progress:</strong> {{ $enrollment->progress_percentage ?? 0 }}%</p>
                        <p><strong>Enrolled:</strong> {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M j, Y') : 'N/A' }}</p>
                        @if($enrollment->completed_at)
                            <p><strong>Completed:</strong> {{ $enrollment->completed_at->format('M j, Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-credit-card"></i> Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Payment Status:</strong> 
                            <span class="badge {{ $enrollment->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($enrollment->payment_status) }}
                            </span>
                        </p>
                        <p><strong>Amount Paid:</strong> ${{ number_format($enrollment->amount_paid ?? 0, 2) }}</p>
                        <p><strong>Payment Method:</strong> {{ $enrollment->payment_method ?? 'N/A' }}</p>
                        @if($enrollment->citation_number)
                            <p><strong>Citation Number:</strong> {{ $enrollment->citation_number }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Time Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Total Time Spent:</strong> {{ $enrollment->total_time_spent ?? 0 }} minutes</p>
                        @if($enrollment->court_date)
                            <p><strong>Court Date:</strong> {{ $enrollment->court_date->format('M j, Y') }}</p>
                        @endif
                        <p><strong>Last Updated:</strong> {{ $enrollment->updated_at->format('M j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($enrollment->progress->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list-check"></i> Chapter Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Chapter</th>
                                        <th>Status</th>
                                        <th>Time Spent</th>
                                        <th>Started</th>
                                        <th>Completed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollment->progress as $progress)
                                    <tr>
                                        <td>{{ $progress->chapter->title ?? 'Chapter ' . $progress->chapter_id }}</td>
                                        <td>
                                            @if($progress->is_completed)
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Completed</span>
                                            @else
                                                <span class="badge bg-warning"><i class="fas fa-clock"></i> In Progress</span>
                                            @endif
                                        </td>
                                        <td>{{ $progress->time_spent ?? 0 }} min</td>
                                        <td>{{ $progress->started_at ? $progress->started_at->format('M j, Y') : 'N/A' }}</td>
                                        <td>{{ $progress->completed_at ? $progress->completed_at->format('M j, Y') : 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="/course-player?enrollmentId={{ $enrollment->id }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-play"></i> View Course Player
                        </a>
                        @if($enrollment->status === 'completed')
                            <a href="/generate-certificates" class="btn btn-success">
                                <i class="fas fa-certificate"></i> Generate Certificate
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
</body>
</html>
