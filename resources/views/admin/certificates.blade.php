<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certificate Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Certificate Management</h2>
            <button onclick="showCreateCertificateModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Certificate
            </button>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="state-filter" class="form-select" onchange="loadCertificates()">
                    <option value="">All States</option>
                    <option value="FL">Florida</option>
                    <option value="CA">California</option>
                    <option value="TX">Texas</option>
                    <option value="MO">Missouri</option>
                    <option value="DE">Delaware</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="status-filter" class="form-select" onchange="loadCertificates()">
                    <option value="">All Status</option>
                    <option value="generated">Generated</option>
                    <option value="submitted">Submitted</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>All Certificates</h5>
            </div>
            <div class="card-body">
                <div id="certificates-table">
                    <p>Loading certificates...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create/Edit Certificate Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="certificateModalTitle">Create Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="certificateForm">
                        <input type="hidden" id="certificateId">
                        <div class="mb-3">
                            <label class="form-label">Enrollment</label>
                            <select class="form-control" id="enrollmentId" required>
                                <option value="">Select Enrollment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="studentName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="courseName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State Code</label>
                            <select class="form-control" id="stateCode" required>
                                <option value="FL">Florida</option>
                                <option value="CA">California</option>
                                <option value="TX">Texas</option>
                                <option value="MO">Missouri</option>
                                <option value="DE">Delaware</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Completion Date</label>
                            <input type="date" class="form-control" id="completionDate" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="certificateStatus" required>
                                <option value="generated">Generated</option>
                                <option value="submitted">Submitted</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="saveCertificate()" class="btn btn-primary">Save Certificate</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Email Certificate Modal -->
    <div class="modal fade" id="emailCertificateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="emailCertificateForm">
                        <input type="hidden" id="emailCertificateId">
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="emailStudentName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Certificate Number</label>
                            <input type="text" class="form-control" id="emailCertNumber" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Recipient Email Address</label>
                            <input type="email" class="form-control" id="recipientEmail" placeholder="student@example.com" required>
                            <small class="text-muted">The certificate will be sent to this email address</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="sendCertificateEmail()" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function loadCertificates() {
            try {
                const stateFilter = document.getElementById('state-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                
                const params = new URLSearchParams();
                if (stateFilter) params.append('state_code', stateFilter);
                if (statusFilter) params.append('status', statusFilter);
                
                const response = await fetch(`/web/admin/certificates?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayCertificates(data.data || data);
                } else {
                    // Fallback to mock data for demo
                    const certificates = [
                        {
                            id: 1,
                            certificate_number: 'FL-2025-000001',
                            student_name: 'John Doe',
                            course_name: 'Florida Traffic School',
                            state_code: 'FL',
                            completion_date: new Date().toISOString(),
                            status: 'generated',
                            is_sent_to_state: false,
                            verification_hash: 'abc123def456'
                        },
                        {
                            id: 2,
                            certificate_number: 'CA-2025-000001',
                            student_name: 'Jane Smith',
                            course_name: 'California Traffic School',
                            state_code: 'CA',
                            completion_date: new Date(Date.now() - 86400000).toISOString(),
                            status: 'submitted',
                            is_sent_to_state: true,
                            verification_hash: 'xyz789uvw012'
                        }
                    ].filter(cert => {
                        return (!stateFilter || cert.state_code === stateFilter) &&
                               (!statusFilter || cert.status === statusFilter);
                    });
                    displayCertificates(certificates);
                }
            } catch (error) {
                console.error('Error loading certificates:', error);
                document.getElementById('certificates-table').innerHTML = '<p class="text-danger">Error loading certificates.</p>';
            }
        }
        
        function displayCertificates(certificates) {
            const container = document.getElementById('certificates-table');
            
            if (certificates.length === 0) {
                container.innerHTML = '<p>No certificates found.</p>';
                return;
            }
            
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Certificate #</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>State</th>
                                <th>Completion Date</th>
                                <th>Status</th>
                                <th>State Submission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${certificates.map(cert => `
                                <tr>
                                    <td>${cert.dicds_certificate_number || cert.certificate_number || 'N/A'}</td>
                                    <td>${cert.student_name}</td>
                                    <td>${cert.course_name}</td>
                                    <td><span class="badge bg-primary">${cert.state || cert.state_code || 'FL'}</span></td>
                                    <td>${new Date(cert.completion_date).toLocaleDateString()}</td>
                                    <td><span class="badge bg-${cert.status === 'confirmed' ? 'success' : cert.status === 'submitted' ? 'info' : 'warning'}">${cert.status || 'generated'}</span></td>
                                    <td>
                                        <span class="badge bg-${cert.is_sent_to_student || cert.is_sent_to_state ? 'success' : 'secondary'}">
                                            ${cert.is_sent_to_student || cert.is_sent_to_state ? 'Sent' : 'Not Sent'}
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick="editCertificate(${cert.id})" class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button onclick="viewCertificate('${cert.verification_hash}')" class="btn btn-sm btn-outline-info">View</button>
                                        <button onclick="downloadCertificate(${cert.id})" class="btn btn-sm btn-outline-success">PDF</button>
                                        <button onclick="emailCertificate(${cert.id})" class="btn btn-sm btn-outline-warning">Email</button>
                                        ${!(cert.is_sent_to_student || cert.is_sent_to_state) ? `<button onclick="submitToState(${cert.id})" class="btn btn-sm btn-outline-secondary">Submit</button>` : ''}
                                        <button onclick="deleteCertificate(${cert.id})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        function viewCertificate(verificationHash) {
            window.open(`/certificates/${verificationHash}/verify`, '_blank');
        }
        
        function downloadCertificate(certId) {
            window.open(`/web/admin/certificates/${certId}/download`, '_blank');
        }
        
        function submitToState(certId) {
            if (confirm('Submit this certificate to the state system?')) {
                fetch(`/web/admin/certificates/${certId}/submit-to-state`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Certificate submitted to state successfully!');
                    loadCertificates(); // Reload to update status
                })
                .catch(error => {
                    console.error('Error submitting certificate:', error);
                    alert('Error submitting certificate to state');
                });
            }
        }
        
        function showCreateCertificateModal() {
            document.getElementById('certificateModalTitle').textContent = 'Create Certificate';
            document.getElementById('certificateForm').reset();
            document.getElementById('certificateId').value = '';
            document.getElementById('completionDate').value = new Date().toISOString().split('T')[0];
            new bootstrap.Modal(document.getElementById('certificateModal')).show();
        }
        
        function editCertificate(certificateId) {
            fetch(`/web/admin/certificates/${certificateId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(certificate => {
                document.getElementById('certificateModalTitle').textContent = 'Edit Certificate';
                document.getElementById('certificateId').value = certificate.id;
                document.getElementById('enrollmentId').value = certificate.enrollment_id || '';
                document.getElementById('studentName').value = certificate.student_name;
                document.getElementById('courseName').value = certificate.course_name;
                document.getElementById('stateCode').value = certificate.state || certificate.state_code || 'FL';
                const completionDate = certificate.completion_date ? (certificate.completion_date.split ? certificate.completion_date.split('T')[0] : certificate.completion_date) : '';
                document.getElementById('completionDate').value = completionDate;
                document.getElementById('certificateStatus').value = certificate.status || 'generated';
                new bootstrap.Modal(document.getElementById('certificateModal')).show();
            })
            .catch(error => {
                console.error('Error loading certificate:', error);
                alert('Failed to load certificate details');
            });
        }
        
        function saveCertificate() {
            const certificateId = document.getElementById('certificateId').value;
            const isEdit = certificateId !== '';
            const url = isEdit ? `/web/admin/certificates/${certificateId}` : '/web/admin/certificates';
            const method = isEdit ? 'PUT' : 'POST';
            
            const data = {
                enrollment_id: document.getElementById('enrollmentId').value,
                student_name: document.getElementById('studentName').value,
                course_name: document.getElementById('courseName').value,
                state_code: document.getElementById('stateCode').value,
                completion_date: document.getElementById('completionDate').value,
                status: document.getElementById('certificateStatus').value
            };
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('certificateModal')).hide();
                alert('Certificate saved successfully!');
                loadCertificates();
            })
            .catch(error => {
                console.error('Error saving certificate:', error);
                alert('Error saving certificate');
            });
        }
        
        function deleteCertificate(certificateId) {
            if (confirm('Are you sure you want to delete this certificate?')) {
                fetch(`/web/admin/certificates/${certificateId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Certificate deleted successfully!');
                    loadCertificates();
                })
                .catch(error => {
                    console.error('Error deleting certificate:', error);
                    alert('Error deleting certificate');
                });
            }
        }
        
        function emailCertificate(certificateId) {
            // Fetch certificate details to get student email
            fetch(`/web/admin/certificates/${certificateId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(certificate => {
                // Populate modal with certificate info
                document.getElementById('emailCertificateId').value = certificate.id;
                document.getElementById('emailStudentName').value = certificate.student_name;
                document.getElementById('emailCertNumber').value = certificate.dicds_certificate_number || certificate.certificate_number || 'N/A';
                
                // Try to get student email from enrollment
                let studentEmail = '';
                if (certificate.enrollment && certificate.enrollment.user) {
                    studentEmail = certificate.enrollment.user.email;
                } else if (certificate.user) {
                    studentEmail = certificate.user.email;
                }
                
                document.getElementById('recipientEmail').value = studentEmail;
                
                // Show modal
                new bootstrap.Modal(document.getElementById('emailCertificateModal')).show();
            })
            .catch(error => {
                console.error('Error loading certificate:', error);
                alert('Failed to load certificate details');
            });
        }
        
        function sendCertificateEmail() {
            const certificateId = document.getElementById('emailCertificateId').value;
            const email = document.getElementById('recipientEmail').value;
            
            if (!email) {
                alert('Please enter an email address');
                return;
            }
            
            // Disable button and show loading
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            fetch(`/web/admin/certificates/${certificateId}/email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('emailCertificateModal')).hide();
                alert('✓ Certificate emailed successfully to ' + email);
            })
            .catch(error => {
                console.error('Error emailing certificate:', error);
                alert('Error emailing certificate');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
        
        async function loadEnrollments() {
            try {
                const response = await fetch('/api/enrollments');
                const enrollments = await response.json();
                const select = document.getElementById('enrollmentId');
                select.innerHTML = '<option value="">Select Enrollment</option>';
                enrollments.forEach(enrollment => {
                    const option = document.createElement('option');
                    option.value = enrollment.id;
                    option.textContent = `#${enrollment.id} - ${enrollment.user?.name || 'Unknown'} - ${enrollment.course?.title || 'Unknown Course'}`;
                    option.dataset.studentName = enrollment.user?.name || '';
                    option.dataset.courseName = enrollment.course?.title || '';
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading enrollments:', error);
            }
        }
        
        document.getElementById('enrollmentId')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.dataset.studentName) {
                document.getElementById('studentName').value = selectedOption.dataset.studentName;
            }
            if (selectedOption.dataset.courseName) {
                document.getElementById('courseName').value = selectedOption.dataset.courseName;
            }
        });
        
        loadCertificates();
        loadEnrollments();
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @vite(['resources/js/app.js'])
</body>
</html>
