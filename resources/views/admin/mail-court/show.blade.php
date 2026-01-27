<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailing Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-envelope"></i> Mailing #{{ $mailing->id }}</h2>
            <a href="{{ route('admin.mail-court.pending') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Queue
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Mailing Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Mailing Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Type:</strong> {{ ucfirst($mailing->mailing_type) }}</p>
                                <p><strong>Recipient:</strong> {{ ucfirst($mailing->recipient_type) }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $mailing->status == 'delivered' ? 'success' : 'warning' }}">
                                        {{ ucfirst($mailing->status) }}
                                    </span>
                                </p>
                                <p><strong>Created:</strong> {{ $mailing->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                @if($mailing->tracking_number)
                                <p><strong>Tracking:</strong> {{ $mailing->tracking_number }}</p>
                                <p><strong>Carrier:</strong> {{ strtoupper($mailing->carrier ?? 'N/A') }}</p>
                                @endif
                                @if($mailing->postage_cost)
                                <p><strong>Postage:</strong> ${{ number_format($mailing->postage_cost, 2) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Student Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $mailing->enrollment->user->name ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $mailing->enrollment->user->email ?? 'N/A' }}</p>
                        <p><strong>Enrollment ID:</strong> {{ $mailing->enrollment_id }}</p>
                    </div>
                </div>

                <!-- Address -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Mailing Address</h5>
                    </div>
                    <div class="card-body">
                        <address>
                            {{ $mailing->court->name ?? 'Court' }}<br>
                            {{ $mailing->address_line_1 }}<br>
                            @if($mailing->address_line_2)
                            {{ $mailing->address_line_2 }}<br>
                            @endif
                            {{ $mailing->city }}, {{ $mailing->state }} {{ $mailing->zip_code }}
                        </address>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Activity Log</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @forelse($mailing->logs as $log)
                            <div class="mb-3">
                                <strong>{{ ucfirst($log->action) }}</strong>
                                @if($log->old_status && $log->new_status)
                                <span class="text-muted">({{ $log->old_status }} → {{ $log->new_status }})</span>
                                @endif
                                <br>
                                <small class="text-muted">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                    @if($log->performedBy)
                                    by {{ $log->performedBy->name }}
                                    @endif
                                </small>
                                @if($log->notes)
                                <p class="mb-0 mt-1"><em>{{ $log->notes }}</em></p>
                                @endif
                            </div>
                            @empty
                            <p class="text-muted">No activity logged yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tasks"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        @if($mailing->status == 'pending')
                        <form action="{{ route('admin.mail-court.mark-printed', $mailing->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-print"></i> Mark as Printed
                            </button>
                        </form>
                        @endif

                        @if($mailing->status == 'printed')
                        <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#mailModal">
                            <i class="fas fa-shipping-fast"></i> Mark as Mailed
                        </button>
                        @endif

                        @if($mailing->status == 'mailed')
                        <form action="{{ route('admin.mail-court.mark-delivered', $mailing->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Mark as Delivered
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#returnModal">
                            <i class="fas fa-undo"></i> Mark as Returned
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-stream"></i> Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i> Created
                                <br><small class="text-muted">{{ $mailing->created_at->format('M d, Y H:i') }}</small>
                            </li>
                            @if($mailing->printed_at)
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i> Printed
                                <br><small class="text-muted">{{ $mailing->printed_at->format('M d, Y H:i') }}</small>
                            </li>
                            @endif
                            @if($mailing->mailed_at)
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i> Mailed
                                <br><small class="text-muted">{{ $mailing->mailed_at->format('M d, Y H:i') }}</small>
                            </li>
                            @endif
                            @if($mailing->delivered_at)
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i> Delivered
                                <br><small class="text-muted">{{ $mailing->delivered_at->format('M d, Y H:i') }}</small>
                            </li>
                            @endif
                            @if($mailing->returned_at)
                            <li class="mb-2">
                                <i class="fas fa-times-circle text-danger"></i> Returned
                                <br><small class="text-muted">{{ $mailing->returned_at->format('M d, Y H:i') }}</small>
                                @if($mailing->return_reason)
                                <br><small class="text-danger">Reason: {{ $mailing->return_reason }}</small>
                                @endif
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mail Modal -->
    <div class="modal fade" id="mailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.mail-court.mark-mailed', $mailing->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Mark as Mailed</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tracking Number (Optional)</label>
                            <input type="text" name="tracking_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Carrier</label>
                            <select name="carrier" class="form-select">
                                <option value="">Select Carrier</option>
                                <option value="usps">USPS</option>
                                <option value="fedex">FedEx</option>
                                <option value="ups">UPS</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Mark as Mailed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.mail-court.mark-returned', $mailing->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Mark as Returned</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Return Reason</label>
                            <input type="text" name="return_reason" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Mark as Returned</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
