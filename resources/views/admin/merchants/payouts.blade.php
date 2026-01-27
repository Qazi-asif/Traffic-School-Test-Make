<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payouts - {{ $account->account_name }}</title>
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
            <h2 class="mt-2"><i class="fas fa-money-bill-wave"></i> Payouts</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Initiated</th>
                                <th>Expected Arrival</th>
                                <th>Arrived</th>
                                <th>Bank Account</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td><strong>{{ $payout->payout_reference }}</strong></td>
                                <td>${{ number_format($payout->amount, 2) }}</td>
                                <td>
                                    @if($payout->status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                    @elseif($payout->status === 'in_transit')
                                    <span class="badge bg-info">In Transit</span>
                                    @elseif($payout->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                    @else
                                    <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $payout->initiated_at->format('M d, Y') }}</td>
                                <td>{{ $payout->expected_arrival_at?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $payout->arrived_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    @if($payout->bank_account_last4)
                                    <span class="text-muted">****{{ $payout->bank_account_last4 }}</span>
                                    @else
                                    —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No payouts yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $payouts->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
