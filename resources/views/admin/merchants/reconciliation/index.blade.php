<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconciliation - {{ $account->account_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <a href="{{ route('admin.merchants.show', $account) }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left"></i> Back to {{ $account->account_name }}
            </a>
            <h2 class="mt-2"><i class="fas fa-balance-scale"></i> Reconciliation</h2>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus"></i> Create New Reconciliation</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.merchants.reconciliation.create', $account) }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label">Period Start</label>
                        <input type="date" name="period_start" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Period End</label>
                        <input type="date" name="period_end" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-play"></i> Run
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Reconciliation History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Expected Revenue</th>
                                <th>Actual Revenue</th>
                                <th>Expected Fees</th>
                                <th>Actual Fees</th>
                                <th>Discrepancy</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reconciliations as $reconciliation)
                            <tr>
                                <td>
                                    {{ $reconciliation->period_start->format('M d') }} - 
                                    {{ $reconciliation->period_end->format('M d, Y') }}
                                </td>
                                <td>${{ number_format($reconciliation->expected_revenue, 2) }}</td>
                                <td>${{ number_format($reconciliation->actual_revenue, 2) }}</td>
                                <td>${{ number_format($reconciliation->expected_fees, 2) }}</td>
                                <td>${{ number_format($reconciliation->actual_fees, 2) }}</td>
                                <td>
                                    <span class="text-{{ $reconciliation->discrepancy_amount != 0 ? 'danger' : 'success' }}">
                                        ${{ number_format(abs($reconciliation->discrepancy_amount), 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($reconciliation->status === 'matched')
                                    <span class="badge bg-success">Matched</span>
                                    @elseif($reconciliation->status === 'discrepancy')
                                    <span class="badge bg-danger">Discrepancy</span>
                                    @elseif($reconciliation->status === 'resolved')
                                    <span class="badge bg-info">Resolved</span>
                                    @else
                                    <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.merchants.reconciliation.show', $reconciliation) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No reconciliations yet. Create one above to get started.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $reconciliations->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
