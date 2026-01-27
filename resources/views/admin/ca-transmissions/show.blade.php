@extends('layouts.app')
@section('title', 'CA Transmission #' . $transmission->id)
@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.ca-transmissions.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <h2><i class="fas fa-share-square me-2"></i>California Transmission #{{ $transmission->id }}</h2>
        </div>
    </div>

    <!-- Transmission Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Transmission Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Transmission ID:</label>
                    <p>{{ $transmission->id }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Status:</label>
                    <p>
                        @if ($transmission->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif ($transmission->status === 'success')
                            <span class="badge bg-success">Success</span>
                        @else
                            <span class="badge bg-danger">Failed</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Response Code:</label>
                    <p>{{ $transmission->response_code ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Retry Count:</label>
                    <p>{{ $transmission->retry_count }}</p>
                </div>
                <div class="col-12 mb-3">
                    <label class="fw-bold">Response Message:</label>
                    <p class="p-3 bg-light rounded">{{ $transmission->response_message ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Created:</label>
                    <p>{{ $transmission->created_at->format('m/d/Y H:i:s') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Sent At:</label>
                    <p>{{ $transmission->sent_at ? $transmission->sent_at->format('m/d/Y H:i:s') : 'Not sent' }}</p>
                </div>
            </div>

            @if ($transmission->status === 'error')
                <div class="mt-3">
                    <form action="{{ route('admin.ca-transmissions.retry', $transmission->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-redo me-1"></i> Retry Transmission
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Student Information -->
    @if($transmission->enrollment && $transmission->enrollment->user)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Name:</label>
                    <p>{{ $transmission->enrollment->user->first_name }} {{ $transmission->enrollment->user->last_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Email:</label>
                    <p>{{ $transmission->enrollment->user->email }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Driver License:</label>
                    <p>{{ $transmission->enrollment->user->driver_license ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Birth Date:</label>
                    <p>{{ $transmission->enrollment->user->birth_date ? $transmission->enrollment->user->birth_date->format('m/d/Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Course Information -->
    @if($transmission->enrollment && $transmission->enrollment->course)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Course Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Course:</label>
                    <p>{{ $transmission->enrollment->course->title }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Completion Date:</label>
                    <p>{{ $transmission->enrollment->completed_at ? $transmission->enrollment->completed_at->format('m/d/Y H:i:s') : 'Not completed' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Progress:</label>
                    <p>{{ $transmission->enrollment->progress }}%</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Status:</label>
                    <p>{{ ucfirst($transmission->enrollment->status) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Certificate Information -->
    @if($transmission->enrollment && $transmission->enrollment->californiaCertificate)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificate Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Certificate Number:</label>
                    <p>{{ $transmission->enrollment->californiaCertificate->certificate_number ?? 'Not assigned' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">CC Seq Number:</label>
                    <p>{{ $transmission->enrollment->californiaCertificate->cc_seq_nbr ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">CC Status Code:</label>
                    <p>{{ $transmission->enrollment->californiaCertificate->cc_stat_cd ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Court Code:</label>
                    <p>{{ $transmission->enrollment->californiaCertificate->court_code ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Citation Number:</label>
                    <p>{{ $transmission->enrollment->californiaCertificate->citation_number ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Sent At:</label>
                    <p>{{ $transmission->enrollment->californiaCertificate->sent_at ? $transmission->enrollment->californiaCertificate->sent_at->format('m/d/Y H:i:s') : 'Not sent' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payload JSON -->
    @if($transmission->payload_json)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-code me-2"></i>Request Payload</h5>
        </div>
        <div class="card-body">
            <pre class="bg-light p-3 rounded">{{ json_encode($transmission->payload_json, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif
</div>

@endsection
