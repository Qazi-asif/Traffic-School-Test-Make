<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Details</title>
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
                <h2><i class="fas fa-layer-group"></i> Batch: {{ $batch->batch_number }}</h2>
                <p class="text-muted">Created {{ $batch->created_at->format('M d, Y') }} by {{ $batch->creator->name ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('admin.mail-court.batches') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Batches
            </a>
        </div>

        <!-- Batch Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Total Items</h6>
                        <h3>{{ $batch->total_items }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Printed</h6>
                        <h3 class="text-info">{{ $batch->printed_count }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Mailed</h6>
                        <h3 class="text-primary">{{ $batch->mailed_count }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Status</h6>
                        <h5>
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
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Actions -->
        @if($batch->status != 'closed')
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-tasks"></i> Batch Actions</h5>
                <div class="btn-group" role="group">
                    @if($batch->status == 'open' && $batch->total_items > 0)
                    <form action="{{ route('admin.mail-court.batches.print', $batch->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-print"></i> Print All Items
                        </button>
                    </form>
                    @endif

                    @if($batch->status == 'ready_to_mail')
                    <form action="{{ route('admin.mail-court.batches.mail', $batch->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shipping-fast"></i> Mark All as Mailed
                        </button>
                    </form>
                    @endif

                    @if(in_array($batch->status, ['ready_to_mail', 'mailed']))
                    <form action="{{ route('admin.mail-court.batches.close', $batch->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-lock"></i> Close Batch
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Batch Items -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Items in Batch ({{ $batch->courtMailings->count() }})</h5>
            </div>
            <div class="card-body">
                @if($batch->courtMailings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Court</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->courtMailings as $mailing)
                            <tr>
                                <td>{{ $mailing->id }}</td>
                                <td>
                                    <strong>{{ $mailing->enrollment->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $mailing->enrollment->user->email ?? '' }}</small>
                                </td>
                                <td>{{ $mailing->court->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($mailing->mailing_type) }}</span></td>
                                <td>
                                    <small>
                                        {{ $mailing->city }}, {{ $mailing->state }} {{ $mailing->zip_code }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'printed' => 'info',
                                            'mailed' => 'primary',
                                            'delivered' => 'success',
                                            'returned' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$mailing->status] ?? 'secondary' }}">
                                        {{ ucfirst($mailing->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.mail-court.show', $mailing->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No items in this batch yet. Add items from the <a href="{{ route('admin.mail-court.pending') }}">pending queue</a>.
                </div>
                @endif
            </div>
        </div>

        @if($batch->notes)
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-muted">Notes</h6>
                <p class="mb-0">{{ $batch->notes }}</p>
            </div>
        </div>
        @endif
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
