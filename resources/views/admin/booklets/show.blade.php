<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $booklet->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-book"></i> {{ $booklet->title }}</h2>
                <p class="text-muted">Booklet Details</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.booklets.edit', $booklet) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.booklets.regenerate', $booklet) }}" method="POST" class="d-inline" onsubmit="return confirm('Regenerate this booklet?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sync"></i> Regenerate PDF
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Booklet Details</h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Course</label>
                        <div class="fw-bold">{{ $booklet->course->title ?? 'Course Not Found' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Version</label>
                        <div class="fw-bold">{{ $booklet->version }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">State</label>
                        <div class="fw-bold">{{ $booklet->state_code ?? 'All States' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            @if($booklet->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Pages</label>
                        <div class="fw-bold">{{ $booklet->page_count }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">File Size</label>
                        <div class="fw-bold">{{ $booklet->getFileSizeFormatted() }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Created By</label>
                        <div class="fw-bold">{{ $booklet->creator->full_name ?? 'System' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Created At</label>
                        <div class="fw-bold">{{ $booklet->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.booklets.preview', $booklet) }}" target="_blank" class="btn btn-success me-2">
                        <i class="fas fa-eye"></i> Preview PDF
                    </a>
                    <a href="{{ route('admin.booklets.download', $booklet) }}" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Recent Orders</h5>
                
                @if($booklet->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Format</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booklet->orders->take(10) as $order)
                                    <tr>
                                        <td>{{ $order->enrollment->user->full_name }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->format)) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($order->status === 'ready') bg-success
                                                @elseif($order->status === 'pending') bg-warning
                                                @elseif($order->status === 'failed') bg-danger
                                                @else bg-info
                                                @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No orders yet</p>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
