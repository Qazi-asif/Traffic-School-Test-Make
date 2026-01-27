@extends('layouts.app')
@section('title', 'California CTSI Results - Admin')
@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-import me-2"></i>California CTSI Results</h2>
            <p class="text-muted">XML callbacks from California Traffic School Interface</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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
                    <h3 class="text-success">{{ $success }}</h3>
                    <p class="text-muted mb-0">Successful</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger">{{ $failed }}</h3>
                    <p class="text-muted mb-0">Failed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
               href="{{ route('admin.ctsi-results.index', ['status' => 'all']) }}">
                All
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
               href="{{ route('admin.ctsi-results.index', ['status' => 'pending']) }}">
                Pending
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'success' ? 'active' : '' }}" 
               href="{{ route('admin.ctsi-results.index', ['status' => 'success']) }}">
                Successful
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'failed' ? 'active' : '' }}" 
               href="{{ route('admin.ctsi-results.index', ['status' => 'failed']) }}">
                Failed
            </a>
        </li>
    </ul>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Key Response</th>
                            <th>Status</th>
                            <th>Process Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($results as $result)
                            <tr>
                                <td>{{ $result->id }}</td>
                                <td>
                                    @if($result->enrollment && $result->enrollment->user)
                                        {{ $result->enrollment->user->first_name }} {{ $result->enrollment->user->last_name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result->enrollment && $result->enrollment->course)
                                        {{ $result->enrollment->course->title }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ Str::limit($result->key_response, 30) }}</small>
                                </td>
                                <td>
                                    @if ($result->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($result->status === 'success')
                                        <span class="badge bg-success">Success</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                                <td>{{ $result->process_date ? $result->process_date->format('m/d/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.ctsi-results.show', $result->id) }}"
                                           class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.ctsi-results.destroy', $result->id) }}"
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
                                <td colspan="7" class="text-center text-muted">
                                    No CTSI results found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $results->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
