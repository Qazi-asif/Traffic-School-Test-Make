<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Certificate Email</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">Test Certificate Email</h1>
            
            <div id="message" class="hidden mb-4 p-4 rounded"></div>

            <form id="testForm" class="space-y-4">
                <div>
                    <label for="enrollment_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Enrollment *
                    </label>
                    <select 
                        id="enrollment_id" 
                        name="enrollment_id" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Loading enrollments...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select an enrollment to test certificate email</p>
                </div>

                <div id="enrollmentDetails" class="hidden p-4 bg-blue-50 rounded-md text-sm">
                    <h3 class="font-semibold text-blue-900 mb-2">Enrollment Details:</h3>
                    <div class="space-y-1 text-blue-800">
                        <p><strong>Student:</strong> <span id="detail-student"></span></p>
                        <p><strong>Email:</strong> <span id="detail-email"></span></p>
                        <p><strong>Course:</strong> <span id="detail-course"></span></p>
                        <p><strong>State:</strong> <span id="detail-state"></span></p>
                        <p><strong>Status:</strong> <span id="detail-status"></span></p>
                        <p><strong>Completed:</strong> <span id="detail-completed"></span></p>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Test Email (Optional)
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Leave empty to use student's email"
                    >
                    <p class="text-xs text-gray-500 mt-1">If empty, will send to the student's registered email</p>
                </div>

                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Send Test Certificate Email
                </button>
            </form>

            <div class="mt-6 p-4 bg-gray-50 rounded-md">
                <h2 class="text-sm font-semibold text-gray-700 mb-2">How to use:</h2>
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                    <li>Select an enrollment from the dropdown</li>
                    <li>Review the enrollment details</li>
                    <li>Optionally enter a test email address (or leave empty to use student's email)</li>
                    <li>Click "Send Test Certificate Email"</li>
                    <li>Check the response and your email inbox</li>
                </ol>
                <p class="text-xs text-gray-500 mt-3">
                    <strong>Note:</strong> This will create a certificate if one doesn't exist, and send an email with the PDF attachment. The certificate number will be state-specific (FL-2026-XXXXXX, MO-2026-XXXXXX, etc.)
                </p>
            </div>
        </div>
    </div>

    <script>
        let enrollments = [];
        const form = document.getElementById('testForm');
        const submitBtn = document.getElementById('submitBtn');
        const messageDiv = document.getElementById('message');
        const enrollmentSelect = document.getElementById('enrollment_id');
        const enrollmentDetails = document.getElementById('enrollmentDetails');

        // Load enrollments on page load
        async function loadEnrollments() {
            try {
                const response = await fetch('/api/test-enrollments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                enrollments = await response.json();
                
                enrollmentSelect.innerHTML = '<option value="">-- Select an enrollment --</option>';
                
                enrollments.forEach(enrollment => {
                    const option = document.createElement('option');
                    option.value = enrollment.id;
                    option.textContent = `#${enrollment.id} - ${enrollment.user_name} - ${enrollment.course_name} (${enrollment.state})`;
                    option.dataset.enrollment = JSON.stringify(enrollment);
                    enrollmentSelect.appendChild(option);
                });
            } catch (error) {
                enrollmentSelect.innerHTML = '<option value="">Error loading enrollments</option>';
                showMessage('error', `<strong>Error:</strong> Failed to load enrollments: ${error.message}`);
            }
        }

        // Show enrollment details when selected
        enrollmentSelect.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            
            if (selectedOption.value) {
                const enrollment = JSON.parse(selectedOption.dataset.enrollment);
                
                document.getElementById('detail-student').textContent = enrollment.user_name;
                document.getElementById('detail-email').textContent = enrollment.user_email;
                document.getElementById('detail-course').textContent = enrollment.course_name;
                document.getElementById('detail-state').textContent = enrollment.state;
                document.getElementById('detail-status').textContent = enrollment.status;
                document.getElementById('detail-completed').textContent = enrollment.completed_at;
                
                enrollmentDetails.classList.remove('hidden');
            } else {
                enrollmentDetails.classList.add('hidden');
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const enrollmentId = document.getElementById('enrollment_id').value;
            const email = document.getElementById('email').value;

            if (!enrollmentId) {
                showMessage('error', '<strong>Error:</strong> Please select an enrollment');
                return;
            }

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            messageDiv.classList.add('hidden');

            try {
                const response = await fetch('/test-certificate-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        enrollment_id: enrollmentId,
                        email: email || null
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('success', `
                        <strong>Success!</strong> Certificate email sent successfully.<br>
                        <strong>Certificate Number:</strong> ${data.data.certificate_number}<br>
                        <strong>State:</strong> ${data.data.state}<br>
                        <strong>Sent to:</strong> ${data.data.sent_to}<br>
                        <strong>Student:</strong> ${data.data.student_name}<br>
                        <strong>Course:</strong> ${data.data.course_name}
                    `);
                } else {
                    showMessage('error', `<strong>Error:</strong> ${data.error || 'Unknown error occurred'}`);
                }
            } catch (error) {
                showMessage('error', `<strong>Error:</strong> ${error.message}`);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Test Certificate Email';
            }
        });

        function showMessage(type, message) {
            messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
            
            if (type === 'success') {
                messageDiv.classList.add('bg-green-100', 'text-green-800');
            } else {
                messageDiv.classList.add('bg-red-100', 'text-red-800');
            }
            
            messageDiv.innerHTML = message;
        }

        // Load enrollments when page loads
        loadEnrollments();
    </script>
</body>
</html>
