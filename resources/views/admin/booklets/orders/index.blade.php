<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booklet Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-shopping-bag"></i> Booklet Orders</h2>
            <p class="text-muted">Manage student booklet orders</p>
        </div>

        <div class="mb-3">
            <div class="btn-group">
                <a href="{{ route('admin.booklets.orders') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                    All Orders
                </a>
                <a href="{{ route('admin.booklets.orders.pending') }}" class="btn {{ request('status') === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Pending
                </a>
                <a href="{{ route('admin.booklets.orders', ['status' => 'ready']) }}" class="btn {{ request('status') === 'ready' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Ready
                </a>
                <a href="{{ route('admin.booklets.orders', ['status' => 'printed']) }}" class="btn {{ request('status') === 'printed' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Printed
                </a>
                <a href="{{ route('admin.booklets.orders', ['status' => 'shipped']) }}" class="btn {{ request('status') === 'shipped' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Shipped
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.booklets.orders.bulk-generate') }}" method="POST" id="bulkForm">
                    @csrf
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-cog"></i> Generate Selected
                            </button>
                            <button type="button" onclick="submitBulkAction('{{ route('admin.booklets.orders.bulk-print') }}')" class="btn btn-primary">
                                <i class="fas fa-print"></i> Mark as Printed
                            </button>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            <label for="selectAll" class="form-check-label">Select All</label>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" class="form-check-input">
                                    </th>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Format</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="form-check-input order-checkbox">
                                        </td>
                                        <td>{{ $order->enrollment->user->full_name }}</td>
                                        <td>{{ $order->enrollment->course->title }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->format)) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($order->status === 'ready') bg-success
                                                @elseif($order->status === 'pending') bg-warning
                                                @elseif($order->status === 'generating') bg-info
                                                @elseif($order->status === 'failed') bg-danger
                                                @elseif($order->status === 'printed') bg-primary
                                                @elseif($order->status === 'shipped') bg-dark
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.booklets.orders.view', $order) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function submitBulkAction(url) {
        const form = document.getElementById('bulkForm');
        form.action = url;
        form.submit();
    }
    </script>
</body>
</html>
