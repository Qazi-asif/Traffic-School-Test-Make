<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconciliation Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <a href="{{ route('admin.merchants.reconciliation', $reconciliation->merchantAccount) }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left"></i> Back to Reconciliation
            </a>
            <h2 class="mt-2"><i class="fas fa-balance-scale"></i> Reconciliation Details</h2>
            <p class="text-muted">
                {{ $reconciliation->period_start->format('M d, Y') }} - {{ $reconciliation->period_end->format('M d, Y') }}
            </p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Expected (System)</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Revenue:</span>
                            <strong>${{ number_format($reconciliation->expected_revenue, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Fees:</span>
                            <strong>${{ number_format($reconciliation->expected_fees, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Actual (Gateway)</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Revenue:</span>
                            <strong>${{ number_format($reconciliation->actual_revenue, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Fees:</span>
                            <strong>${{ number_format($reconciliation->actual_fees, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-{{ $reconciliation->discrepancy_amount != 0 ? 'danger' : 'success' }} text-white">
                <h5 class="mb-0">
                    <i class="fas fa-{{ $reconciliation->discrepancy_amount != 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
                    {{ $reconciliation->discrepancy_amount != 0 ? 'Discrepancy Found' : 'Matched' }}
                </h5>
            </div>
            <div class="card-body">
                <h3 class="text-{{ $reconciliation->discrepancy_amount != 0 ? 'danger' : 'success' }}">
                    ${{ number_format(abs($reconciliation->discrepancy_amount), 2) }}
                </h3>
                <p class="text-muted mb-0">
                    @if($reconciliation->discrepancy_amount > 0)
                    System shows more revenue than gateway
                    @elseif($reconciliation->discrepancy_amount < 0)
                    Gateway shows more revenue than system
                    @else
                    All amounts match perfectly
                    @endif
                </p>
            </div>
        </div>

        @if($reconciliation->notes)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $reconciliation->notes }}</p>
                @if($reconciliation->reconciledBy)
                <small class="text-muted">
                    Resolved by {{ $reconciliation->reconciledBy->name }} on {{ $reconciliation->reconciled_at->format('M d, Y H:i') }}
                </small>
                @endif
            </div>
        </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-{{ $reconciliation->status === 'matched' ? 'success' : ($reconciliation->status === 'resolved' ? 'info' : 'warning') }} fs-5">
                            {{ ucfirst($reconciliation->status) }}
                        </span>
                    </div>
                    @if($reconciliation->status === 'discrepancy')
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#resolveModal">
                        <i class="fas fa-check"></i> Mark as Resolved
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resolve Modal -->
    <div class="modal fade" id="resolveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Discrepancy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.merchants.reconciliation.resolve', $reconciliation) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Resolution Notes</label>
                            <textarea name="notes" class="form-control" rows="4" required placeholder="Explain how this discrepancy was resolved..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Mark as Resolved</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
