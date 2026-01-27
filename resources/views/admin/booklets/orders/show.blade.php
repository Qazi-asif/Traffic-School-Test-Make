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
            <p class="text-muted">Order details and actions</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Order Details</h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Student</label>
                        <div class="fw-bold">{{ $order->enrollment->user->full_name }}</div>
                        <small class="text-muted">{{ $order->enrollment->user->email }}</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Course</label>
                        <div class="fw-bold">{{ $order->enrollment->course->title }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Booklet</label>
                        <div class="fw-bold">{{ $order->booklet->title }}</div>
                        <small class="text-muted">Version {{ $order->booklet->version }}</small>
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
                                @elseif($order->status === 'failed') bg-danger
                                @else bg-info
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Ordered</label>
                        <div class="fw-bold">{{ $order->created_at->format('M d, Y g:i A') }}</div>
                    </div>
                    @if($order->printed_at)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Printed</label>
                            <div class="fw-bold">{{ $order->printed_at->format('M d, Y g:i A') }}</div>
                        </div>
                    @endif
                    @if($order->shipped_at)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Shipped</label>
                            <div class="fw-bold">{{ $order->shipped_at->format('M d, Y g:i A') }}</div>
                        </div>
                    @endif
                    @if($order->tracking_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tracking Number</label>
                            <div class="fw-bold">{{ $order->tracking_number }}</div>
                        </div>
                    @endif
                </div>

                @if($order->notes)
                    <div class="mt-3">
                        <label class="text-muted small">Notes</label>
                        <div class="alert alert-info">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Actions</h5>
                
                <div class="d-flex flex-wrap gap-2">
                    @if($order->status === 'pending')
                        <form action="{{ route('admin.booklets.orders.generate', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-cog"></i> Generate Booklet
                            </button>
                        </form>
                    @endif

                    @if($order->status === 'ready' && $order->file_path)
                        <a href="{{ Storage::url($order->file_path) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Personalized PDF
                        </a>
                    @endif

                    @if(in_array($order->status, ['ready', 'printed']) && in_array($order->format, ['print_mail', 'print_pickup']))
                        @if($order->status === 'ready')
                            <form action="{{ route('admin.booklets.orders.mark-printed', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-print"></i> Mark as Printed
                                </button>
                            </form>
                        @endif

                        @if($order->status === 'printed' && $order->format === 'print_mail')
                            <form action="{{ route('admin.booklets.orders.mark-shipped', $order) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="text" name="tracking_number" placeholder="Tracking Number (optional)" class="form-control" style="max-width: 250px;">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-shipping-fast"></i> Mark as Shipped
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
