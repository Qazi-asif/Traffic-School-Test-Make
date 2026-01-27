<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Export - {{ $type }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .info { background-color: #f0f0f0; padding: 10px; margin: 10px 0; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Data Export Report</h1>
    <div class="info">
        <strong>Export Type:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}<br>
        <strong>Generated:</strong> {{ $generated_at }}<br>
        <strong>User:</strong> {{ $user->name }} ({{ $user->email }})
    </div>

    <h2>User Information</h2>
    <table>
        <tr><th>Field</th><th>Value</th></tr>
        <tr><td>Name</td><td>{{ $user->name }}</td></tr>
        <tr><td>Email</td><td>{{ $user->email }}</td></tr>
        <tr><td>Role</td><td>{{ $user->role->name ?? 'N/A' }}</td></tr>
        <tr><td>Created</td><td>{{ $user->created_at }}</td></tr>
    </table>

    @if($type === 'enrollments' || $type === 'full_report')
    <h2>Enrollments</h2>
    <table>
        <tr>
            <th>Course</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Enrolled Date</th>
        </tr>
        @forelse($user->enrollments ?? [] as $enrollment)
        <tr>
            <td>{{ $enrollment->course->title ?? 'N/A' }}</td>
            <td>{{ $enrollment->status }}</td>
            <td>{{ $enrollment->progress_percentage }}%</td>
            <td>{{ $enrollment->created_at }}</td>
        </tr>
        @empty
        <tr><td colspan="4">No enrollments found</td></tr>
        @endforelse
    </table>
    @endif

    @if($type === 'certificates' || $type === 'full_report')
    <h2>Certificates</h2>
    <p>Certificate data would be displayed here</p>
    @endif

    @if($type === 'payments' || $type === 'full_report')
    <h2>Payment History</h2>
    <p>Payment data would be displayed here</p>
    @endif

    <div class="info">
        <small>This export was generated on {{ $generated_at }}. For questions, contact support.</small>
    </div>
</body>
</html>
