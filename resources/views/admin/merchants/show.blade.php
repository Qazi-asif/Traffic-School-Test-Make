<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $account->account_name }} - Merchant Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <a href="{{ route('admin.merchants.index') }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left"></i> Back to Merchant Accounts
            </a>
            <h2 class="mt-2"><i class="fas fa-building"></i> {{ $account->account_name }}</h2>
            <p class="text-muted">{{ $account->gateway->display_name }} • {{ $account->account_identifier }}</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Current Balance</h6>
                        <h3 class="text-success mb-0">${{ number_format($balance, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Pending Payout</h6>
                        <h3 class="text-warning mb-0">${{ number_format($pendingPayout, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">This Month Revenue</h6>
                        <h3 class="text-primary mb-0">${{ number_format($summary['total_charges'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">This Month Fees</h6>
                        <h3 class="text-danger mb-0">${{ number_format($summary['total_fees'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Recent Transactions</h5>
                        <a href="{{ route('admin.merchants.transactions', $account) }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Net</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($account->transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->processed_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->transaction_type === 'charge' ? 'success' : 'danger' }}">
                                                {{ ucfirst($transaction->transaction_type) }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($transaction->gross_amount, 2) }}</td>
                                        <td>${{ number_format($transaction->fee_amount, 2) }}</td>
                                        <td><strong>${{ number_format($transaction->net_amount, 2) }}</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No transactions yet</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Recent Payouts</h5>
                    </div>
                    <div class="card-body">
                        @forelse($recentPayouts as $payout)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-bold">${{ number_format($payout->amount, 2) }}</div>
                                <small class="text-muted">{{ $payout->initiated_at->format('M d, Y') }}</small>
                            </div>
                            <span class="badge bg-{{ $payout->status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-muted text-center">No payouts yet</p>
                        @endforelse
                        <a href="{{ route('admin.merchants.payouts', $account) }}" class="btn btn-sm btn-outline-primary w-100">
                            View All Payouts
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.merchants.sync', $account) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-sync"></i> Sync with Gateway
                                </button>
                            </form>
                            <a href="{{ route('admin.merchants.reconciliation', $account) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-balance-scale"></i> Reconciliation
                            </a>
                            <a href="{{ route('admin.merchants.transactions', $account) }}" class="btn btn-outline-info">
                                <i class="fas fa-list"></i> All Transactions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
