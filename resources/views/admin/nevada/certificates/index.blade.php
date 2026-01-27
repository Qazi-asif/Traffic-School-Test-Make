<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nevada Certificates - Admin</title>
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
            <h1 class="mb-4">Nevada Certificates</h1>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/nevada/certificates">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                <th>Certificate Number</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Completion Date</th>
                                <th>Status</th>
                                <th>Submission Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($certificates as $cert)
                            <tr>
                                <td><strong>{{ $cert->nevada_certificate_number }}</strong></td>
                                <td>{{ $cert->enrollment->user->first_name }} {{ $cert->enrollment->user->last_name }}</td>
                                <td>{{ $cert->enrollment->course->title }}</td>
                                <td>{{ $cert->completion_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $cert->submission_status === 'accepted' ? 'success' : 
                                        ($cert->submission_status === 'pending' ? 'warning' : 
                                        ($cert->submission_status === 'rejected' ? 'danger' : 'info'))
                                    }}">
                                        {{ ucfirst($cert->submission_status) }}
                                    </span>
                                </td>
                                <td>{{ $cert->submission_date?->format('M d, Y H:i') ?? 'Not submitted' }}</td>
                                <td>
                                    @if($cert->submission_status === 'pending')
                                    <button class="btn btn-sm btn-success" onclick="submitCertificate({{ $cert->id }})">
                                        <i class="fas fa-paper-plane"></i> Submit
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No certificates found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $certificates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function submitCertificate(id) {
            if (!confirm('Submit this certificate to Nevada state?')) return;

            fetch(`/admin/nevada/certificates/${id}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Certificate submitted successfully!');
                    location.reload();
                } else {
                    alert('Submission failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>
