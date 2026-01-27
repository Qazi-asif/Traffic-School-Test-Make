@extends('layouts.admin')

@section('title', 'Timer Violation Statistics')

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['total_violations']) }}</h4>
                            <p class="mb-0">Total Violations</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['today_violations']) }}</h4>
                            <p class="mb-0">Today's Violations</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['this_week_violations']) }}</h4>
                            <p class="mb-0">This Week</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['violation_types']->count() }}</h4>
                            <p class="mb-0">Violation Types</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Violation Types Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Violation Types</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['violation_types'] as $type)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $this->getViolationBadgeClass($type->violation_type) }}">
                                                {{ ucwords(str_replace('_', ' ', $type->violation_type)) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($type->count) }}</td>
                                        <td>
                                            @php
                                                $percentage = $stats['total_violations'] > 0 ? ($type->count / $stats['total_violations']) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Violators -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Violators</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Violations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['top_violators'] as $session)
                                    <tr>
                                        <td>
                                            @if($session->user)
                                                <strong>{{ $session->user->first_name }} {{ $session->user->last_name }}</strong><br>
                                                <small class="text-muted">ID: {{ $session->user->id }}</small>
                                            @else
                                                <span class="text-muted">Unknown User</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">{{ $session->violations_count }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.timer-violations.session', $session->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No violations found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Violations -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Violations</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.timer-violations.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_violations'] as $violation)
                                    <tr>
                                        <td>
                                            @if($violation->timerSession && $violation->timerSession->user)
                                                {{ $violation->timerSession->user->first_name }} {{ $violation->timerSession->user->last_name }}
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $this->getViolationBadgeClass($violation->violation_type) }}">
                                                {{ ucwords(str_replace('_', ' ', $violation->violation_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($violation->details, 40) }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $violation->detected_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent violations</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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