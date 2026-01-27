<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Never Started</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Never Started</h1>
            <p class="text-gray-600 mt-2">Paid students who haven't started their course</p>
        </div>
        <a href="{{ route('admin.customers.segments') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">← Back</a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Since Enrollment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($enrollments as $enrollment)
                <tr>
                    <td class="px-6 py-4">
                        <div>{{ $enrollment->user->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $enrollment->user->email ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4">{{ $enrollment->course->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $enrollment->enrolled_at?->format('M d, Y') }}</td>
                    <td class="px-6 py-4">{{ $enrollment->enrolled_at?->diffInDays(now()) }} days</td>
                    <td class="px-6 py-4"><a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="text-blue-600 hover:text-blue-900">View</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No never-started enrollments found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">{{ $enrollments->links() }}</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
