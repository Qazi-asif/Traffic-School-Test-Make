<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Enrollments - Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            margin-left: 300px;
            max-width: calc(100% - 320px);
            padding: 20px;
        }
        .enrollment-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .enrollment-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            border-color: var(--accent);
        }
        .course-header {
            background: linear-gradient(135deg, var(--accent), var(--hover));
            color: white;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        .course-type {
            font-size: 0.875rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-active { background: #516425; color: white; }
        .status-completed { background: #516425; color: white; }
        .status-pending { background: #ffc107; color: #000; }
        .status-cancelled { background: #6c757d; color: white; }
        .status-expired { background: #dc3545; color: white; }
        
        .payment-paid { background: #516425; color: white; }
        .payment-pending { background: #ffc107; color: #000; }
        .payment-failed { background: #dc3545; color: white; }
        .payment-cancelled { background: #6c757d; color: white; }
        
        .progress-container {
            background: var(--bg-secondary);
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
            margin: 1rem 0;
            position: relative;
        }
        .progress-fill {
            background: linear-gradient(90deg, #516425, #6b8332);
            height: 100%;
            transition: width 0.6s ease;
            border-radius: 10px;
            position: relative;
        }
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .btn-continue {
            background: #516425;
            border-color: #516425;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-continue:hover {
            background: #3d4b1c;
            border-color: #3d4b1c;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(81, 100, 37, 0.3);
        }
        .btn-pay-now {
            background: #ffc107;
            border-color: #ffc107;
            color: #000;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-pay-now:hover {
            background: #e0a800;
            border-color: #e0a800;
            color: #000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border);
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
            color: var(--accent);
        }
        .enrollment-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .enrollment-date {
            font-size: 0.875rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .course-stats {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
            font-size: 0.875rem;
            color: var(--text-secondary);
            flex-wrap: wrap;
        }
        .course-stats span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-secondary);
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }
        .card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .card-actions {
            margin-top: auto;
        }
        @media (max-width: 768px) {
            .enrollment-meta {
                flex-direction: column;
                align-items: flex-start;
            }
            .course-stats {
                justify-content: center;
            }
            .course-stats span {
                flex: 1;
                justify-content: center;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container-fluid px-4 mt-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <div class="row">
            <div class="col-12">
                <!-- Header Section -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-graduation-cap me-2"></i>
                            My Course Enrollments
                        </h2>
                        <p class="text-muted mb-0">Track your progress and continue your courses</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="/courses" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>
                            Browse Courses
                        </a>
                        <button class="btn btn-primary" onclick="refreshEnrollments()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments Container -->
        <div class="row">
            <div class="col-12">
                <div id="enrollments-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading your enrollments...</p>
                    </div>
                </div>

                <!-- Vue.js App Container (fallback) -->
                <div id="app" style="display: none;">
                    <my-enrollments></my-enrollments>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function loadEnrollments() {
            try {
                const response = await fetch('/web/my-enrollments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error('Failed to load enrollments');
                }
                
                const enrollments = await response.json();
                console.log('Enrollments data:', enrollments);
                displayEnrollments(enrollments);
                
            } catch (error) {
                console.error('Error loading enrollments:', error);
                document.getElementById('enrollments-container').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading enrollments. Please try again.
                    </div>
                `;
            }
        }
        
        function displayEnrollments(enrollments) {
            const container = document.getElementById('enrollments-container');
            
            if (!enrollments || enrollments.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-graduation-cap"></i>
                        <h4>No Course Enrollments</h4>
                        <p>You haven't enrolled in any courses yet.</p>
                        <a href="/courses" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Browse Available Courses
                        </a>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <div class="row">
                    ${enrollments.map(enrollment => createEnrollmentCard(enrollment)).join('')}
                </div>
            `;
        }
        
        function createEnrollmentCard(enrollment) {
            const course = enrollment.course || {};
            const courseTitle = course.title || `4-Hour Florida ${course.course_type || 'BDI'} Course`;
            const courseDesc = course.description || 'Complete this course to receive your certificate';
            const status = enrollment.status || 'active';
            const paymentStatus = enrollment.payment_status || 'pending';
            const progress = Math.round(enrollment.progress_percentage || 0);
            const enrolledDate = new Date(enrollment.enrolled_at || enrollment.created_at).toLocaleDateString();
            const duration = course.duration || course.total_duration || 240;
            const price = course.price || enrollment.amount_paid || 0;
            
            // Determine button action based on payment status
            const buttonConfig = getButtonConfig(enrollment, paymentStatus);
            
            return `
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card enrollment-card h-100">
                        <div class="course-header">
                            <div class="course-title">${courseTitle}</div>
                            <div class="course-type">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                ${course.state_code || 'FL'} • ${course.course_type || 'BDI'}
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="enrollment-meta">
                                <div class="enrollment-date">
                                    <i class="fas fa-calendar me-1"></i>
                                    Enrolled: ${enrolledDate}
                                </div>
                            </div>
                            
                            <p class="card-text flex-grow-1">${courseDesc}</p>
                            
                            <div class="course-stats">
                                <span>
                                    <i class="fas fa-clock"></i>
                                    ${duration} min
                                </span>
                                <span>
                                    <i class="fas fa-dollar-sign"></i>
                                    $${parseFloat(price).toFixed(2)}
                                </span>
                            </div>
                            
                            <div class="progress-container">
                                <div class="progress-fill" style="width: ${progress}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">Progress: ${progress}%</small>
                                <small class="text-muted">${progress === 100 ? 'Completed!' : `${100 - progress}% remaining`}</small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="status-badge status-${status}">${status.toUpperCase()}</span>
                                </div>
                                <div>
                                    <span class="status-badge payment-${paymentStatus}">${getPaymentStatusText(paymentStatus)}</span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-auto">
                                ${buttonConfig.html}
                            </div>
                            
                            ${paymentStatus === 'pending' ? `
                                <div class="alert alert-warning mt-2 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <small>Payment required to access course content</small>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }
        
        function getButtonConfig(enrollment, paymentStatus) {
            const courseId = enrollment.course_id;
            const courseTable = enrollment.course_table || 'florida_courses';
            
            if (paymentStatus === 'paid') {
                return {
                    html: `
                        <button class="btn btn-continue" onclick="continueToCourse(${enrollment.id})">
                            <i class="fas fa-play me-1"></i>
                            Continue Course
                        </button>
                        <button class="btn btn-outline-info btn-sm mt-2" onclick="viewCourseDetails('${courseTable}', ${courseId})">
                            <i class="fas fa-info-circle me-1"></i>
                            Course Details
                        </button>
                    `
                };
            } else if (paymentStatus === 'pending') {
                return {
                    html: `
                        <button class="btn btn-pay-now" onclick="redirectToPayment(${courseId}, '${courseTable}')">
                            <i class="fas fa-credit-card me-1"></i>
                            Complete Payment
                        </button>
                        <button class="btn btn-outline-info btn-sm mt-1" onclick="viewCourseDetails('${courseTable}', ${courseId})">
                            <i class="fas fa-info-circle me-1"></i>
                            Course Details
                        </button>
                        <button class="btn btn-outline-danger btn-sm mt-1" onclick="cancelEnrollment(${enrollment.id})">
                            <i class="fas fa-times me-1"></i>
                            Cancel Enrollment
                        </button>
                    `
                };
            } else {
                return {
                    html: `
                        <button class="btn btn-secondary" disabled>
                            <i class="fas fa-ban me-1"></i>
                            ${paymentStatus === 'failed' ? 'Payment Failed' : 'Unavailable'}
                        </button>
                        <button class="btn btn-outline-info btn-sm mt-2" onclick="viewCourseDetails('${courseTable}', ${courseId})">
                            <i class="fas fa-info-circle me-1"></i>
                            Course Details
                        </button>
                    `
                };
            }
        }
        
        function getPaymentStatusText(status) {
            const statusMap = {
                'paid': 'PAID',
                'pending': 'PENDING',
                'failed': 'FAILED',
                'cancelled': 'CANCELLED'
            };
            return statusMap[status] || status.toUpperCase();
        }
        
        function continueToCourse(enrollmentId) {
            window.location.href = `/course-player/${enrollmentId}`;
        }
        
        function viewCourseDetails(courseTable, courseId) {
            window.location.href = `/course-details/${courseTable}/${courseId}`;
        }
        
        function redirectToPayment(courseId, courseTable) {
            const url = `/payment?course_id=${courseId}&table=${courseTable}`;
            window.location.href = url;
        }
        
        async function cancelEnrollment(enrollmentId) {
            if (!confirm('Are you sure you want to cancel this enrollment? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/web/enrollments/${enrollmentId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    showSuccess('Enrollment cancelled successfully');
                    loadEnrollments(); // Refresh the list
                } else {
                    const data = await response.json();
                    showError(data.error || 'Failed to cancel enrollment');
                }
            } catch (error) {
                console.error('Error cancelling enrollment:', error);
                showError('Error cancelling enrollment. Please try again.');
            }
        }
        
        function refreshEnrollments() {
            const btn = event.target;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Refreshing...';
            btn.disabled = true;
            
            loadEnrollments().finally(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }
        
        function showSuccess(message) {
            // You can implement a toast notification system here
            alert('Success: ' + message);
        }
        
        function showError(message) {
            // You can implement a toast notification system here
            alert('Error: ' + message);
        }
        
        // Load enrollments when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadEnrollments();
        });
        
        // Show fallback Vue app if needed (keeping for compatibility)
        setTimeout(() => {
            const vueApp = document.querySelector('#app my-enrollments');
            if (vueApp && vueApp.children.length > 0) {
                document.getElementById('app').style.display = 'block';
                document.getElementById('enrollments-container').style.display = 'none';
            }
        }, 2000);
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
