<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subscriber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit"></i> Edit Subscriber</h2>
            <a href="{{ route('admin.newsletter.subscribers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.newsletter.subscribers.update', $subscriber) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $subscriber->email) }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $subscriber->first_name) }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $subscriber->last_name) }}" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <select name="state_code" class="form-select">
                                <option value="">Select State</option>
                                <option value="FL" {{ $subscriber->state_code === 'FL' ? 'selected' : '' }}>Florida</option>
                                <option value="CA" {{ $subscriber->state_code === 'CA' ? 'selected' : '' }}>California</option>
                                <option value="TX" {{ $subscriber->state_code === 'TX' ? 'selected' : '' }}>Texas</option>
                                <option value="MO" {{ $subscriber->state_code === 'MO' ? 'selected' : '' }}>Missouri</option>
                                <option value="DE" {{ $subscriber->state_code === 'DE' ? 'selected' : '' }}>Delaware</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" value="1" {{ $subscriber->is_active ? 'checked' : '' }} class="form-check-input" id="isActive">
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Subscriber Info:</strong><br>
                        Source: {{ ucfirst($subscriber->source) }}<br>
                        Subscribed: {{ $subscriber->subscribed_at->format('M d, Y H:i') }}<br>
                        @if($subscriber->confirmed_at)
                            Confirmed: {{ $subscriber->confirmed_at->format('M d, Y H:i') }}
                        @else
                            Status: Not confirmed
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Subscriber
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
