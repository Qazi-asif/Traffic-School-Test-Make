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
                        </div>
                    </div>
                </div>
            </div>
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
