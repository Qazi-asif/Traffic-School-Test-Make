<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailing Batches</title>
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
                <h2><i class="fas fa-layer-group"></i> Mailing Batches</h2>
                <p class="text-muted">Manage batch processing of court mailings</p>
            </div>
            <div>
                <a href="{{ route('admin.mail-court.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBatchModal">
                    <i class="fas fa-plus"></i> Create New Batch
                </button>
            </div>
        </div>

        <!-- Batches Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Batch Number</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Printed</th>
                                <th>Mailed</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                            <tr>
                                <td><strong>{{ $batch->batch_number }}</strong></td>
                                <td>{{ $batch->batch_date->format('M d, Y') }}</td>
                                <td>{{ $batch->total_items }}</td>
                                <td>{{ $batch->printed_count }}</td>
                                <td>{{ $batch->mailed_count }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'open' => 'secondary',
                                            'printing' => 'info',
                                            'ready_to_mail' => 'warning',
                                            'mailed' => 'primary',
                                            'closed' => 'success'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$batch->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                    </span>
                                </td>
                                <td>{{ $batch->creator->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.mail-court.batches.show', $batch->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No batches created yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $batches->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Create Batch Modal -->
    <div class="modal fade" id="createBatchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.mail-court.batches.create') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Batch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this batch..."></textarea>
                        </div>
                        <p class="text-muted small">After creating the batch, you can add mailings to it from the pending queue.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
