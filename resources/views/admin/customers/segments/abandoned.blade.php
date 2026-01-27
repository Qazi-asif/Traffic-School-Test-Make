<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abandoned Enrollments</title>
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
            <h1 class="text-3xl font-bold text-gray-900">Abandoned Enrollments</h1>
            <p class="text-gray-600 mt-2">Students inactive for {{ $daysInactive }}+ days</p>
        </div>
        <a href="{{ route('admin.customers.segments') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">← Back</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="flex gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Days Inactive</label>
                <input type="number" name="days_inactive" value="{{ $daysInactive }}" min="1" class="border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                <select name="state" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">All States</option>
                    <option value="FL" {{ ($filters['state'] ?? '') == 'FL' ? 'selected' : '' }}>Florida</option>
                    <option value="MO" {{ ($filters['state'] ?? '') == 'MO' ? 'selected' : '' }}>Missouri</option>
                    <option value="TX" {{ ($filters['state'] ?? '') == 'TX' ? 'selected' : '' }}>Texas</option>
                    <option value="DE" {{ ($filters['state'] ?? '') == 'DE' ? 'selected' : '' }}>Delaware</option>
                </select>
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
            <input type="hidden" name="template" value="re-engagement">
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Send Re-engagement Email</button>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Inactive</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($enrollments as $enrollment)
                <tr>
                    <td class="px-6 py-4"><input type="checkbox" class="enrollment-checkbox" value="{{ $enrollment->id }}"></td>
                    <td class="px-6 py-4">
                        <div>{{ $enrollment->user->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $enrollment->user->email ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4">{{ $enrollment->course->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $enrollment->progress_percentage }}%</td>
                    <td class="px-6 py-4">{{ $enrollment->last_activity_at ? $enrollment->last_activity_at->format('M d, Y') : 'Never' }}</td>
                    <td class="px-6 py-4">
                        {{ $enrollment->last_activity_at ? $enrollment->last_activity_at->diffInDays(now()) : $enrollment->enrolled_at->diffInDays(now()) }} days
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded {{ $enrollment->payment_status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}" style="{{ $enrollment->payment_status == 'paid' ? 'background-color: #f4f6f0; color: #516425;' : '' }}">
                            {{ ucfirst($enrollment->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4"><a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="text-blue-600 hover:text-blue-900">View</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No abandoned enrollments found</td></tr>
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
        this.querySelector('.bulk-ids').value = JSON.stringify(selected);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
