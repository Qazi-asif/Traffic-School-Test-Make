<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submission Detail - Nevada</title>
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
                <h1>Submission Detail</h1>
                <a href="/admin/nevada/submissions" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Submissions
                </a>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Submission Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Submission ID:</strong> {{ $submission->id }}</p>
                            <p><strong>Method:</strong> <span class="badge bg-secondary">{{ ucfirst($submission->submission_method) }}</span></p>
                            <p><strong>Submission Date:</strong> {{ $submission->submission_date->format('M d, Y H:i:s') }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ 
                                    $submission->status === 'confirmed' ? 'success' : 
                                    ($submission->status === 'failed' ? 'danger' : 
                                    ($submission->status === 'sent' ? 'info' : 'warning'))
                                }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </p>
                            <p><strong>Confirmation Number:</strong> {{ $submission->confirmation_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Certificate Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Certificate Number:</strong> {{ $submission->certificate->nevada_certificate_number }}</p>
                            <p><strong>Student:</strong> {{ $submission->certificate->enrollment->user->first_name }} {{ $submission->certificate->enrollment->user->last_name }}</p>
                            <p><strong>Email:</strong> {{ $submission->certificate->enrollment->user->email }}</p>
                            <p><strong>Course:</strong> {{ $submission->certificate->enrollment->course->title }}</p>
                            <p><strong>Completion Date:</strong> {{ $submission->certificate->completion_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($submission->response_data)
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Response Data</h5>
                </div>
                <div class="card-body">
                    <pre>{{ json_encode($submission->response_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            @if($submission->error_message)
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-circle"></i> Error Message</h5>
                <p class="mb-0">{{ $submission->error_message }}</p>
            </div>
            @endif

            @if($submission->status === 'failed')
            <div class="text-center">
                <button class="btn btn-warning btn-lg" onclick="retrySubmission()">
                    <i class="fas fa-redo"></i> Retry Submission
                </button>
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function retrySubmission() {
            if (!confirm('Retry this submission?')) return;

            fetch(`/admin/nevada/submissions/{{ $submission->id }}/retry`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Submission retried successfully!');
                    location.reload();
                } else {
                    alert('Retry failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>
