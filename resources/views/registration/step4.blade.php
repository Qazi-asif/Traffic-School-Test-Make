<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create your Account - Step 4</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
            color: #212529;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin: 0; }
        .header p { color: #6c757d; margin: 10px 0 0 0; }
        .registration-form { background: white; padding: 40px; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .instruction { color: #6c757d; font-size: 16px; margin-bottom: 30px; text-align: center; }
        .info-section { margin-bottom: 30px; }
        .section-title { 
            color: #0d6efd; 
            font-weight: bold; 
            font-size: 18px; 
            text-align: center; 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .info-row { display: flex; margin-bottom: 8px; padding: 5px 0; }
        .info-label { color: #6c757d; width: 250px; font-size: 14px; }
        .info-value { color: #212529; font-weight: 500; }
        .terms-section { 
            margin: 30px 0; 
            text-align: center; 
            padding: 30px; 
            background: #f8f9fa; 
            border-radius: 0.375rem; 
            border: 1px solid #dee2e6;
        }
        .terms-text { color: #6c757d; margin-bottom: 20px; line-height: 1.5; }
        .name-input-row { margin: 20px 0; }
        .agreement-name-input { 
            width: 300px; 
            padding: 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            font-size: 16px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .agreement-name-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .checkbox-row { margin: 20px 0; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .checkbox-label { color: #212529; margin: 0; }
        .error-message { color: #dc3545; font-size: 14px; margin-top: 5px; display: none; }
        .form-group.error .agreement-name-input { border-color: #dc3545; }
        .button-row { display: flex; justify-content: center; gap: 20px; margin-top: 30px; }
        .btn { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            text-decoration: none; 
            display: inline-block;
            transition: background-color 0.15s ease-in-out;
            min-width: 120px;
            text-align: center;
        }
        .btn-continue { background: #516425; color: white; }
        .btn-continue:hover { background: #3d4b1c; }
        .btn-edit { background: #fd7e14; color: white; }
        .btn-edit:hover { background: #e8650e; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; }
        .footer a { color: #0d6efd; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            position: relative;
            animation: slideIn 0.3s ease-out;
        }
        
        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .modal-title {
            color: #0d6efd;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .modal-body {
            padding: 30px;
            max-height: 60vh;
            overflow-y: auto;
            line-height: 1.6;
            color: #212529;
        }
        
        .modal-body h3 {
            color: #0d6efd;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .modal-body h3:first-child {
            margin-top: 0;
        }
        
        .modal-body p {
            margin-bottom: 15px;
            text-align: justify;
        }
        
        .modal-body ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        
        .modal-body li {
            margin-bottom: 8px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 0 0 0.5rem 0.5rem;
            text-align: center;
        }
        
        .modal-footer .btn {
            min-width: 100px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Custom scrollbar for modal body */
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Form Review</h1>
            <p>Step 4 of 4 - Review and confirm your information</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 4) }}">
            @csrf
            
            @if(session('error'))
                <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div style="background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Success:</strong> {{ session('success') }}
                </div>
            @endif
            
            <div class="registration-form">
                <div class="instruction">
                    Take your time and make sure it is accurate!
                </div>
                
                <!-- Your Information Section -->
                <div class="info-section">
                    <div class="section-title">Your Information</div>
                    
                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ session('registration_step_1.email', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">First Name:</div>
                        <div class="info-value">{{ session('registration_step_1.first_name', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Last Name:</div>
                        <div class="info-value">{{ session('registration_step_1.last_name', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Mailing Address:</div>
                        <div class="info-value">{{ session('registration_step_2.mailing_address', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">City:</div>
                        <div class="info-value">{{ session('registration_step_2.city', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">State:</div>
                        <div class="info-value">{{ session('registration_step_2.state', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Zip:</div>
                        <div class="info-value">{{ session('registration_step_2.zip', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Phone:</div>
                        <div class="info-value">{{ session('registration_step_2.phone_1', '') }}-{{ session('registration_step_2.phone_2', '') }}-{{ session('registration_step_2.phone_3', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Gender:</div>
                        <div class="info-value">{{ ucfirst(session('registration_step_2.gender', '')) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Birthday:</div>
                        <div class="info-value">{{ session('registration_step_2.birth_month', '') }}/{{ session('registration_step_2.birth_day', '') }}/{{ session('registration_step_2.birth_year', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Driver License</div>
                        <div class="info-value">{{ session('registration_step_2.driver_license', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">State Issue</div>
                        <div class="info-value">{{ session('registration_step_2.license_state', '') }}</div>
                    </div>
                </div>
                
                <!-- Court Information Section -->
                @if(!session('registration_step_2.insurance_discount_only'))
                <div class="info-section">
                    <div class="section-title">Court Information</div>
                    
                    <div class="info-row">
                        <div class="info-label">Court Selected:</div>
                        <div class="info-value">{{ session('registration_step_2.court_selected', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Citation Number:</div>
                        <div class="info-value">{{ session('registration_step_2.citation_number', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Traffic school due date:</div>
                        <div class="info-value">{{ session('registration_step_2.due_month', '') }}/{{ session('registration_step_2.due_day', '') }}/{{ session('registration_step_2.due_year', '') }}</div>
                    </div>
                </div>
                @endif
                
                <!-- Personal Information Section -->
                <div class="info-section">
                    <div class="section-title">Security Questions</div>
                    
                    <div class="info-row">
                        <div class="info-label">License expiration year:</div>
                        <div class="info-value">{{ session('registration_step_3.q1', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Weight on license:</div>
                        <div class="info-value">{{ session('registration_step_3.q2', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Number of cars owned:</div>
                        <div class="info-value">{{ session('registration_step_3.q3', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Last four digits of license:</div>
                        <div class="info-value">{{ session('registration_step_3.q4', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Current age:</div>
                        <div class="info-value">{{ session('registration_step_3.q5', '') }}</div>
                    </div>
                      <div class="info-row">
                        <div class="info-label">Age driver’s license first recieved:</div>
                        <div class="info-value">{{ session('registration_step_3.q6', '') }}</div>
                    </div>
                      <div class="info-row">
                        <div class="info-label">Zip code:</div>
                        <div class="info-value">{{ session('registration_step_3.q7', '') }}</div>
                    </div>
                     <div class="info-row">
                        <div class="info-label">Year born:</div>
                        <div class="info-value">{{ session('registration_step_3.q8', '') }}</div>
                    </div>
                     <div class="info-row">
                        <div class="info-label">Hair color:</div>
                        <div class="info-value">{{ session('registration_step_3.q9', '') }}</div>
                    </div>
                     <div class="info-row">
                        <div class="info-label">Current city:</div>
                        <div class="info-value">{{ session('registration_step_3.q10', '') }}</div>
                    </div>
                </div>
                
                <!-- Terms and Conditions Section -->
                <div class="terms-section">
                    <div class="terms-text">
                        By typing your name here, you acknowledge that you have read and agree to the following
                        <a href="#" id="terms-link" style="font-weight: bold; color: #000000; text-decoration: underline;">Terms and Conditions.</a>
                    </div>
                    
                    <div class="name-input-row">
                        <div class="form-group">
                            <input type="text" name="agreement_name" class="agreement-name-input" placeholder="Type your full name here" pattern="[a-zA-Z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes allowed" required>
                            <div class="error-message">Only letters, spaces, hyphens, and apostrophes allowed</div>
                        </div>
                    </div>
                    
                    <div class="checkbox-row">
                        <input type="checkbox" id="terms_agreement" name="terms_agreement" required>
                        <label for="terms_agreement" class="checkbox-label">I agree to the terms and conditions above.</label>
                    </div>
                </div>
                
                <div class="button-row">
                    <button type="submit" class="btn btn-continue">I Accept</button>
                    <a href="{{ route('register.step', 2) }}" class="btn btn-edit">Edit</a>
                </div>
            </div>
        </form>
        
        <div class="footer">
            Have an account? <a href="/login">Sign In</a>
        </div>
    </div>
    
    <!-- Terms and Conditions Modal -->
    <div id="terms-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Terms and Conditions</h2>
                <button type="button" class="modal-close" id="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <h3>Course Tuition</h3>
                <p>A registration fee is required before you can complete the course. This fee includes all necessary course materials.</p>
                
                <h3>Course Schedule</h3>
                <p>DummiesTrafficSchool.com offers an online driver education course that is available 24 hours a day, 7 days a week. Occasional downtime may occur due to system or internet issues outside of our control.</p>
                
                <h3>Hardware and Software Requirements</h3>
                <p>To take this course, your device must meet the following minimum requirements:</p>
                <ul>
                    <li>A modern browser (Google Chrome, Firefox, or Internet Explorer 11 or newer)</li>
                    <li>Support for Java and JavaScript</li>
                    <li>Flash Player 10 or higher for some videos (Flash does not work on iPads or iPhones)</li>
                    <li>A mouse or pointing device (required) and speakers (recommended)</li>
                </ul>
                <p>After registration, your system will be checked for compatibility.</p>
                
                <h3>Course Completion and Certification</h3>
                <p>To receive a completion certificate, you must finish all required lessons, quizzes, and tests. If you need a replacement or duplicate certificate, and it's not our fault, a reissue fee may apply.</p>
                
                <h3>Cheating Policy</h3>
                <p>You must complete all course work yourself. If we find that you've cheated or misrepresented your identity:</p>
                <ul>
                    <li>You will be removed from the course without a refund.</li>
                    <li>You may face legal action or prosecution.</li>
                </ul>
                
                <h3>Course Deadline</h3>
                <p>You have to complete your course by the required deadline that the court/DMV gave you.</p>
                
                <h3>Cancellations and Refunds</h3>
                <p>You can request a full refund within 30 days of purchase if you have not received a certificate of completion or partial completion. Refunds will be processed within 30 days of your request.</p>
                
                <h3>Privacy Policy</h3>
                <p>We collect some personal information to manage your course and account. We do not sell or share your information for unrelated purposes. By enrolling, you agree to our Privacy Statement describing how your data is collected and used.</p>
                
                <h3>Surveys</h3>
                <p>After completing the course, you may be invited to complete a voluntary survey. Survey responses help us improve our courses and may be used as testimonials with your permission.</p>
                
                <h3>Accuracy of Information</h3>
                <p>You confirm that all information you provide is true and accurate. False information may result in removal from the course and potential legal liability.</p>
                
                <h3>Limitation of Liability</h3>
                <p>Our total liability to you for any issue related to the course will not exceed the amount of your registration fee.</p>
                
                <h3>Third-Party Products or Services</h3>
                <p>We may display ads, promotions, or links to other companies. We are not responsible for their products, services, or content. Any dealings with third parties are strictly between you and them.</p>
                
                <h3>Copyright and Use</h3>
                <p>All course materials — including text, videos, graphics, and software — are owned by InternetEducationalServices.com. You may only use these materials for personal study within this course. You may not copy, share, or sell any part of the course content.</p>
                
                <h3>License to Use the Course</h3>
                <p>You are granted a limited, personal, non-transferable license to use this course while you are enrolled. You may not copy, modify, or try to access the course's source code.</p>
                
                <h3>Indemnification</h3>
                <p>You agree to protect and compensate the Course Provider and its partners from any losses or claims that result from your misuse of the course or violation of these terms.</p>
                
                <h3>Governing Law</h3>
                <p>These terms are governed by the laws of the state where you are required to take the course to remove your infraction.</p>
                <p>For example, if you need to take a Texas course, the laws of Texas will apply.</p>
                <p>Any legal actions must be filed in a court located in that state.</p>
                
                <h3>General Terms</h3>
                <p>These terms make up the full agreement between you and the Course Provider.</p>
                <p>If any part of these terms is found invalid, the rest will still apply.</p>
                <p>You must notify us of any claim within one (6) months of the issue, or it will be permanently waived.</p>
                
                <p><strong>Last Updated:</strong> January 2026</p>
                <p><strong>Effective Date:</strong> January 1, 2026</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-continue" id="modal-accept">I Understand</button>
            </div>
        </div>
    </div>
    
    <script src="/js/csrf-handler.js"></script>
    <script>
        // Real-time validation for agreement name field
        document.querySelector('input[name="agreement_name"]').addEventListener('input', function(e) {
            const value = e.target.value;
            const regex = /^[a-zA-Z\s\-']*$/;
            const parent = e.target.closest('.form-group');
            const errorMsg = parent.querySelector('.error-message');
            
            if (!regex.test(value)) {
                parent.classList.add('error');
                errorMsg.style.display = 'block';
                e.target.value = value.replace(/[^a-zA-Z\s\-']/g, '');
            } else {
                parent.classList.remove('error');
                errorMsg.style.display = 'none';
            }
        });
        
        // Terms and Conditions Modal functionality
        const modal = document.getElementById('terms-modal');
        const termsLink = document.getElementById('terms-link');
        const modalClose = document.getElementById('modal-close');
        const modalAccept = document.getElementById('modal-accept');
        
        // Open modal when terms link is clicked
        termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        });
        
        // Close modal when X button is clicked
        modalClose.addEventListener('click', function() {
            closeModal();
        });
        
        // Close modal when "I Understand" button is clicked
        modalAccept.addEventListener('click', function() {
            closeModal();
        });
        
        // Close modal when clicking outside the modal content
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeModal();
            }
        });
        
        // Function to close modal
        function closeModal() {
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restore background scrolling
        }
        
        // Smooth scroll to top of modal when opened
        termsLink.addEventListener('click', function() {
            setTimeout(() => {
                const modalBody = document.querySelector('.modal-body');
                modalBody.scrollTop = 0;
            }, 100);
        });
    </script>
</body>
</html>
