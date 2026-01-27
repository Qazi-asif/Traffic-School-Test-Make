@extends('layouts.app')
@section('title', 'California TVCC Transmissions - Admin')
@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-share-square me-2"></i>California TVCC Transmissions</h2>
            <p class="text-muted">Manage course completion transmissions to California DMV</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.ca-transmissions.send-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Send all pending transmissions?')">
                    <i class="fas fa-paper-plane me-1"></i> Send All Pending
                </button>
            </form>
            <form action="{{ route('admin.ca-transmissions.retry-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Retry all failed transmissions?')">
                    <i class="fas fa-redo me-1"></i> Retry All Failed
                </button>
            </form>
        </div>
    </div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Status Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="text-warning">{{ $pending }}</h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success">{{ $successful }}</h3>
                    <p class="text-muted mb-0">Successful</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger">{{ $errors }}</h3>
                    <p class="text-muted mb-0">Failed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
               href="{{ route('admin.ca-transmissions.index', ['status' => 'all']) }}">
                All
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
               href="{{ route('admin.ca-transmissions.index', ['status' => 'pending']) }}">
                Pending
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'success' ? 'active' : '' }}" 
               href="{{ route('admin.ca-transmissions.index', ['status' => 'success']) }}">
                Successful
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'error' ? 'active' : '' }}" 
               href="{{ route('admin.ca-transmissions.index', ['status' => 'error']) }}">
                Failed
            </a>
        </li>
    </ul>

    <!-- Transmissions Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Response</th>
                            <th>Retries</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transmissions as $transmission)
                            <tr>
                                <td>{{ $transmission->id }}</td>
                                <td>
                                    @if($transmission->enrollment && $transmission->enrollment->user)
                                        {{ $transmission->enrollment->user->first_name }} {{ $transmission->enrollment->user->last_name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transmission->enrollment && $transmission->enrollment->course)
                                        {{ $transmission->enrollment->course->title }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transmission->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($transmission->status === 'success')
                                        <span class="badge bg-success">Success</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                                <td>
                                    <small title="{{ $transmission->response_message }}">
                                        {{ $transmission->response_code }}
                                        @if($transmission->response_message)
                                            - {{ Str::limit($transmission->response_message, 30) }}
                                        @endif
                                    </small>
                                </td>
                                <td>{{ $transmission->retry_count }}</td>
                                <td>{{ $transmission->created_at->format('m/d/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.ca-transmissions.show', $transmission->id) }}"
                                           class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($transmission->status === 'error')
                                            <form action="{{ route('admin.ca-transmissions.retry', $transmission->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning" title="Retry">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.ca-transmissions.destroy', $transmission->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Delete"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No transmissions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transmissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
