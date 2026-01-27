<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paid, Not Completed</title>
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
            <h1 class="text-3xl font-bold text-gray-900">Paid, Not Completed</h1>
            <p class="text-gray-600 mt-2">Students who paid but haven't finished their course</p>
        </div>
        <a href="{{ route('admin.customers.segments') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            ← Back to Segments
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                <select name="state" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">All States</option>
                    <option value="FL" {{ ($filters['state'] ?? '') == 'FL' ? 'selected' : '' }}>Florida</option>
                    <option value="MO" {{ ($filters['state'] ?? '') == 'MO' ? 'selected' : '' }}>Missouri</option>
                    <option value="TX" {{ ($filters['state'] ?? '') == 'TX' ? 'selected' : '' }}>Texas</option>
                    <option value="DE" {{ ($filters['state'] ?? '') == 'DE' ? 'selected' : '' }}>Delaware</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                <select name="course_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ ($filters['course_id'] ?? '') == $course->id ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Progress Min %</label>
                <input type="number" name="progress_min" value="{{ $filters['progress_min'] ?? '' }}" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Progress Max %</label>
                <input type="number" name="progress_max" value="{{ $filters['progress_max'] ?? '' }}" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Apply Filters
                </button>
                <a href="{{ route('admin.customers.paid-incomplete') }}" class="ml-2 px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex space-x-4">
        <form action="{{ route('admin.customers.bulk-remind') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="enrollment_ids" class="bulk-ids">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Send Reminder to Selected
            </button>
        </form>
        <form action="{{ route('admin.customers.bulk-export') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="enrollment_ids" class="bulk-ids">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" style="background-color: #516425;" onmouseover="this.style.backgroundColor='#3d4b1c'" onmouseout="this.style.backgroundColor='#516425'">
                Export Selected
            </button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">State</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Since Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($enrollments as $enrollment)
                    <tr>
                        <td class="px-6 py-4">
                            <input type="checkbox" class="enrollment-checkbox" value="{{ $enrollment->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $enrollment->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $enrollment->user->email ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $enrollment->course->title ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $enrollment->course->state ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $enrollment->enrolled_at?->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${{ number_format($enrollment->amount_paid, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                </div>
                                <span class="text-sm">{{ $enrollment->progress_percentage }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $enrollment->last_activity_at ? $enrollment->last_activity_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $enrollment->enrolled_at?->diffInDays(now()) }} days
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-4 text-center text-gray-500">No enrollments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $enrollments->links() }}
        </div>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.enrollment-checkbox').forEach(cb => cb.checked = this.checked);
});

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const selected = Array.from(document.querySelectorAll('.enrollment-checkbox:checked')).map(cb => cb.value);
        this.querySelector('.bulk-ids').value = JSON.stringify(selected);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
