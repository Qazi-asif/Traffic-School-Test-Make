@extends('admin.layouts.app')

@section('title', 'Florida Courses')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Florida Courses</h3>
                    <a href="{{ route('admin.florida.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Course
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search courses..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="course_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="BDI" {{ request('course_type') == 'BDI' ? 'selected' : '' }}>BDI</option>
                                    <option value="ADI" {{ request('course_type') == 'ADI' ? 'selected' : '' }}>ADI</option>
                                    <option value="TLSAE" {{ request('course_type') == 'TLSAE' ? 'selected' : '' }}>TLSAE</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary">Filter</button>
                                <a href="{{ route('admin.florida.courses.index') }}" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Courses Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Delivery</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Chapters</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                <tr>
                                    <td>{{ $course->id }}</td>
                                    <td>
                                        <strong>{{ $course->title }}</strong>
                                        @if($course->description)
                                            <br><small class="text-muted">{{ Str::limit($course->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info">{{ $course->course_type }}</span></td>
                                    <td>{{ $course->delivery_type }}</td>
                                    <td>{{ $course->duration }} hours</td>
                                    <td>${{ number_format($course->price, 2) }}</td>
                                    <td>
                                        @if($course->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $course->chapters->count() }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.florida.courses.show', $course) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.florida.courses.edit', $course) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.florida.courses.destroy', $course) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No courses found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $courses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection