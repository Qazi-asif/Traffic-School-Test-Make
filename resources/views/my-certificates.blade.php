<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Certificates</title>
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
        <h2 class="mb-4">My Certificates</h2>

        <div id="certificates-container">
            <div class="row" id="certificates-list">
                <!-- Certificates will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        async function loadCertificates() {
            try {
                console.log('Starting to load certificates...');
                
                const response = await fetch('/api/my-certificates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                if (!response.ok) {
                    throw new Error('Failed to load certificates');
                }
                
                const data = await response.json();
                console.log('API Response:', data);
                
                // Handle debug response format
                const certificates = data.certificates || data;
                console.log('Certificates array:', certificates);
                console.log('Is array?', Array.isArray(certificates));
                console.log('Length:', certificates.length);
                
                const container = document.getElementById('certificates-list');
                
                if (!Array.isArray(certificates) || certificates.length === 0) {
                    console.log('No certificates found, showing empty message');
                    container.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You don't have any certificates yet. Complete a course to earn your first certificate!
                            </div>
                        </div>
                    `;
                    return;
                }
                
                console.log('Rendering certificates...');
                container.innerHTML = certificates.map(cert => `
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-certificate text-primary"></i> ${cert.course_name || 'Certificate'}
                                </h5>
                                <p class="card-text">
                                    <strong>Certificate Number:</strong> ${cert.dicds_certificate_number || 'N/A'}<br>
                                    <strong>Completion Date:</strong> ${cert.completion_date ? new Date(cert.completion_date).toLocaleDateString() : 'N/A'}<br>
                                    <strong>Final Score:</strong> ${cert.final_exam_score || 'N/A'}%<br>
                                    <strong>Status:</strong> <span class="badge bg-success">Completed</span>
                                </p>
                                <div class="d-flex gap-2">
                                    <button onclick="downloadCertificateDirectly(${cert.id})" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Download PDF
                                    </button>
                                    <button onclick="emailCertificate(${cert.id})" class="btn btn-success btn-sm">
                                        <i class="fas fa-envelope"></i> Email Certificate
                                    </button>
                                    <a href="/certificates/verify/${cert.verification_hash}" class="btn btn-secondary btn-sm" target="_blank">
                                        <i class="fas fa-check-circle"></i> Verify
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                
            } catch (error) {
                console.error('Error loading certificates:', error);
                document.getElementById('certificates-list').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Unable to load certificates at this time. Please try again later.
                        </div>
                    </div>
                `;
            }
        }

        async function downloadCertificateDirectly(certificateId) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
            button.disabled = true;
            
            try {
                // Use GET request to download certificate PDF
                const response = await fetch(`/api/certificates/${certificateId}/download`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/pdf',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `certificate-${certificateId}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showAlert('Certificate PDF downloaded successfully!', 'success');
                } else {
                    const errorData = await response.text();
                    console.error('Download error:', errorData);
                    showAlert('Error downloading certificate. Please try again.', 'danger');
                }
            } catch (error) {
                console.error('Download error:', error);
                showAlert('Error downloading certificate. Please try again.', 'danger');
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        async function emailCertificate(certificateId) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            button.disabled = true;
            
            try {
                const response = await fetch(`/api/certificates/${certificateId}/email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (response.ok && data.message) {
                    showAlert('Certificate emailed successfully!', 'success');
                } else {
                    showAlert(data.error || 'Failed to email certificate', 'danger');
                }
            } catch (error) {
                console.error('Email error:', error);
                showAlert('Error sending email. Please try again.', 'danger');
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        loadCertificates();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <x-footer />
</body>
</html>
