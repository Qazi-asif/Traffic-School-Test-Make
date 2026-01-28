@extends('admin.layouts.app')

@section('title', 'Florida Course: ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ $course->title }}</h3>
                    <div class="btn-group">
                        <a href="{{ route('admin.florida.courses.edit', $course) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.florida.courses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Course Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Course Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Course Type:</strong> 
                                            <span class="badge badge-info">{{ $course->course_type }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Delivery Type:</strong> {{ $course->delivery_type }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>Duration:</strong> {{ $course->duration }} hours
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Price:</strong> ${{ number_format($course->price, 2) }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>Passing Score:</strong> {{ $course->passing_score }}%
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Status:</strong> 
                                            @if($course->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($course->description)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <strong>Description:</strong>
                                            <p class="mt-1">{{ $course->description }}</p>
                                        </div>
                                    </div>
                                    @endif
                                    @if($course->certificate_type)
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <strong>Certificate Type:</strong> {{ $course->certificate_type }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Chapters -->
                            <div class="card mt-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Course Chapters ({{ $stats['total_chapters'] }})</h5>
                                    <a href="{{ route('admin.florida.chapters.create', ['course_id' => $course->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Add Chapter
                                    </a>
                                </div>
                                <div class="card-body">
                                    @if($course->chapters->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Order</th>
                                                        <th>Title</th>
                                                        <th>Questions</th>
                                                        <th>Duration</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($course->chapters->sortBy('order_index') as $chapter)
                                                    <tr>
                                                        <td>{{ $chapter->order_index }}</td>
                                                        <td>{{ $chapter->title }}</td>
                                                        <td>{{ $chapter->questions->count() }}</td>
                                                        <td>{{ $chapter->duration_minutes ? $chapter->duration_minutes . ' min' : 'N/A' }}</td>
                                                        <td>
                                                            @if($chapter->is_active)
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-secondary">Inactive</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('admin.florida.chapters.show', $chapter) }}" class="btn btn-xs btn-info">View</a>
                                                            <a href="{{ route('admin.florida.chapters.edit', $chapter) }}" class="btn btn-xs btn-warning">Edit</a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No chapters created yet.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Recent Enrollments -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>Recent Enrollments</h5>
                                </div>
                                <div class="card-body">
                                    @if($recentEnrollments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Status</th>
                                                        <th>Progress</th>
                                                        <th>Enrolled</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentEnrollments as $enrollment)
                                                    <tr>
                                                        <td>{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $enrollment->status == 'completed' ? 'success' : ($enrollment->status == 'active' ? 'primary' : 'secondary') }}">
                                                                {{ ucfirst($enrollment->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $enrollment->progress_percentage }}%</td>
                                                        <td>{{ $enrollment->enrolled_at->format('M j, Y') }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.florida.enrollments.show', $enrollment) }}" class="btn btn-xs btn-info">View</a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No enrollments yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Statistics -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Course Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-book"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Chapters</span>
                                                    <span class="info-box-number">{{ $stats['total_chapters'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-question"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Questions</span>
                                                    <span class="info-box-number">{{ $stats['total_questions'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Enrollments</span>
                                                    <span class="info-box-number">{{ $stats['total_enrollments'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-user-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Active</span>
                                                    <span class="info-box-number">{{ $stats['active_enrollments'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-graduation-cap"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Completed</span>
                                                    <span class="info-box-number">{{ $stats['completed_enrollments'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-green"><i class="fas fa-dollar-sign"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Revenue</span>
                                                    <span class="info-box-number">${{ number_format($stats['total_revenue'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.florida.chapters.create', ['course_id' => $course->id]) }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus"></i> Add Chapter
                                        </a>
                                        <a href="{{ route('admin.florida.enrollments.create', ['course_id' => $course->id]) }}" class="btn btn-success btn-block">
                                            <i class="fas fa-user-plus"></i> Enroll Student
                                        </a>
                                        <form method="POST" action="{{ route('admin.florida.courses.duplicate', $course) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-block">
                                                <i class="fas fa-copy"></i> Duplicate Course
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.florida.courses.toggle-status', $course) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-{{ $course->is_active ? 'warning' : 'success' }} btn-block">
                                                <i class="fas fa-{{ $course->is_active ? 'pause' : 'play' }}"></i> 
                                                {{ $course->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection