<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiring Soon</title>
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
            <h1 class="text-3xl font-bold text-gray-900">Expiring Soon</h1>
            <p class="text-gray-600 mt-2">Enrollments expiring within {{ $days }} days</p>
        </div>
        <a href="{{ route('admin.customers.segments') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">← Back</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="flex gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Days Until Expiration</label>
                <input type="number" name="days" value="{{ $days }}" min="1" max="30" class="border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex space-x-4">
        <form action="{{ route('admin.customers.bulk-remind') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="enrollment_ids" class="bulk-ids">
            <input type="hidden" name="template" value="expiration-warning">
            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Send Expiration Warning</button>
        </form>
        <form action="{{ route('admin.customers.bulk-extend') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="enrollment_ids" class="bulk-ids">
            <input type="number" name="days" value="7" min="1" class="border-gray-300 rounded-md shadow-sm w-20 mr-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Extend Expiration</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left"><input type="checkbox" id="select-all"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires On</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Remaining</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($enrollments as $enrollment)
                <tr class="{{ $enrollment->court_date->diffInDays(now()) <= 3 ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4"><input type="checkbox" class="enrollment-checkbox" value="{{ $enrollment->id }}"></td>
                    <td class="px-6 py-4">
                        <div>{{ $enrollment->user->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $enrollment->user->email ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4">{{ $enrollment->course->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $enrollment->progress_percentage }}%</td>
                    <td class="px-6 py-4">{{ $enrollment->court_date?->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded {{ $enrollment->court_date->diffInDays(now()) <= 3 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $enrollment->court_date->diffInDays(now()) }} days
                        </span>
                    </td>
                    <td class="px-6 py-4"><a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="text-blue-600 hover:text-blue-900">View</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No expiring enrollments found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">{{ $enrollments->links() }}</div>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.enrollment-checkbox').forEach(cb => cb.checked = this.checked);
});
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const selected = Array.from(document.querySelectorAll('.enrollment-checkbox:checked')).map(cb => cb.value);
        const bulkInput = this.querySelector('.bulk-ids');
        if (bulkInput) bulkInput.value = JSON.stringify(selected);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
