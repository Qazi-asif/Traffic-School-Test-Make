<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $gateway->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <a href="{{ route('admin.payment-gateways.show', $gateway) }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left"></i> Back to {{ $gateway->name }}
            </a>
            <h2 class="mt-2"><i class="fas fa-edit"></i> Edit Gateway Settings</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.payment-gateways.update', $gateway) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" value="{{ old('display_name', $gateway->display_name) }}"
                                   class="form-control @error('display_name') is-invalid @enderror" required>
                            @error('display_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" value="{{ old('display_order', $gateway->display_order) }}"
                                   class="form-control @error('display_order') is-invalid @enderror" min="0">
                            @error('display_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $gateway->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Amount ($)</label>
                            <input type="number" step="0.01" name="min_amount" value="{{ old('min_amount', $gateway->min_amount) }}"
                                   class="form-control @error('min_amount') is-invalid @enderror" min="0">
                            @error('min_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Amount ($)</label>
                            <input type="number" step="0.01" name="max_amount" value="{{ old('max_amount', $gateway->max_amount) }}"
                                   class="form-control @error('max_amount') is-invalid @enderror" min="0">
                            @error('max_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transaction Fee (%)</label>
                            <input type="number" step="0.01" name="transaction_fee_percent" 
                                   value="{{ old('transaction_fee_percent', $gateway->transaction_fee_percent) }}"
                                   class="form-control @error('transaction_fee_percent') is-invalid @enderror" min="0" max="100">
                            @error('transaction_fee_percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fixed Fee ($)</label>
                            <input type="number" step="0.01" name="transaction_fee_fixed" 
                                   value="{{ old('transaction_fee_fixed', $gateway->transaction_fee_fixed) }}"
                                   class="form-control @error('transaction_fee_fixed') is-invalid @enderror" min="0">
                            @error('transaction_fee_fixed')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.payment-gateways.show', $gateway) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
