<<<<<<< HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="/admin/dashboard">Dashboard</a>
                <a class="nav-link" href="/create-course">Courses</a>
                <a class="nav-link" href="/admin/enrollments">Enrollments</a>
                <a class="nav-link" href="/admin/users">Users</a>
                <a class="nav-link" href="/admin/reports">Reports</a>
                <a class="nav-link" href="/logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <div id="app">
            <admin-dashboard></admin-dashboard>
        </div>
        
        <!-- Fallback content -->
        <div id="fallback-content" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <h2>Admin Dashboard</h2>
                    <div class="row" id="stats-cards">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 id="total-users">-</h3>
                                    <p class="mb-0">Active Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 id="total-enrollments">-</h3>
                                    <p class="mb-0">Monthly Enrollments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 id="monthly-revenue">-</h3>
                                    <p class="mb-0">Monthly Revenue</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 id="completion-rate">-</h3>
                                    <p class="mb-0">Completion Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 id="pending-submissions">-</h3>
                                    <p class="mb-0">Pending Submissions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 id="total-courses">-</h3>
                                    <p class="mb-0">Active Courses</p>
                                </div>
                            </div>
=======
@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">
                Welcome back, {{ auth('admin')->user()->name }}! 
                @if(auth('admin')->user()->isSuperAdmin())
                    You have super admin access to all states.
                @else
                    You have access to: {{ implode(', ', array_map('ucfirst', auth('admin')->user()->state_access ?? [])) }}
                @endif
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($states as $state)
            @if(isset($stats[$state]))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr($state, 0, 2)) }}</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ ucfirst($state) }} Students</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($stats[$state]['total_students']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Active:</span>
                            <span class="font-medium text-gray-900">{{ number_format($stats[$state]['active_enrollments']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Completed:</span>
                            <span class="font-medium text-gray-900">{{ number_format($stats[$state]['completed_enrollments']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Revenue:</span>
                            <span class="font-medium text-green-600">${{ number_format($stats[$state]['total_revenue'], 2) }}</span>
>>>>>>> e8fe972 (Humayun Work)
                        </div>
                    </div>
                </div>
            </div>
<<<<<<< HEAD
        </div>
    </div>
    
    <script>
        async function loadDashboardStats() {
            try {
                const response = await fetch('/web/admin/dashboard/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const stats = await response.json();
                    document.getElementById('total-users').textContent = stats.total_users;
                    document.getElementById('total-enrollments').textContent = stats.total_enrollments;
                    document.getElementById('monthly-revenue').textContent = '$' + stats.monthly_revenue;
                    document.getElementById('completion-rate').textContent = stats.completion_rate + '%';
                    document.getElementById('pending-submissions').textContent = stats.pending_submissions;
                    document.getElementById('total-courses').textContent = stats.total_courses;
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }
        
        // Show fallback and load stats if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app admin-dashboard');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadDashboardStats();
            }
        }, 1000);
    </script>
    
    @vite(['resources/js/app.js'])
</body>
</html>
=======
            @endif
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Enrollment Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Enrollment Trends</h3>
                <div class="mt-4">
                    <canvas id="enrollmentChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Revenue Trends</h3>
                <div class="mt-4">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Enrollments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Enrollments</h3>
                <div class="mt-4">
                    @if($recentEnrollments->count() > 0)
                        <div class="flow-root">
                            <ul class="-my-5 divide-y divide-gray-200">
                                @foreach($recentEnrollments as $enrollment)
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($enrollment->user->first_name ?? 'U', 0, 1) }}{{ substr($enrollment->user->last_name ?? 'U', 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $enrollment->user->first_name ?? 'Unknown' }} {{ $enrollment->user->last_name ?? 'User' }}
                                            </p>
                                            <p class="text-sm text-gray-500 truncate">
                                                {{ $enrollment->course->title ?? 'Course' }} - {{ ucfirst($enrollment->status) }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 text-sm text-gray-500">
                                            {{ $enrollment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No recent enrollments found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Payments</h3>
                <div class="mt-4">
                    @if($recentPayments->count() > 0)
                        <div class="flow-root">
                            <ul class="-my-5 divide-y divide-gray-200">
                                @foreach($recentPayments as $payment)
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-green-800">$</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $payment->enrollment->user->first_name ?? 'Unknown' }} {{ $payment->enrollment->user->last_name ?? 'User' }}
                                            </p>
                                            <p class="text-sm text-gray-500 truncate">
                                                ${{ number_format($payment->amount, 2) }} - {{ ucfirst($payment->gateway) }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 text-sm text-gray-500">
                                            {{ $payment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No recent payments found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Enrollment Chart
const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
new Chart(enrollmentCtx, {
    type: 'line',
    data: {
        labels: @json($enrollmentChart['labels']),
        datasets: [{
            label: 'Enrollments',
            data: @json($enrollmentChart['data']),
            borderColor: 'rgb(79, 70, 229)',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($revenueChart['labels']),
        datasets: [{
            label: 'Revenue ($)',
            data: @json($revenueChart['data']),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush
>>>>>>> e8fe972 (Humayun Work)
