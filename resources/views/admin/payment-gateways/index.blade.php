<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateways</title>
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
                <h2><i class="fas fa-credit-card"></i> Payment Gateways</h2>
                <p class="text-muted">Manage payment gateway configurations and settings</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Mode</th>
                                <th>Transaction Fees</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gateways as $gateway)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($gateway->icon)
                                        <img src="{{ $gateway->icon }}" alt="{{ $gateway->name }}" class="me-3" style="height: 32px; width: 32px;">
                                        @else
                                        <div class="bg-secondary bg-opacity-10 p-2 rounded me-3">
                                            <i class="fas fa-credit-card fa-lg text-secondary"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $gateway->display_name }}</div>
                                            <small class="text-muted">{{ $gateway->code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($gateway->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle"></i> Inactive
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($gateway->is_test_mode)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-flask"></i> Test Mode
                                    </span>
                                    @else
                                    <span class="badge bg-primary">
                                        <i class="fas fa-globe"></i> Production
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($gateway->transaction_fee_percent || $gateway->transaction_fee_fixed)
                                    <span class="text-muted">{{ $gateway->transaction_fee_percent }}% + ${{ number_format($gateway->transaction_fee_fixed, 2) }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.payment-gateways.show', $gateway) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-cog"></i> Configure
                                        </a>
                                        @if($gateway->is_active)
                                        <form action="{{ route('admin.payment-gateways.deactivate', $gateway) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Deactivate this gateway?')">
                                                <i class="fas fa-power-off"></i> Deactivate
                                            </button>
                                        </form>
                                        @else
                                        <form action="{{ route('admin.payment-gateways.activate', $gateway) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success">
                                                <i class="fas fa-check"></i> Activate
                                            </button>
                                        </form>
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

        <div class="alert alert-info mt-4">
            <h5 class="alert-heading"><i class="fas fa-shield-alt"></i> Security Notice</h5>
            <p class="mb-0">
                API keys and secrets are encrypted in the database. Always use test mode keys during development.
                Ensure you have proper SSL certificates before enabling production mode.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
