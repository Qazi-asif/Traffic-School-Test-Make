<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Dummies Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--accent) 0%, var(--hover) 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
            border-radius: 0 0 15px 15px;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1.25rem;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            height: 100%;
            margin-bottom: 1rem;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .action-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            height: 100%;
            text-decoration: none;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            color: var(--text-primary);
            text-decoration: none;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .progress-ring {
            width: 70px;
            height: 70px;
            position: relative;
        }
        
        .progress-ring-circle {
            stroke: var(--accent);
            stroke-width: 3;
            fill: transparent;
            stroke-dasharray: 219.8;
            stroke-dashoffset: 219.8;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.5s ease-in-out;
        }
        
        .recent-activity {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
        }
        
        .activity-item {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: var(--bg-primary);
            border: 1px solid var(--border);
        }
        
        .quick-actions {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1000;
        }
        
        .fab {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .fab:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chart-container {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            height: 280px;
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .metric-label {
            font-size: 0.85rem;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .trend-indicator {
            font-size: 0.75rem;
            padding: 0.2rem 0.4rem;
            border-radius: 15px;
            font-weight: 500;
        }
        
        .trend-up {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .trend-down {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .container-main {
            padding: 0 1.5rem;
        }
        
        .row {
            margin-left: -0.75rem;
            margin-right: -0.75rem;
        }
        
        .row > * {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        @media (max-width: 768px) {
            .container-main {
                padding: 0 1rem;
            }
            
            .dashboard-header {
                margin-left: -1rem;
                margin-right: -1rem;
                border-radius: 0;
            }
            
            .quick-actions {
                bottom: 1rem;
                right: 1rem;
            }
            
            .fab {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 0;">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="container-main">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="welcome-card">
                            <h2 class="mb-2">
                                <i class="fas fa-sun me-2"></i>
                                Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, 
                                {{ auth()->user()->first_name }}!
                            </h2>
                            <p class="mb-1 opacity-75">{{ auth()->user()->getRoleName() }} Dashboard</p>
                            <p class="mb-0 small opacity-50">{{ now()->format('l, F j, Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="progress-ring">
                                <svg class="progress-ring" width="70" height="70">
                                    <circle class="progress-ring-circle" cx="35" cy="35" r="30" id="progress-circle"></circle>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <div class="fw-bold" id="progress-text">0%</div>
                                    <small class="opacity-75">Complete</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-main">
            @php
                $userRole = auth()->user()->getRoleSlug();
            @endphp
            
            @if($userRole === 'student')
            <!-- Student Dashboard -->
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="mb-3"><i class="fas fa-graduation-cap me-2"></i>My Learning Journey</h3>
                </div>
            </div>

            <!-- Student Stats -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="action-icon bg-primary bg-opacity-10 text-primary mx-auto">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="metric-value text-primary" id="student-enrolled">0</div>
                        <div class="metric-label">Enrolled Courses</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="action-icon bg-success bg-opacity-10 text-success mx-auto">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="metric-value text-success" id="student-completed">0</div>
                        <div class="metric-label">Completed</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="action-icon bg-warning bg-opacity-10 text-warning mx-auto">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="metric-value text-warning" id="student-progress">0</div>
                        <div class="metric-label">In Progress</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <div class="action-icon bg-info bg-opacity-10 text-info mx-auto">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="metric-value text-info" id="student-certificates">0</div>
                        <div class="metric-label">Certificates</div>
                    </div>
                </div>
            </div>

            <!-- Student Actions -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <a href="/courses" class="action-card d-block">
                        <div class="action-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-search"></i>
                        </div>
                        <h6 class="mb-2">Browse Courses</h6>
                        <p class="text-muted mb-3 small">Discover traffic school courses for your state and requirements</p>
                        <div class="d-flex align-items-center text-primary">
                            <span class="me-2 small">Explore Now</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="/my-enrollments" class="action-card d-block">
                        <div class="action-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <h6 class="mb-2">Continue Learning</h6>
                        <p class="text-muted mb-3 small">Resume your enrolled courses and track your progress</p>
                        <div class="d-flex align-items-center text-success">
                            <span class="me-2 small">My Courses</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="/profile" class="action-card d-block">
                        <div class="action-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h6 class="mb-2">My Profile</h6>
                        <p class="text-muted mb-3 small">Manage your account settings and personal information</p>
                        <div class="d-flex align-items-center text-info">
                            <span class="me-2 small">View Profile</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-8">
                    <div class="recent-activity">
                        <h6 class="mb-3"><i class="fas fa-history me-2"></i>Recent Activity</h6>
                        <div id="student-activity">
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <p class="small">No recent activity</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="recent-activity">
                        <h6 class="mb-3"><i class="fas fa-bullhorn me-2"></i>Announcements</h6>
                        <div id="announcements">
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-bell fa-2x mb-2"></i>
                                <p class="small">No new announcements</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @else
            <!-- Admin Dashboard -->
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="mb-3"><i class="fas fa-tachometer-alt me-2"></i>System Overview</h3>
                </div>
            </div>

            <!-- Admin Stats -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="action-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-book"></i>
                            </div>
                            <span class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>12%
                            </span>
                        </div>
                        <div class="metric-value text-primary" id="total-courses">0</div>
                        <div class="metric-label">Total Courses</div>
                        <small class="text-muted">Active courses in system</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="action-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <span class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>8%
                            </span>
                        </div>
                        <div class="metric-value text-success" id="total-enrollments">0</div>
                        <div class="metric-label">Total Enrollments</div>
                        <small class="text-muted">Student enrollments</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="action-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>5%
                            </span>
                        </div>
                        <div class="metric-value text-warning" id="total-users">0</div>
                        <div class="metric-label">Total Users</div>
                        <small class="text-muted">Registered users</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="action-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <span class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>3%
                            </span>
                        </div>
                        <div class="metric-value text-info" id="completion-rate">0%</div>
                        <div class="metric-label">Completion Rate</div>
                        <small class="text-muted">Course completion</small>
                    </div>
                </div>
            </div>

            <!-- Admin Actions -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <a href="/create-course" class="action-card d-block">
                        <div class="action-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h6 class="mb-2">Manage Courses</h6>
                        <p class="text-muted mb-3 small">Create, edit, and organize course content</p>
                        <div class="d-flex align-items-center text-primary">
                            <span class="me-2 small">Manage</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/enrollments" class="action-card d-block">
                        <div class="action-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h6 class="mb-2">Enrollments</h6>
                        <p class="text-muted mb-3 small">Monitor student progress and enrollments</p>
                        <div class="d-flex align-items-center text-success">
                            <span class="me-2 small">View All</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/users" class="action-card d-block">
                        <div class="action-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h6 class="mb-2">User Management</h6>
                        <p class="text-muted mb-3 small">Manage users, roles, and permissions</p>
                        <div class="d-flex align-items-center text-warning">
                            <span class="me-2 small">Manage</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/reports" class="action-card d-block">
                        <div class="action-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h6 class="mb-2">Reports</h6>
                        <p class="text-muted mb-3 small">View analytics and system reports</p>
                        <div class="d-flex align-items-center text-info">
                            <span class="me-2 small">View Reports</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Additional Admin Tools -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/florida-courses" class="action-card d-block">
                        <div class="action-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h6 class="mb-2">Florida Courses</h6>
                        <p class="text-muted mb-3 small">Manage Florida-specific courses</p>
                        <div class="d-flex align-items-center text-danger">
                            <span class="me-2 small">Manage</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/fl-transmissions" class="action-card d-block">
                        <div class="action-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h6 class="mb-2">State Transmissions</h6>
                        <p class="text-muted mb-3 small">Monitor state reporting</p>
                        <div class="d-flex align-items-center text-secondary">
                            <span class="me-2 small">View</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/announcements" class="action-card d-block">
                        <div class="action-icon bg-purple bg-opacity-10 text-purple">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h6 class="mb-2">Announcements</h6>
                        <p class="text-muted mb-3 small">Manage system announcements</p>
                        <div class="d-flex align-items-center" style="color: #6f42c1;">
                            <span class="me-2 small">Manage</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/admin/settings" class="action-card d-block">
                        <div class="action-icon bg-dark bg-opacity-10 text-dark">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h6 class="mb-2">System Settings</h6>
                        <p class="text-muted mb-3 small">Configure system preferences</p>
                        <div class="d-flex align-items-center text-dark">
                            <span class="me-2 small">Configure</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Charts and Analytics -->
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h6 class="mb-3"><i class="fas fa-chart-area me-2"></i>Enrollment Trends</h6>
                        <canvas id="enrollmentChart" height="80"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="recent-activity">
                        <h6 class="mb-3"><i class="fas fa-bell me-2"></i>System Alerts</h6>
                        <div id="system-alerts">
                            <div class="activity-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    <div>
                                        <small class="text-muted">System Status</small>
                                        <div class="small">All systems operational</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions FAB -->
    <div class="quick-actions">
        @if($userRole === 'student')
        <button class="fab" onclick="window.location.href='/courses'" title="Browse Courses">
            <i class="fas fa-search"></i>
        </button>
        <button class="fab" onclick="window.location.href='/my-enrollments'" title="My Courses">
            <i class="fas fa-book-open"></i>
            <span class="notification-badge" id="active-courses">0</span>
        </button>
        @else
        <button class="fab" onclick="window.location.href='/create-course'" title="Add Course">
            <i class="fas fa-plus"></i>
        </button>
        <button class="fab" onclick="window.location.href='/admin/users'" title="Manage Users">
            <i class="fas fa-users"></i>
        </button>
        @endif
    </div>
    
    
    <script>
        // Dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            updateProgressRing();
        });

        async function loadDashboardData() {
            const userRole = '{{ $userRole }}';
            
            if (userRole === 'student') {
                await loadStudentData();
            } else {
                await loadAdminData();
            }
        }

        async function loadStudentData() {
            try {
                // Load student enrollments
                const response = await fetch('/web/my-enrollments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const enrollments = await response.json();
                    const enrolled = enrollments.length;
                    const completed = enrollments.filter(e => e.status === 'completed').length;
                    const inProgress = enrollments.filter(e => e.status === 'in_progress').length;
                    
                    document.getElementById('student-enrolled').textContent = enrolled;
                    document.getElementById('student-completed').textContent = completed;
                    document.getElementById('student-progress').textContent = inProgress;
                    document.getElementById('student-certificates').textContent = completed;
                    document.getElementById('active-courses').textContent = inProgress;
                    
                    // Update progress ring
                    const progressPercent = enrolled > 0 ? Math.round((completed / enrolled) * 100) : 0;
                    updateProgressRing(progressPercent);
                    
                    // Load recent activity
                    loadStudentActivity(enrollments);
                }
            } catch (error) {
                console.error('Error loading student data:', error);
            }
        }

        async function loadAdminData() {
            try {
                // Use the dedicated dashboard stats endpoint
                const statsResponse = await fetch('/web/admin/dashboard/stats', {
                    headers: { 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    credentials: 'same-origin'
                });
                
                if (statsResponse.ok) {
                    const data = await statsResponse.json();
                    const stats = data.stats;
                    
                    // Update dashboard stats
                    document.getElementById('total-courses').textContent = stats.total_courses || 0;
                    document.getElementById('total-enrollments').textContent = stats.total_enrollments || 0;
                    document.getElementById('total-users').textContent = stats.total_students || 0;
                    
                    // Calculate and display completion rate
                    const completionRate = stats.total_enrollments > 0 
                        ? Math.round((stats.completed_courses / stats.total_enrollments) * 100) 
                        : 0;
                    document.getElementById('completion-rate').textContent = completionRate + '%';
                    
                    // Update progress ring for admin (overall system health)
                    updateProgressRing(completionRate);
                } else {
                    console.error('Failed to load dashboard stats');
                    // Fallback to individual API calls if stats endpoint fails
                    await loadAdminDataFallback();
                }
                
            } catch (error) {
                console.error('Error loading admin data:', error);
                // Fallback to individual API calls
                await loadAdminDataFallback();
            }
        }
        
        async function loadAdminDataFallback() {
            try {
                // Fallback: Load individual counts (handle new paginated format)
                
                // Load courses count
                const coursesResponse = await fetch('/web/courses', {
                    headers: { 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    credentials: 'same-origin'
                });
                if (coursesResponse.ok) {
                    const courses = await coursesResponse.json();
                    const courseCount = courses.total || courses.length || 0;
                    document.getElementById('total-courses').textContent = courseCount;
                }
                
                // Load enrollments count
                const enrollmentsResponse = await fetch('/web/enrollments?per_page=1', {
                    headers: { 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    credentials: 'same-origin'
                });
                if (enrollmentsResponse.ok) {
                    const enrollmentsData = await enrollmentsResponse.json();
                    const totalEnrollments = enrollmentsData.total || enrollmentsData.length || 0;
                    document.getElementById('total-enrollments').textContent = totalEnrollments;
                    
                    // For completion rate, we need to fetch more data or use a different approach
                    document.getElementById('completion-rate').textContent = '0%';
                    updateProgressRing(0);
                }
                
                // Load users count
                const usersResponse = await fetch('/web/users?per_page=1', {
                    headers: { 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    credentials: 'same-origin'
                });
                if (usersResponse.ok) {
                    const usersData = await usersResponse.json();
                    const userCount = usersData.total || usersData.length || 0;
                    document.getElementById('total-users').textContent = userCount;
                }
                
            } catch (error) {
                console.error('Error in fallback admin data loading:', error);
            }
        }

        function updateProgressRing(percent = 0) {
            const circle = document.getElementById('progress-circle');
            const text = document.getElementById('progress-text');
            
            if (circle && text) {
                const circumference = 2 * Math.PI * 30; // radius = 30
                const offset = circumference - (percent / 100) * circumference;
                
                circle.style.strokeDashoffset = offset;
                text.textContent = percent + '%';
            }
        }

        function loadStudentActivity(enrollments) {
            const activityContainer = document.getElementById('student-activity');
            
            if (enrollments.length === 0) {
                activityContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <p>No recent activity</p>
                    </div>
                `;
                return;
            }

            const recentEnrollments = enrollments.slice(0, 5);
            const activityHTML = recentEnrollments.map(enrollment => `
                <div class="activity-item">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${enrollment.status === 'completed' ? 'check-circle text-success' : 'clock text-warning'} me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-medium">${enrollment.course_title || 'Course'}</div>
                            <small class="text-muted">
                                ${enrollment.status === 'completed' ? 'Completed' : 'In Progress'} • 
                                ${new Date(enrollment.created_at).toLocaleDateString()}
                            </small>
                        </div>
                        <div class="text-end">
                            <small class="badge bg-${enrollment.status === 'completed' ? 'success' : 'warning'}">
                                ${enrollment.progress || 0}%
                            </small>
                        </div>
                    </div>
                </div>
            `).join('');

            activityContainer.innerHTML = activityHTML;
        }

        // Theme-aware animations
        function animateCounters() {
            const counters = document.querySelectorAll('.metric-value');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent) || 0;
                let current = 0;
                const increment = target / 50;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target + (counter.textContent.includes('%') ? '%' : '');
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current) + (counter.textContent.includes('%') ? '%' : '');
                    }
                }, 20);
            });
        }

        // Load announcements
        async function loadAnnouncements() {
            try {
                const response = await fetch('/api/announcements/active', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const announcements = await response.json();
                    displayAnnouncements(announcements);
                }
            } catch (error) {
                console.error('Error loading announcements:', error);
            }
        }

        function displayAnnouncements(announcements) {
            const container = document.getElementById('announcements');
            
            if (announcements.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-bell fa-2x mb-2"></i>
                        <p>No new announcements</p>
                    </div>
                `;
                return;
            }

            const announcementsHTML = announcements.slice(0, 3).map(announcement => `
                <div class="activity-item">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-bullhorn text-primary me-3 mt-1"></i>
                        <div>
                            <div class="fw-medium">${announcement.title}</div>
                            <p class="text-muted small mb-1">${announcement.description.substring(0, 100)}...</p>
                            <small class="text-muted">${new Date(announcement.created_at).toLocaleDateString()}</small>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = announcementsHTML;
        }

        // Initialize animations after data loads
        setTimeout(() => {
            animateCounters();
        }, 500);

        // Load announcements
        loadAnnouncements();
    </script>

    <!-- Announcement Modal -->
    @php
        $userRole = auth()->user()->getRoleSlug();
        $activeAnnouncements = collect(); // Empty collection as fallback
        
        // Only try to load announcements if the Announcement model exists and table exists
        if (class_exists('\App\Models\Announcement')) {
            try {
                $activeAnnouncements = \App\Models\Announcement::where('is_active', true)
                    ->where(function($query) use ($userRole) {
                        $query->where('target_audience', 'all')
                              ->orWhere('target_audience', $userRole === 'student' ? 'student' : 'college');
                    })
                    ->where(function($query) {
                        $query->whereNull('start_date')
                              ->orWhere('start_date', '<=', now());
                    })
                    ->where(function($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', now());
                    })
                    ->get();
            } catch (\Exception $e) {
                // If there's any error (like table doesn't exist), just use empty collection
                $activeAnnouncements = collect();
            }
        }
    @endphp

    @foreach($activeAnnouncements as $announcement)
    <div class="modal fade" id="announcementModal{{ $announcement->id }}" tabindex="-1" aria-labelledby="announcementModalLabel{{ $announcement->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalLabel{{ $announcement->id }}">
                        <i class="fas fa-bullhorn"></i> {{ $announcement->title }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($announcement->image_path)
                        <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                             alt="{{ $announcement->title }}" 
                             class="img-fluid mb-3 rounded">
                    @endif
                    <p>{{ $announcement->description }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        // Show announcement modals on page load
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($activeAnnouncements as $index => $announcement)
                @if($index === 0)
                    // Show first announcement immediately
                    setTimeout(() => {
                        var modal{{ $announcement->id }} = new bootstrap.Modal(document.getElementById('announcementModal{{ $announcement->id }}'));
                        modal{{ $announcement->id }}.show();
                    }, 1000);
                @else
                    // Show subsequent announcements after previous ones are closed
                    document.getElementById('announcementModal{{ $activeAnnouncements[$index - 1]->id }}').addEventListener('hidden.bs.modal', function () {
                        var modal{{ $announcement->id }} = new bootstrap.Modal(document.getElementById('announcementModal{{ $announcement->id }}'));
                        modal{{ $announcement->id }}.show();
                    });
                @endif
            @endforeach
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('enrollmentChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7'],
                        datasets: [
                            {
                                label: 'New Enrollments',
                                data: [12, 19, 8, 15, 22, 18, 25],
                                borderColor: '#4F46E5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#4F46E5',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            },
                            {
                                label: 'Completed Courses',
                                data: [8, 12, 10, 14, 16, 20, 22],
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#10B981',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    <x-footer />
</body>
</html>
