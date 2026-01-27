<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Florida Certificate Management</title>
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
    
    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); margin-top: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Florida Certificate Management</h2>
            <button class="btn btn-primary" onclick="showGenerateModal()">
                <i class="fas fa-certificate"></i> Generate Certificate
            </button>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <input id="searchInput" type="text" class="form-control" placeholder="Search by name or certificate number...">
            </div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="sent">Sent to Student</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-secondary" onclick="loadCertificates()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <div id="certificates-table" class="table-responsive">
            <p>Loading certificates...</p>
        </div>
    </div>

    <!-- Generate Certificate Modal -->
    <div class="modal fade" id="generateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Florida Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="generateForm">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Enrollment ID</label>
                                <input id="enrollmentId" type="number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DICDS Certificate Number</label>
                                <input id="dicdsNumber" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Final Exam Score (%)</label>
                                <input id="examScore" type="number" min="0" max="100" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Citation Number</label>
                                <input id="citationNumber" type="text" maxlength="7" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Citation County</label>
                                <input id="citationCounty" type="text" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Traffic School Due Date</label>
                                <input id="dueDate" type="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Court Name</label>
                            <input id="courtName" type="text" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="generateCertificate()">Generate Certificate</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let certificates = [];
        
        async function loadCertificates() {
            console.log('Loading certificates from API...');
            try {
                const response = await fetch('/api/florida-certificates', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Response status:', response.status);
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('API Response:', result);
                    
                    certificates = result.data || result;
                    console.log('Certificates array:', certificates);
                    
                    displayCertificates();
                } else {
                    console.error('API Error:', response.status);
                    document.getElementById('certificates-table').innerHTML = '<p class="text-danger">Error loading certificates.</p>';
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                document.getElementById('certificates-table').innerHTML = '<p class="text-danger">Error loading certificates: ' + error.message + '</p>';
            }
        }
        
        function displayCertificates() {
            const container = document.getElementById('certificates-table');
            
            if (!certificates || certificates.length === 0) {
                container.innerHTML = '<p>No certificates found. <button class="btn btn-primary" onclick="showGenerateModal()">Generate First Certificate</button></p>';
                return;
            }
            
            container.innerHTML = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>DICDS Number</th>
                            <th>Course</th>
                            <th>Score</th>
                            <th>Generated</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${certificates.map(cert => `
                            <tr>
                                <td>${cert.student_name}</td>
                                <td>${cert.dicds_certificate_number}</td>
                                <td>${cert.course_name}</td>
                                <td>${cert.final_exam_score}%</td>
                                <td>${new Date(cert.generated_at).toLocaleDateString()}</td>
                                <td>
                                    <span class="${cert.is_sent_to_student ? 'badge bg-success' : 'badge bg-warning'}">
                                        ${cert.is_sent_to_student ? 'Sent' : 'Pending'}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="viewCertificate(${cert.id})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success me-1" onclick="downloadCertificate(${cert.id})">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="emailCertificate(${cert.id})" ${cert.is_sent_to_student ? 'disabled' : ''}>
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function showGenerateModal() {
            new bootstrap.Modal(document.getElementById('generateModal')).show();
        }
        
        async function generateCertificate() {
            const formData = {
                dicds_certificate_number: document.getElementById('dicdsNumber').value,
                final_exam_score: document.getElementById('examScore').value,
                citation_number: document.getElementById('citationNumber').value,
                citation_county: document.getElementById('citationCounty').value,
                traffic_school_due_date: document.getElementById('dueDate').value,
                court_name: document.getElementById('courtName').value
            };
            
            const enrollmentId = document.getElementById('enrollmentId').value;
            
            try {
                const response = await fetch(`/api/enrollments/${enrollmentId}/generate-certificate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('generateModal')).hide();
                    loadCertificates();
                    alert('Certificate generated successfully!');
                } else {
                    alert('Error generating certificate');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function viewCertificate(id) {
            window.open(`/api/florida-certificates/${id}/view`, '_blank');
        }
        
        function downloadCertificate(id) {
            window.location.href = `/api/florida-certificates/${id}/download`;
        }
        
        async function emailCertificate(id) {
            try {
                const response = await fetch(`/api/florida-certificates/${id}/send-email`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    loadCertificates();
                    alert('Certificate emailed successfully!');
                } else {
                    alert('Error sending email');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadCertificates();
        });
    </script>
</body>
</html>
