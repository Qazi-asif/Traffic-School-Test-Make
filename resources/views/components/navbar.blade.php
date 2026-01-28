<!-- Vertical Sidebar Navigation -->
<nav style="width: 280px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1000; overflow-y: auto; overflow-x: hidden; background: var(--bg-secondary) !important;">
        <div class="p-3" style="padding-bottom: 2rem !important;">
            <a class="navbar-brand text-decoration-none d-block mb-4" href="/dashboard" style="font-weight: 700;">
                <div style="background: linear-gradient(135deg, #4a5d23, #6b7c2e); padding: 12px 16px; border-radius: 8px; display: inline-block; width: 100%; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <div style="color: #ffd700 !important; font-weight: bold; font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); margin-bottom: 2px;">Dummies</div>
                    <div style="color: white !important; font-size: 14px; font-weight: 600; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">TRAFFIC SCHOOL.COM</div>
                </div>
            </a>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard" style="color: {{ request()->is('dashboard') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('dashboard') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('courses') ? 'active' : '' }}" href="/courses" style="color: {{ request()->is('courses') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('courses') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-book me-2"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('my-enrollments') ? 'active' : '' }}" href="/my-enrollments" style="color: {{ request()->is('my-enrollments') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('my-enrollments') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-user-graduate me-2"></i> My Enrollments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('generate-certificates') ? 'active' : '' }}" href="/generate-certificates" style="color: {{ request()->is('generate-certificates') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('generate-certificates') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-award me-2"></i> Generate Certificates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('my-certificates') ? 'active' : '' }}" href="/my-certificates" style="color: {{ request()->is('my-certificates') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('my-certificates') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-certificate me-2"></i> My Certificates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('booklets*') ? 'active' : '' }}" href="/booklets" style="color: {{ request()->is('booklets*') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('booklets*') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-book-open me-2"></i> My Course Booklets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('open-ticket') ? 'active' : '' }}" href="/open-ticket" style="color: {{ request()->is('open-ticket') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('open-ticket') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> Open a Ticket
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('profile') ? 'active' : '' }}" href="/profile" style="color: {{ request()->is('profile') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('profile') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('account-security') ? 'active' : '' }}" href="/account-security" style="color: {{ request()->is('account-security') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('account-security') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-shield-alt me-2"></i> Account Security
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('my-payments') ? 'active' : '' }}" href="/my-payments" style="color: {{ request()->is('my-payments') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('my-payments') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-receipt me-2"></i> My Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('student/feedback*') ? 'active' : '' }}" href="/student/feedback" style="color: {{ request()->is('student/feedback*') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('student/feedback*') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-comments me-2"></i> My Feedback
                    </a>
                </li>
                
                @if(auth()->check() && auth()->user()->isAdmin())
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">ADMIN PANEL</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dashboard') ? 'bg-primary rounded' : '' }}" href="/admin/dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/enrollments') ? 'bg-primary rounded' : '' }}" href="/admin/enrollments">
                            <i class="fas fa-users me-2"></i> Enrollments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/users') ? 'bg-primary rounded' : '' }}" href="/admin/users">
                            <i class="fas fa-user me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/user-access') ? 'bg-primary rounded' : '' }}" href="/admin/user-access">
                            <i class="fas fa-lock me-2"></i> User Access
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/final-exam-attempts*') ? 'bg-primary rounded' : '' }}" href="/admin/final-exam-attempts">
                            <i class="fas fa-clipboard-check me-2"></i> Final Exam Attempts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/certificates*') ? 'bg-primary rounded' : '' }}" href="/admin/certificates">
                            <i class="fas fa-certificate me-2"></i> Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/support/tickets*') ? 'bg-primary rounded' : '' }}" href="/admin/support/tickets">
                            <i class="fas fa-ticket-alt me-2"></i> Support Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/faqs*') ? 'bg-primary rounded' : '' }}" href="/admin/faqs">
                            <i class="fas fa-question me-2"></i> FAQs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/reports') ? 'bg-primary rounded' : '' }}" href="/admin/reports">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">ONLINE COURSES</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('create-course') ? 'bg-primary rounded' : '' }}" href="/create-course">
                            <i class="fas fa-book me-2"></i> Manage Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-courses*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-courses">
                            <i class="fas fa-cogs me-2"></i> Courses (Advanced)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/course-timers*') ? 'bg-primary rounded' : '' }}" href="/admin/course-timers">
                            <i class="fas fa-hourglass me-2"></i> Course Timers
                        </a>
                    </li>
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <!--<li class="nav-item">-->
                    <!--    <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">COURSE BOOKLETS</small>-->
                    <!--</li>-->
                    <!-- <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/booklets') && !request()->is('admin/booklets/orders*') && !request()->is('admin/booklets/templates*') ? 'bg-primary rounded' : '' }}" href="/admin/booklets">
                            <i class="fas fa-book-open me-2"></i> Manage Booklets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/booklets/orders*') ? 'bg-primary rounded' : '' }}" href="/admin/booklets/orders">
                            <i class="fas fa-shopping-cart me-2"></i> Booklet Orders
                        </a>
                    </li> -->
                    <!-- <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/booklets/templates*') ? 'bg-primary rounded' : '' }}" href="/admin/booklets/templates">
                            <i class="fas fa-file-alt me-2"></i> Booklet Templates
                        </a>
                    </li>
                     -->
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">EMAIL & NOTIFICATIONS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/email-templates') ? 'bg-primary rounded' : '' }}" href="/admin/email-templates">
                            <i class="fas fa-envelope me-2"></i> Email Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/notifications') ? 'bg-primary rounded' : '' }}" href="/admin/notifications">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/newsletter/subscribers*') ? 'bg-primary rounded' : '' }}" href="/admin/newsletter/subscribers">
                            <i class="fas fa-newspaper me-2"></i> Newsletter Subscribers
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">INTEGRATION & TRANSMISSIONS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/state-integration') ? 'bg-primary rounded' : '' }}" href="/admin/state-integration">
                            <i class="fas fa-globe me-2"></i> State Integration
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/state-transmissions*') ? 'bg-primary rounded' : '' }}" href="/admin/state-transmissions">
                            <i class="fas fa-exchange-alt me-2"></i> All State Transmissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/fl-transmissions*') ? 'bg-primary rounded' : '' }}" href="/admin/fl-transmissions">
                            <i class="fas fa-paper-plane me-2"></i> FL Transmissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/ca-transmissions*') ? 'bg-primary rounded' : '' }}" href="/admin/ca-transmissions">
                            <i class="fas fa-share-square me-2"></i> CA Transmissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/ctsi-results*') ? 'bg-primary rounded' : '' }}" href="/admin/ctsi-results">
                            <i class="fas fa-file-import me-2"></i> CA CTSI Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/state-stamps*') ? 'bg-primary rounded' : '' }}" href="/admin/state-stamps">
                            <i class="fas fa-stamp me-2"></i> State Stamps
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">PAYMENTS & REVENUE</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/revenue*') ? 'bg-primary rounded' : '' }}" href="/admin/revenue">
                            <i class="fas fa-chart-line me-2"></i> Revenue Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payments') ? 'bg-primary rounded' : '' }}" href="/admin/payments">
                            <i class="fas fa-credit-card me-2"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/pricing-rules*') ? 'bg-primary rounded' : '' }}" href="/admin/pricing-rules">
                            <i class="fas fa-tag me-2"></i> Pricing Rules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/coupons*') ? 'bg-primary rounded' : '' }}" href="/admin/coupons">
                            <i class="fas fa-tags me-2"></i> Coupons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/invoices') ? 'bg-primary rounded' : '' }}" href="/admin/invoices">
                            <i class="fas fa-receipt me-2"></i> Invoices
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">ACCESSIBILITY, SECURITY & DATA</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/security-questions*') ? 'bg-primary rounded' : '' }}" href="/admin/security-questions">
                            <i class="fas fa-question-circle me-2"></i> Security Questions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/final-exam-questions*') ? 'bg-primary rounded' : '' }}" href="/admin/final-exam-questions">
                            <i class="fas fa-clipboard-question me-2"></i> Final Exam Questions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/final-exam-questions*') ? 'bg-primary rounded' : '' }}" href="/admin/final-exam-settings">
                            <i class="fas fa-clipboard-question me-2"></i> Final Exam Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/free-response-quiz*') && !request()->is('admin/free-response-quiz-placements*') && !request()->is('admin/free-response-quiz-submissions*') ? 'bg-primary rounded' : '' }}" href="/admin/free-response-quiz">
                            <i class="fas fa-edit me-2"></i> Free Response Quiz
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/free-response-quiz-placements*') ? 'bg-primary rounded' : '' }}" href="/admin/free-response-quiz-placements">
                            <i class="fas fa-map-marker-alt me-2"></i> Quiz Placements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/free-response-quiz-submissions*') ? 'bg-primary rounded' : '' }}" href="/admin/free-response-quiz-submissions">
                            <i class="fas fa-eye me-2"></i> View Submissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/student-feedback*') ? 'bg-primary rounded' : '' }}" href="/admin/student-feedback">
                            <i class="fas fa-comments me-2"></i> Student Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/data-export') ? 'bg-primary rounded' : '' }}" href="/admin/data-export">
                            <i class="fas fa-download me-2"></i> Data Export
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">LEGAL & COMPLIANCE</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/legal-documents*') ? 'bg-primary rounded' : '' }}" href="/admin/legal-documents">
                            <i class="fas fa-file-contract me-2"></i> Legal Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/user-consents*') ? 'bg-primary rounded' : '' }}" href="/admin/user-consents">
                            <i class="fas fa-handshake me-2"></i> User Consents
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">FLORIDA DICDS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-dashboard*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-dashboard">
                            <i class="fas fa-home me-2"></i> Florida Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/flhsmv/submissions*') ? 'bg-primary rounded' : '' }}" href="/admin/flhsmv/submissions">
                            <i class="fas fa-paper-plane me-2"></i> FLHSMV Submissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-orders*') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-orders">
                            <i class="fas fa-shopping-cart me-2"></i> DICDS Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-certificates*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-certificates">
                            <i class="fas fa-certificate me-2"></i> Florida Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/compliance-reports*') ? 'bg-primary rounded' : '' }}" href="/admin/compliance-reports">
                            <i class="fas fa-file-pdf me-2"></i> Compliance Reports
                        </a>
                    </li>
                    
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">NEVADA STATE</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/nevada*') ? 'bg-primary rounded' : '' }}" href="/admin/nevada">
                            <i class="fas fa-home me-2"></i> Nevada Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/nevada/students*') ? 'bg-primary rounded' : '' }}" href="/admin/nevada/students">
                            <i class="fas fa-users me-2"></i> Nevada Students
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA SECURITY & AUDIT</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-audit') ? 'bg-primary rounded' : '' }}" href="/admin/florida-audit">
                            <i class="fas fa-clipboard-list me-2"></i> Audit Trail
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-compliance') ? 'bg-primary rounded' : '' }}" href="/admin/florida-compliance">
                            <i class="fas fa-check-circle me-2"></i> Compliance Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-data-export') ? 'bg-primary rounded' : '' }}" href="/admin/florida-data-export">
                            <i class="fas fa-download me-2"></i> Data Export Tool
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA MOBILE & ACCESSIBILITY</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-mobile') ? 'bg-primary rounded' : '' }}" href="/admin/florida-mobile">
                            <i class="fas fa-mobile-alt me-2"></i> Mobile Optimization
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-accessibility') ? 'bg-primary rounded' : '' }}" href="/admin/florida-accessibility">
                            <i class="fas fa-universal-access me-2"></i> Accessibility Settings
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA DICDS UI & WORKFLOW</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/welcome') ? 'bg-primary rounded' : '' }}" href="/dicds/welcome">
                            <i class="fas fa-home me-2"></i> DICDS Welcome
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/main-menu') ? 'bg-primary rounded' : '' }}" href="/dicds/main-menu">
                            <i class="fas fa-th-large me-2"></i> DICDS Main Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-user-management') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-user-management">
                            <i class="fas fa-users-cog me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-access-requests') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-access-requests">
                            <i class="fas fa-key me-2"></i> Access Requests
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA DICDS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/certificate-inventory*') ? 'bg-primary rounded' : '' }}" href="/admin/certificate-inventory">
                            <i class="fas fa-boxes me-2"></i> Certificate Inventory
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">PAYMENT SYSTEM</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-payments*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-payments">
                            <i class="fas fa-dollar-sign me-2"></i> Florida Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/fee-remittances*') ? 'bg-primary rounded' : '' }}" href="/admin/fee-remittances">
                            <i class="fas fa-money-check-alt me-2"></i> Fee Remittances
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/pricing-rules*') ? 'bg-primary rounded' : '' }}" href="/admin/pricing-rules">
                            <i class="fas fa-tags me-2"></i> Pricing Rules
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">EMAIL & NOTIFICATIONS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-email-templates*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-email-templates">
                            <i class="fas fa-envelope-open-text me-2"></i> Email Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/provider-menu') ? 'bg-primary rounded' : '' }}" href="/dicds/provider-menu">
                            <i class="fas fa-home me-2"></i> Provider Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/schools*') ? 'bg-primary rounded' : '' }}" href="/dicds/schools/maintain">
                            <i class="fas fa-school me-2"></i> Manage Schools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/instructors*') ? 'bg-primary rounded' : '' }}" href="/dicds/instructors/manage">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Manage Instructors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/certificates/order*') ? 'bg-primary rounded' : '' }}" href="/dicds/certificates/order">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Order Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/certificates/distribute*') ? 'bg-primary rounded' : '' }}" href="/dicds/certificates/distribute">
                            <i class="fas fa-share-alt me-2"></i> Distribute Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/reports*') ? 'bg-primary rounded' : '' }}" href="/dicds/reports/menu">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/web-service-info*') ? 'bg-primary rounded' : '' }}" href="/dicds/web-service-info">
                            <i class="fas fa-info-circle me-2"></i> Web Service Info
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">LEGAL & COMPLIANCE</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/legal-documents*') ? 'bg-primary rounded' : '' }}" href="/admin/legal-documents">
                            <i class="fas fa-file-contract me-2"></i> Legal Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/copyright-protection*') ? 'bg-primary rounded' : '' }}" href="/admin/copyright-protection">
                            <i class="fas fa-shield-alt me-2"></i> Copyright Protection
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/user-consents*') ? 'bg-primary rounded' : '' }}" href="/admin/user-consents">
                            <i class="fas fa-user-check me-2"></i> User Consents
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">NEW MODULES</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/flhsmv/submissions*') ? 'bg-primary rounded' : '' }}" href="/admin/flhsmv/submissions">
                            <i class="fas fa-paper-plane me-2"></i> FLHSMV Submissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payments/transactions*') ? 'bg-primary rounded' : '' }}" href="/admin/payments/transactions">
                            <i class="fas fa-credit-card me-2"></i> Payment Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payments/stripe*') ? 'bg-primary rounded' : '' }}" href="/admin/payments/stripe">
                            <i class="fab fa-stripe me-2"></i> Stripe Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/state-stamps*') ? 'bg-primary rounded' : '' }}" href="/admin/state-stamps">
                            <i class="fas fa-stamp me-2"></i> State Stamps
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/support/tickets*') ? 'bg-primary rounded' : '' }}" href="/admin/support/tickets">
                            <i class="fas fa-ticket-alt me-2"></i> Support Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/faqs*') ? 'bg-primary rounded' : '' }}" href="/admin/faqs">
                            <i class="fas fa-question me-2"></i> FAQs
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">SURVEYS & FEEDBACK</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/surveys*') ? 'bg-primary rounded' : '' }}" href="/admin/surveys">
                            <i class="fas fa-poll me-2"></i> Manage Surveys
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/survey-reports*') ? 'bg-primary rounded' : '' }}" href="/admin/survey-reports">
                            <i class="fas fa-chart-pie me-2"></i> Survey Reports
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">NEWSLETTER & MARKETING</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/newsletter/subscribers*') ? 'bg-primary rounded' : '' }}" href="/admin/newsletter/subscribers">
                            <i class="fas fa-envelope me-2"></i> Newsletter Subscribers
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">COURSE BOOKLETS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/booklets') && !request()->is('admin/booklets/orders*') && !request()->is('admin/booklets/templates*') ? 'bg-primary rounded' : '' }}" href="/admin/booklets">
                            <i class="fas fa-book me-2"></i> Manage Booklets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/booklets/orders/all') ? 'bg-primary rounded' : '' }}" href="/admin/booklets/orders/all">
                            <i class="fas fa-shopping-bag me-2"></i> Booklet Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/booklets/templates*') ? 'bg-primary rounded' : '' }}" href="/admin/booklets/templates/all">
                            <i class="fas fa-file-code me-2"></i> Booklet Templates
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">REVENUE & REPORTS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/revenue*') ? 'bg-primary rounded' : '' }}" href="/admin/revenue/dashboard">
                            <i class="fas fa-chart-line me-2"></i> Revenue Dashboard
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">PAYMENT MANAGEMENT</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payment-gateways*') ? 'bg-primary rounded' : '' }}" href="/admin/payment-gateways">
                            <i class="fas fa-credit-card me-2"></i> Payment Gateways
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/merchants*') ? 'bg-primary rounded' : '' }}" href="/admin/merchants">
                            <i class="fas fa-building me-2"></i> Merchant Accounts
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">NEVADA STATE</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/nevada*') ? 'bg-primary rounded' : '' }}" href="/admin/nevada">
                            <i class="fas fa-mountain me-2"></i> Nevada Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/nevada/students*') ? 'bg-primary rounded' : '' }}" href="/admin/nevada/students">
                            <i class="fas fa-users me-2"></i> Nevada Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/nevada/compliance-logs*') ? 'bg-primary rounded' : '' }}" href="/admin/nevada/compliance-logs">
                            <i class="fas fa-clipboard-check me-2"></i> Compliance Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/nevada/certificates*') ? 'bg-primary rounded' : '' }}" href="/admin/nevada/certificates">
                            <i class="fas fa-certificate me-2"></i> Nevada Certificates
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">CUSTOMER MANAGEMENT</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/customers/segments*') ? 'bg-primary rounded' : '' }}" href="/admin/customers/segments">
                            <i class="fas fa-users-cog me-2"></i> Customer Segments
                        </a>
                    </li>

                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">MAIL TRACKING</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/mail-court*') ? 'bg-primary rounded' : '' }}" href="/admin/mail-court">
                            <i class="fas fa-envelope me-2"></i> Court Mailings
                        </a>
                    </li>
                @endif
                
                <hr class="text-white">
                <li class="nav-item">
                    <form method="POST" action="/logout" style="display: inline;">
                        @csrf
                        <button type="submit" class="nav-link text-white btn btn-link" style="border: none; background: none; padding: 0.5rem 1rem;">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Push Notification Modal -->
    <div class="modal fade" id="pushNotificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" id="notificationHeader">
                    <h5 class="modal-title" id="notificationTitle">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="notificationMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time notification checker
        function checkForNotifications() {
            fetch('/api/check-notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(notification => {
                if (notification && notification.title) {
                    showPushNotification(notification);
                }
            })
            .catch(error => {});
        }

        function showPushNotification(notification) {
            // Update modal content
            document.getElementById('notificationTitle').textContent = notification.title;
            document.getElementById('notificationMessage').textContent = notification.message;
            
            // Set header color based on type
            const header = document.getElementById('notificationHeader');
            header.className = 'modal-header';
            switch(notification.type) {
                case 'success':
                    header.classList.add('bg-success', 'text-white');
                    break;
                case 'warning':
                    header.classList.add('bg-warning', 'text-dark');
                    break;
                case 'error':
                    header.classList.add('bg-danger', 'text-white');
                    break;
                default:
                    header.classList.add('bg-info', 'text-white');
            }
            
            // Show modal
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(document.getElementById('pushNotificationModal'));
                modal.show();
                console.log('Modal shown');
            } else {
                console.log('Bootstrap not available');
                alert(`${notification.title}: ${notification.message}`);
            }
        }

        // Check for notifications every 3 seconds
        setInterval(checkForNotifications, 3000);
        
        // Check immediately on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(checkForNotifications, 1000);
        });
    </script>
