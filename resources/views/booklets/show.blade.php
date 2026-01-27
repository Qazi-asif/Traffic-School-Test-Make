<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booklet Order #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-file-alt"></i> Booklet Order #{{ $order->id }}</h2>
            <p class="text-muted">Order status and download</p>
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

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Order Details</h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Course</label>
                        <div class="fw-bold">{{ $order->enrollment->course->title ?? 'Course Not Found' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Booklet</label>
                        <div class="fw-bold">{{ $order->booklet->title }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Format</label>
                        <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $order->format)) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            <span class="badge 
                                @if($order->status === 'ready') bg-success
                                @elseif($order->status === 'pending') bg-warning
                                @elseif($order->status === 'generating') bg-info
                                @elseif($order->status === 'failed') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Ordered</label>
                        <div class="fw-bold">{{ $order->created_at->format('M d, Y g:i A') }}</div>
                    </div>
                    @if($order->tracking_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tracking Number</label>
                            <div class="fw-bold">{{ $order->tracking_number }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($order->status === 'pending' || $order->status === 'generating')
            <div class="alert alert-info">
                <i class="fas fa-spinner fa-spin"></i> <strong>Processing:</strong> Your booklet is being generated. This may take a few minutes. Please refresh this page to check the status.
            </div>
        @endif

        @if($order->status === 'ready' && $order->format === 'pdf_download')
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Download Your Booklet</h5>
                    <p class="text-muted mb-4">Your personalized course booklet is ready for download.</p>
                    <a href="{{ route('booklets.download', $order) }}" class="btn btn-success btn-lg">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
            </div>
        @endif

        @if($order->status === 'printed' || $order->status === 'shipped')
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Shipping Information</h5>
                    @if($order->status === 'printed')
                        <p class="text-muted">Your booklet has been printed and will be shipped soon.</p>
                    @else
                        <p class="text-muted mb-2">Your booklet has been shipped!</p>
                        @if($order->tracking_number)
                            <p>Tracking Number: <strong>{{ $order->tracking_number }}</strong></p>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        @if($order->status === 'failed')
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> There was a problem generating your booklet. Please contact support for assistance.
                @if($order->notes)
                    <p class="mb-0 mt-2 small">{{ $order->notes }}</p>
                @endif
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('booklets.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to My Booklets
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
