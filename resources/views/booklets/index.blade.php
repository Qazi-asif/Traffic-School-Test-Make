<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Course Booklets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-book-open"></i> My Course Booklets</h2>
            <p class="text-muted">View and download your ordered course booklets</p>
        </div>

        @if($orders->count() > 0)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Format</th>
                                    <th>Status</th>
                                    <th>Ordered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->enrollment->course->title ?? 'Course Not Found' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->format)) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($order->status === 'ready') bg-success
                                                @elseif($order->status === 'pending') bg-warning
                                                @elseif($order->status === 'generating') bg-info
                                                @elseif($order->status === 'failed') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('booklets.show', $order) }}" class="btn btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if($order->isDownloadable())
                                                    <a href="{{ route('booklets.download', $order) }}" class="btn btn-success">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-3">You haven't ordered any course booklets yet.</p>
                    <a href="{{ route('my-enrollments') }}" class="btn btn-primary">
                        <i class="fas fa-graduation-cap"></i> View My Enrollments
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
