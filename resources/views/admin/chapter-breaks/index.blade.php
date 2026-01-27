@extends('layouts.app')

@section('title', 'Chapter Breaks - ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-pause-circle me-2"></i>Chapter Breaks</h2>
                    <p class="text-muted mb-0">
                        <strong>{{ $course->title }}</strong> 
                        <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('-', ' ', $courseType)) }}</span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.chapter-breaks.create', [$courseType, $courseId]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Chapter Break
                    </a>
                    <a href="{{ $courseType === 'florida-courses' ? '/admin/florida-courses' : '/admin/manage-courses' }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Courses
                    </a>
                </div>
            </div>

            <!-- Course Chapters Overview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Course Structure</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($chapters as $chapter)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $chapter->title }}</h6>
                                                <small class="text-muted">Chapter {{ $chapter->order_index }}</small>
                                            </div>
                                            @php
                                                $hasBreak = $breaks->where('after_chapter_id', $chapter->id)->first();
                                            @endphp
                                            @if($hasBreak)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-pause me-1"></i>Break After
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Chapter Breaks List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-pause-circle me-2"></i>Configured Breaks ({{ $breaks->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($breaks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>After Chapter</th>
                                        <th>Break Title</th>
                                        <th>Duration</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($breaks as $break)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $break->chapter->title ?? 'Chapter ' . $break->after_chapter_id }}</strong>
                                                    <br>
                                                    <small class="text-muted">Chapter {{ $break->chapter->order_index ?? $break->after_chapter_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $break->break_title }}</strong>
                                                    @if($break->break_message)
                                                        <br>
                                                        <small class="text-muted">{{ Str::limit($break->break_message, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info fs-6">
                                                    {{ $break->formatted_duration }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $break->is_mandatory ? 'danger' : 'success' }}">
                                                    {{ $break->is_mandatory ? 'Mandatory' : 'Optional' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $break->is_active ? 'success' : 'secondary' }}">
                                                    {{ $break->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.chapter-breaks.edit', [$courseType, $courseId, $break->id]) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.chapter-breaks.toggle', [$courseType, $courseId, $break->id]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-{{ $break->is_active ? 'warning' : 'success' }}"
                                                                title="{{ $break->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas fa-{{ $break->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.chapter-breaks.destroy', [$courseType, $courseId, $break->id]) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this break?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-pause-circle fa-3x text-muted mb-3"></i>
                            <h5>No Chapter Breaks Configured</h5>
                            <p class="text-muted mb-4">
                                Add breaks between chapters to give students time to rest and absorb the material.
                            </p>
                            <a href="{{ route('admin.chapter-breaks.create', [$courseType, $courseId]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Your First Break
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-hide success messages
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 3000);
</script>
@endsection