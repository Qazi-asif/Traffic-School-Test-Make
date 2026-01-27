@extends('layouts.app')
@section('title', 'CTSI Result #' . $result->id)
@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.ctsi-results.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <h2><i class="fas fa-file-import me-2"></i>CTSI Result #{{ $result->id }}</h2>
        </div>
    </div>

    <!-- Result Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>CTSI Callback Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Result ID:</label>
                    <p>{{ $result->id }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Status:</label>
                    <p>
                        @if ($result->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif ($result->status === 'success')
                            <span class="badge bg-success">Success</span>
                        @else
                            <span class="badge bg-danger">Failed</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Key Response:</label>
                    <p>{{ $result->key_response ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Process Date:</label>
                    <p>{{ $result->process_date ? $result->process_date->format('m/d/Y H:i:s') : 'N/A' }}</p>
                </div>
                <div class="col-12 mb-3">
                    <label class="fw-bold">Save Data:</label>
                    <p class="p-3 bg-light rounded">{{ $result->save_data ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Received:</label>
                    <p>{{ $result->created_at->format('m/d/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Information -->
    @if($result->enrollment && $result->enrollment->user)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Name:</label>
                    <p>{{ $result->enrollment->user->first_name }} {{ $result->enrollment->user->last_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Email:</label>
                    <p>{{ $result->enrollment->user->email }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Driver License:</label>
                    <p>{{ $result->enrollment->user->driver_license ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Birth Date:</label>
                    <p>{{ $result->enrollment->user->birth_date ? $result->enrollment->user->birth_date->format('m/d/Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Course Information -->
    @if($result->enrollment && $result->enrollment->course)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Course Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Course:</label>
                    <p>{{ $result->enrollment->course->title }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Completion Date:</label>
                    <p>{{ $result->enrollment->completed_at ? $result->enrollment->completed_at->format('m/d/Y H:i:s') : 'Not completed' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Progress:</label>
                    <p>{{ $result->enrollment->progress }}%</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Status:</label>
                    <p>{{ ucfirst($result->enrollment->status) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Certificate Information -->
    @if($result->enrollment && $result->enrollment->californiaCertificate)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>California Certificate</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Certificate Number:</label>
                    <p>{{ $result->enrollment->californiaCertificate->certificate_number ?? 'Not assigned' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Court Code:</label>
                    <p>{{ $result->enrollment->californiaCertificate->court_code ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Status:</label>
                    <p>{{ ucfirst($result->enrollment->californiaCertificate->status) }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Sent At:</label>
                    <p>{{ $result->enrollment->californiaCertificate->sent_at ? $result->enrollment->californiaCertificate->sent_at->format('m/d/Y H:i:s') : 'Not sent' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Raw XML -->
    @if($result->raw_xml)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-code me-2"></i>Raw XML Data</h5>
        </div>
        <div class="card-body">
            <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">{{ $result->raw_xml }}</pre>
        </div>
    </div>
    @endif
</div>

@endsection
