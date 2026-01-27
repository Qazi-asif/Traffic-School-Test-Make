@extends('layouts.admin')

@section('title', 'Timer Violations')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Timer Violations</h3>
                    <div class="btn-group">
                        <a href="{{ route('admin.timer-violations.stats') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="violation_type">Violation Type</label>
                                <select name="violation_type" id="violation_type" class="form-control">
                                    <option value="">All Types</option>
                                    @foreach($violationTypes as $type)
                                        <option value="{{ $type->violation_type }}" 
                                                {{ request('violation_type') == $type->violation_type ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $type->violation_type)) }} ({{ $type->count }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from">From Date</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to">To Date</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="user_id">User ID</label>
                                <input type="number" name="user_id" id="user_id" class="form-control" 
                                       value="{{ request('user_id') }}" placeholder="Enter user ID">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('admin.timer-violations.index') }}" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Violations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Violation Type</th>
                                    <th>Chapter</th>
                                    <th>Details</th>
                                    <th>Detected At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($violations as $violation)
                                    <tr>
                                        <td>{{ $violation->id }}</td>
                                        <td>
                                            @if($violation->timerSession && $violation->timerSession->user)
                                                <strong>{{ $violation->timerSession->user->first_name }} {{ $violation->timerSession->user->last_name }}</strong><br>
                                                <small class="text-muted">ID: {{ $violation->timerSession->user->id }}</small>
                                            @else
                                                <span class="text-muted">Unknown User</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $this->getViolationBadgeClass($violation->violation_type) }}">
                                                {{ ucwords(str_replace('_', ' ', $violation->violation_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($violation->timerSession && $violation->timerSession->timer)
                                                Chapter {{ $violation->timerSession->chapter_id }}
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($violation->details, 50) }}</small>
                                        </td>
                                        <td>
                                            {{ $violation->detected_at->format('M j, Y H:i:s') }}<br>
                                            <small class="text-muted">{{ $violation->detected_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.timer-violations.show', $violation->id) }}" 
                                                   class="btn btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($violation->timerSession)
                                                    <a href="{{ route('admin.timer-violations.session', $violation->timerSession->id) }}" 
                                                       class="btn btn-warning" title="View Session">
                                                        <i class="fas fa-clock"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No violations found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $violations->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@php
function getViolationBadgeClass($type) {
    return match($type) {
        'tab_switch', 'window_blur' => 'warning',
        'page_reload' => 'info',
        'time_manipulation', 'devtools_opened' => 'danger',
        'blocked_shortcut', 'context_menu' => 'secondary',
        default => 'primary'
    };
}
@endphp
@endsection