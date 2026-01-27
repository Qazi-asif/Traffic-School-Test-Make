<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailed - In Transit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shipping-fast"></i> In Transit</h2>
            <a href="{{ route('admin.mail-court.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Court</th>
                                <th>Tracking</th>
                                <th>Carrier</th>
                                <th>Mailed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mailings as $mailing)
                            <tr>
                                <td>{{ $mailing->enrollment->user->name ?? 'N/A' }}</td>
                                <td>{{ $mailing->court->name ?? 'N/A' }}</td>
                                <td>{{ $mailing->tracking_number ?? 'N/A' }}</td>
                                <td>{{ strtoupper($mailing->carrier ?? 'N/A') }}</td>
                                <td>{{ $mailing->mailed_at?->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.mail-court.show', $mailing->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No mailings in transit</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $mailings->links() }}</div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
