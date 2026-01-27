@extends('layouts.app')
@section('title', 'Florida State Transmissions - Admin')
@section('content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-exchange-alt me-2"></i>Florida State Transmissions</h2>
            <p class="text-muted">Manage course completion transmissions to Florida DICDS</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Pending</h6>
                    <h2 class="card-title mb-0">{{ $pending->total() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Errors</h6>
                    <h2 class="card-title mb-0">{{ $errors->total() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Successful</h6>
                    <h2 class="card-title mb-0">{{ $successful->total() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Total</h6>
                    <h2 class="card-title mb-0">{{ $pending->total() + $errors->total() + $successful->total() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- All Transmissions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Transmissions</h5>
            @if($pending->count() > 0)
                <form action="{{ route('admin.fl-transmissions.send-all') }}" method="POST" 
                      onsubmit="return confirm('Send all {{ $pending->total() }} pending transmissions?');" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-paper-plane me-1"></i>Send All Pending ({{ $pending->total() }})
                    </button>
                </form>
            @endif
        </div>
        <div class="card-body p-0">
            @if($pending->count() > 0 || $errors->count() > 0 || $successful->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Course</th>
                                <th>Finish Date</th>
                                <th>Status</th>
                                <th>Error Code</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending->concat($errors)->concat($successful) as $transmission)
                                <tr>
                                    <td>{{ $transmission->enrollment?->user?->email ?? 'N/A' }}</td>
                                    <td>{{ $transmission->enrollment?->user?->first_name ?? 'N/A' }}</td>
                                    <td>{{ $transmission->enrollment?->user?->last_name ?? 'N/A' }}</td>
                                    <td>{{ $transmission->enrollment?->course?->name ?? 'N/A' }}</td>
                                    <td>{{ $transmission->enrollment?->completed_at?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td>
                                        @if($transmission->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($transmission->status === 'success')
                                            <span class="badge bg-success">Success</span>
                                        @else
                                            <span class="badge bg-danger">Error</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transmission->response_code)
                                            <code class="text-danger">{{ $transmission->response_code }}</code>
                                            @if(preg_match('/^[A-Z]{2}\d{3}$/', $transmission->response_code))
                                                @php
                                                    $errorMappings = [
                                                        'CF033' => 'Invalid driver license number',
                                                        'CF032' => 'Not in Florida DL format',
                                                        'CF034' => 'Multiple records found for DL',
                                                        'DV100' => 'Citation number incorrect length',
                                                        'DV030' => 'First name missing',
                                                        'DV040' => 'Last name missing',
                                                        'VL000' => 'Login failed - invalid credentials',
                                                        'VS000' => 'School validation failed',
                                                        'VI000' => 'Could not verify instructor',
                                                        'VC000' => 'Could not verify class',
                                                    ];
                                                    $errorMessage = $errorMappings[$transmission->response_code] ?? 'Unknown error';
                                                @endphp
                                                <br><small class="text-muted">{{ $errorMessage }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transmission->response_message)
                                            <small class="text-muted" title="{{ $transmission->response_message }}">
                                                {{ Str::limit($transmission->response_message, 40) }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($transmission->status === 'pending')
                                                <form action="{{ route('admin.fl-transmissions.send', $transmission->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" 
                                                            onclick="return confirm('Send this transmission?');"
                                                            title="Send">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($transmission->status === 'error')
                                                <form action="{{ route('admin.fl-transmissions.retry', $transmission->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning" 
                                                            onclick="return confirm('Retry this transmission?');"
                                                            title="Retry">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="/admin/enrollments/{{ $transmission->enrollment_id }}" 
                                               class="btn btn-outline-primary" target="_blank" title="Edit Enrollment">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="{{ route('admin.fl-transmissions.show', $transmission->id) }}" 
                                               class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-inbox display-4"></i>
                    <p class="mt-2">No transmissions found</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
