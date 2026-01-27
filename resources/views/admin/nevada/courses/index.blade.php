<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nevada Courses - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
    
    <div style="margin-left: 280px; padding: 2rem;">
        <div class="container-fluid">
            <h1 class="mb-4">Nevada Courses</h1>

            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Nevada Code</th>
                                <th>Type</th>
                                <th>Required Hours</th>
                                <th>Max Days</th>
                                <th>Approval Number</th>
                                <th>Expiration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $nevadaCourse)
                            <tr>
                                <td>{{ $nevadaCourse->course->title }}</td>
                                <td><strong>{{ $nevadaCourse->nevada_course_code }}</strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ str_replace('_', ' ', ucwords($nevadaCourse->course_type)) }}
                                    </span>
                                </td>
                                <td>{{ $nevadaCourse->required_hours }} hrs</td>
                                <td>{{ $nevadaCourse->max_completion_days }} days</td>
                                <td>{{ $nevadaCourse->approval_number ?? 'N/A' }}</td>
                                <td>
                                    @if($nevadaCourse->expiration_date)
                                        <span class="badge bg-{{ $nevadaCourse->isExpired() ? 'danger' : 'success' }}">
                                            {{ $nevadaCourse->expiration_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $nevadaCourse->is_active ? 'success' : 'secondary' }}">
                                        {{ $nevadaCourse->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No Nevada courses found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $courses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
