<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Mailings</title>
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
                <h2><i class="fas fa-clock"></i> Pending Mailings Queue</h2>
                <p class="text-muted">Certificates and documents waiting to be printed and mailed</p>
            </div>
            <a href="{{ route('admin.mail-court.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <select name="state" class="form-select">
                            <option value="">All States</option>
                            <option value="FL" {{ ($filters['state'] ?? '') == 'FL' ? 'selected' : '' }}>Florida</option>
                            <option value="MO" {{ ($filters['state'] ?? '') == 'MO' ? 'selected' : '' }}>Missouri</option>
                            <option value="TX" {{ ($filters['state'] ?? '') == 'TX' ? 'selected' : '' }}>Texas</option>
                            <option value="DE" {{ ($filters['state'] ?? '') == 'DE' ? 'selected' : '' }}>Delaware</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.mail-court.pending') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.mail-court.bulk-print') }}" method="POST" id="bulk-form">
                    @csrf
                    <input type="hidden" name="mailing_ids" id="selected-ids">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-print"></i> Print Selected
                        </button>
                        <button type="button" class="btn btn-info" onclick="createBatch()">
                            <i class="fas fa-layer-group"></i> Add to Batch
                        </button>
                    </div>
                    <span class="ms-3 text-muted" id="selected-count">0 selected</span>
                </form>
            </div>
        </div>

        <!-- Mailings Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Student</th>
                                <th>Court</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th>Created</th>
                                <th>Days Pending</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mailings as $mailing)
                            <tr>
                                <td><input type="checkbox" class="mailing-checkbox" value="{{ $mailing->id }}"></td>
                                <td>
                                    <strong>{{ $mailing->enrollment->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $mailing->enrollment->user->email ?? '' }}</small>
                                </td>
                                <td>{{ $mailing->court->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($mailing->mailing_type) }}</span></td>
                                <td>
                                    <small>
                                        {{ $mailing->address_line_1 }}<br>
                                        {{ $mailing->city }}, {{ $mailing->state }} {{ $mailing->zip_code }}
                                    </small>
                                </td>
                                <td>{{ $mailing->created_at->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $days = $mailing->created_at->diffInDays(now());
                                        $color = $days > 7 ? 'danger' : ($days > 3 ? 'warning' : 'success');
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $days }} days</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.mail-court.show', $mailing->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.mail-court.mark-printed', $mailing->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Mark as Printed">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No pending mailings</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $mailings->links() }}
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.mailing-checkbox').forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

document.querySelectorAll('.mailing-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const selected = Array.from(document.querySelectorAll('.mailing-checkbox:checked')).map(cb => cb.value);
    document.getElementById('selected-count').textContent = selected.length + ' selected';
    document.getElementById('selected-ids').value = JSON.stringify(selected);
}

function createBatch() {
    const selected = Array.from(document.querySelectorAll('.mailing-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one mailing');
        return;
    }
    // Redirect to batch creation with selected IDs
    window.location.href = '{{ route("admin.mail-court.batches") }}?add=' + selected.join(',');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
