<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nevada Submissions - Admin</title>
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
            <h1 class="mb-4">Nevada Submissions</h1>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/nevada/submissions">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Certificate Number</th>
                                <th>Student</th>
                                <th>Method</th>
                                <th>Submission Date</th>
                                <th>Status</th>
                                <th>Confirmation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                            <tr>
                                <td>{{ $submission->id }}</td>
                                <td><strong>{{ $submission->certificate->nevada_certificate_number }}</strong></td>
                                <td>{{ $submission->certificate->enrollment->user->first_name }} {{ $submission->certificate->enrollment->user->last_name }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($submission->submission_method) }}
                                    </span>
                                </td>
                                <td>{{ $submission->submission_date->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $submission->status === 'confirmed' ? 'success' : 
                                        ($submission->status === 'failed' ? 'danger' : 
                                        ($submission->status === 'sent' ? 'info' : 'warning'))
                                    }}">
                                        {{ ucfirst($submission->status) }}
                                    </span>
                                </td>
                                <td>{{ $submission->confirmation_number ?? 'N/A' }}</td>
                                <td>
                                    <a href="/admin/nevada/submissions/{{ $submission->id }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if($submission->status === 'failed')
                                    <button class="btn btn-sm btn-warning" onclick="retrySubmission({{ $submission->id }})">
                                        <i class="fas fa-redo"></i> Retry
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No submissions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $submissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function retrySubmission(id) {
            if (!confirm('Retry this submission?')) return;

            fetch(`/admin/nevada/submissions/${id}/retry`, {
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
