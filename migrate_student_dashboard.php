<?php
/**
 * Migrate Student Dashboard Interface
 * Replicate exact student dashboard from previous system
 */

echo "👤 MIGRATING STUDENT DASHBOARD INTERFACE\n";
echo "=======================================\n\n";

// Create student dashboard view
$dashboardView = '@extends("layouts.app")

@section("content")
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="sidebar-sticky">
                <div class="user-info p-3 text-center border-bottom">
                    <div class="avatar mb-2">
                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                    </div>
                    <h6 class="mb-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h6>
                    <small class="text-muted">{{ ucfirst(auth()->user()->state) }} Student</small>
                </div>
                
                <nav class="nav flex-column mt-3">
                    <a class="nav-link active" href="#dashboard" onclick="showSection(\'dashboard\')">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="#courses" onclick="showSection(\'courses\')">
                        <i class="fas fa-book me-2"></i>My Courses
                    </a>
                    <a class="nav-link" href="#progress" onclick="showSection(\'progress\')">
                        <i class="fas fa-chart-line me-2"></i>Progress
                    </a>
                    <a class="nav-link" href="#certificates" onclick="showSection(\'certificates\')">
                        <i class="fas fa-certificate me-2"></i>Certificates
                    </a>
                    <a class="nav-link" href="#profile" onclick="showSection(\'profile\')">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                    <a class="nav-link" href="#support" onclick="showSection(\'support\')">
                        <i class="fas fa-life-ring me-2"></i>Support
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div class="text-muted">
                        <i class="fas fa-calendar me-1"></i>{{ date("F j, Y") }}
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 id="total-courses">{{ $enrollments->count() }}</h4>
                                        <p class="mb-0">Total Courses</p>
                                    </div>
                                    <i class="fas fa-book fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 id="completed-courses">{{ $enrollments->where("status", "completed")->count() }}</h4>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 id="in-progress-courses">{{ $enrollments->where("status", "active")->count() }}</h4>
                                        <p class="mb-0">In Progress</p>
                                    </div>
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 id="certificates-earned">{{ $certificates->count() }}</h4>
                                        <p class="mb-0">Certificates</p>
                                    </div>
                                    <i class="fas fa-award fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div id="recent-activity">
                                    @forelse($recentActivity as $activity)
                                    <div class="activity-item d-flex align-items-center mb-3">
                                        <div class="activity-icon me-3">
                                            <i class="fas {{ $activity->icon }} text-{{ $activity->color }}"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">{{ $activity->title }}</div>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <p>No recent activity</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" onclick="showSection(\'courses\')">
                                        <i class="fas fa-play me-2"></i>Continue Learning
                                    </button>
                                    <button class="btn btn-outline-success" onclick="showSection(\'certificates\')">
                                        <i class="fas fa-download me-2"></i>Download Certificates
                                    </button>
                                    <button class="btn btn-outline-info" onclick="showSection(\'progress\')">
                                        <i class="fas fa-chart-line me-2"></i>View Progress
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="showSection(\'support\')">
                                        <i class="fas fa-question-circle me-2"></i>Get Help
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses Section -->
            <div id="courses-section" class="content-section" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Courses</h2>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-secondary active" onclick="filterCourses(\'all\')">All</button>
                        <button class="btn btn-outline-secondary" onclick="filterCourses(\'active\')">Active</button>
                        <button class="btn btn-outline-secondary" onclick="filterCourses(\'completed\')">Completed</button>
                    </div>
                </div>

                <div class="row" id="courses-grid">
                    @forelse($enrollments as $enrollment)
                    <div class="col-md-6 col-lg-4 mb-4 course-card" data-status="{{ $enrollment->status }}">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $enrollment->course->title ?? "Course" }}</h6>
                                <span class="badge bg-{{ $enrollment->status === \'completed\' ? \'success\' : \'primary\' }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text">{{ $enrollment->course->description ?? "Course description" }}</p>
                                
                                <div class="progress mb-3">
                                    <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%">
                                        {{ round($enrollment->progress_percentage) }}%
                                    </div>
                                </div>
                                
                                <div class="course-stats">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Enrolled: {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format(\'M j, Y\') : \'N/A\' }}
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer">
                                @if($enrollment->status === \'completed\')
                                    <button class="btn btn-success btn-sm me-2" onclick="viewCertificate({{ $enrollment->id }})">
                                        <i class="fas fa-certificate me-1"></i>View Certificate
                                    </button>
                                @else
                                    <button class="btn btn-primary btn-sm me-2" onclick="continueCourse({{ $enrollment->id }})">
                                        <i class="fas fa-play me-1"></i>
                                        {{ $enrollment->started_at ? \'Continue\' : \'Start\' }}
                                    </button>
                                @endif
                                <button class="btn btn-outline-secondary btn-sm" onclick="viewCourseDetails({{ $enrollment->id }})">
                                    <i class="fas fa-info-circle me-1"></i>Details
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h4>No Courses Yet</h4>
                            <p class="text-muted">You haven\'t enrolled in any courses yet.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Progress Section -->
            <div id="progress-section" class="content-section" style="display: none;">
                <h2 class="mb-4">Learning Progress</h2>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Progress Overview</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="progress-chart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Achievements</h5>
                            </div>
                            <div class="card-body">
                                <div class="achievement-list">
                                    @if($enrollments->where("status", "completed")->count() > 0)
                                    <div class="achievement-item mb-3">
                                        <i class="fas fa-trophy text-warning me-2"></i>
                                        <span>Course Completed</span>
                                    </div>
                                    @endif
                                    
                                    @if($certificates->count() > 0)
                                    <div class="achievement-item mb-3">
                                        <i class="fas fa-certificate text-success me-2"></i>
                                        <span>Certificate Earned</span>
                                    </div>
                                    @endif
                                    
                                    <div class="achievement-item mb-3">
                                        <i class="fas fa-user-graduate text-info me-2"></i>
                                        <span>Student Enrolled</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificates Section -->
            <div id="certificates-section" class="content-section" style="display: none;">
                <h2 class="mb-4">My Certificates</h2>
                
                <div class="row">
                    @forelse($certificates as $certificate)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card certificate-card">
                            <div class="card-body text-center">
                                <i class="fas fa-certificate fa-3x text-warning mb-3"></i>
                                <h5>{{ $certificate->course_name ?? "Course Certificate" }}</h5>
                                <p class="text-muted">{{ $certificate->student_name }}</p>
                                <p class="text-muted">
                                    <small>Completed: {{ $certificate->completion_date ? date("M j, Y", strtotime($certificate->completion_date)) : "N/A" }}</small>
                                </p>
                                
                                <div class="mt-3">
                                    <button class="btn btn-primary btn-sm me-2" onclick="viewCertificate({{ $certificate->enrollment_id }})">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="downloadCertificate({{ $certificate->enrollment_id }})">
                                        <i class="fas fa-download me-1"></i>Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                            <h4>No Certificates Yet</h4>
                            <p class="text-muted">Complete a course to earn your first certificate.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profile-section" class="content-section" style="display: none;">
                <h2 class="mb-4">My Profile</h2>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Personal Information</h5>
                            </div>
                            <div class="card-body">
                                <form id="profile-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" value="{{ auth()->user()->first_name }}" name="first_name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" value="{{ auth()->user()->last_name }}" name="last_name">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="{{ auth()->user()->email }}" name="email">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" value="{{ auth()->user()->phone }}" name="phone">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">State</label>
                                        <input type="text" class="form-control" value="{{ ucfirst(auth()->user()->state) }}" readonly>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Account Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" onclick="changePassword()">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="downloadData()">
                                        <i class="fas fa-download me-2"></i>Download My Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div id="support-section" class="content-section" style="display: none;">
                <h2 class="mb-4">Support & Help</h2>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Frequently Asked Questions</h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="faq-accordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                How do I access my course?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faq-accordion">
                                            <div class="accordion-body">
                                                Go to "My Courses" section and click "Start" or "Continue" on your enrolled course.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                How do I download my certificate?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
                                            <div class="accordion-body">
                                                Complete your course and final exam, then visit the "Certificates" section to download.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Contact Support</h5>
                            </div>
                            <div class="card-body">
                                <div class="contact-info">
                                    <div class="mb-3">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <span>support@trafficschool.com</span>
                                    </div>
                                    <div class="mb-3">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        <span>1-800-TRAFFIC</span>
                                    </div>
                                    <div class="mb-3">
                                        <i class="fas fa-clock text-info me-2"></i>
                                        <span>Mon-Fri 9AM-5PM</span>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary w-100 mt-3">
                                    <i class="fas fa-comments me-2"></i>Live Chat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sidebar {
    background: #f8f9fa;
    min-height: 100vh;
    border-right: 1px solid #dee2e6;
}

.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.activity-item {
    padding: 10px;
    border-radius: 8px;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #f8f9fa;
}

.course-card {
    transition: transform 0.2s;
}

.course-card:hover {
    transform: translateY(-2px);
}

.certificate-card {
    border: 2px solid #ffc107;
    border-radius: 15px;
}

.nav-link {
    color: #6c757d;
    border-radius: 8px;
    margin: 2px 0;
}

.nav-link:hover, .nav-link.active {
    background: #007bff;
    color: white;
}

.content-section {
    padding: 20px;
}
</style>

<script>
function showSection(section) {
    // Hide all sections
    document.querySelectorAll(".content-section").forEach(el => el.style.display = "none");
    
    // Show selected section
    document.getElementById(section + "-section").style.display = "block";
    
    // Update navigation
    document.querySelectorAll(".nav-link").forEach(el => el.classList.remove("active"));
    document.querySelector(`[href="#${section}"]`).classList.add("active");
}

function filterCourses(status) {
    const cards = document.querySelectorAll(".course-card");
    const buttons = document.querySelectorAll(".btn-group .btn");
    
    // Update button states
    buttons.forEach(btn => btn.classList.remove("active"));
    event.target.classList.add("active");
    
    // Filter cards
    cards.forEach(card => {
        if (status === "all" || card.dataset.status === status) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}

function continueCourse(enrollmentId) {
    window.location.href = `/course/player?enrollment_id=${enrollmentId}`;
}

function viewCertificate(enrollmentId) {
    window.open(`/certificate/view?enrollment_id=${enrollmentId}`, "_blank");
}

function downloadCertificate(enrollmentId) {
    window.open(`/certificate/generate?enrollment_id=${enrollmentId}`, "_blank");
}

// Initialize dashboard
document.addEventListener("DOMContentLoaded", function() {
    // Load progress chart if Chart.js is available
    if (typeof Chart !== "undefined") {
        loadProgressChart();
    }
});
</script>
@endsection';

// Save the dashboard view
if (!is_dir('resources/views/student')) {
    mkdir('resources/views/student', 0755, true);
}

file_put_contents('resources/views/student/dashboard.blade.php', $dashboardView);
echo "✅ Created student dashboard interface\n";