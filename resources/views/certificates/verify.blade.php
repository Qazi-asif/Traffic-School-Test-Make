<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certificate Verification - Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-shield-alt"></i> Certificate Verification</h2>
                        <p class="mb-0 text-muted">Verify the authenticity of a course completion certificate</p>
                    </div>
                    <div class="card-body">
                        <!-- Verification Form -->
                        <form id="verificationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="certificate_number" class="form-label">
                                            <i class="fas fa-certificate"></i> Certificate Number *
                                        </label>
                                        <input type="text" class="form-control" id="certificate_number" name="certificate_number" 
                                               placeholder="Enter certificate number" required>
                                        <div class="form-text">Example: FL2025000001, MO2025000001</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="student_name" class="form-label">
                                            <i class="fas fa-user"></i> Student Name (Optional)
                                        </label>
                                        <input type="text" class="form-control" id="student_name" name="student_name" 
                                               placeholder="Enter student name">
                                        <div class="form-text">For additional verification</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search"></i> Verify Certificate
                                </button>
                            </div>
                        </form>

                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="text-center mt-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Verifying certificate...</p>
                        </div>

                        <!-- Verification Results -->
                        <div id="verificationResults" class="mt-4" style="display: none;">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> About Certificate Verification</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Who Can Verify Certificates?</h6>
                                <ul>
                                    <li>Courts and legal authorities</li>
                                    <li>Insurance companies</li>
                                    <li>Employers</li>
                                    <li>Students and certificate holders</li>
                                    <li>Educational institutions</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>What Information is Verified?</h6>
                                <ul>
                                    <li>Certificate authenticity</li>
                                    <li>Student identity</li>
                                    <li>Course completion date</li>
                                    <li>Final exam score</li>
                                    <li>State compliance status</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Security Notice:</strong> All certificate verifications are logged for security purposes. 
                            Only enter certificate numbers you are authorized to verify.
                        </div>
                    </div>
                </div>

                <!-- State-Specific Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-map-marker-alt"></i> State-Specific Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="badge bg-primary fs-6 mb-2">FL</div>
                                    <h6>Florida</h6>
                                    <small>DICDS integrated<br>FLHSMV approved</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="badge bg-success fs-6 mb-2">MO</div>
                                    <h6>Missouri</h6>
                                    <small>Form 4444 compliant<br>DOR approved</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="badge bg-warning fs-6 mb-2">TX</div>
                                    <h6>Texas</h6>
                                    <small>TDLR approved<br>Court accepted</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="badge bg-info fs-6 mb-2">DE</div>
                                    <h6>Delaware</h6>
                                    <small>DMV approved<br>Point reduction</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const certificateNumber = document.getElementById('certificate_number').value.trim();
            const studentName = document.getElementById('student_name').value.trim();
            
            if (!certificateNumber) {
                alert('Please enter a certificate number');
                return;
            }
            
            // Show loading spinner
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('verificationResults').style.display = 'none';
            
            // Make verification request
            fetch('/api/certificates/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    certificate_number: certificateNumber,
                    student_name: studentName
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingSpinner').style.display = 'none';
                displayVerificationResults(data);
            })
            .catch(error => {
                document.getElementById('loadingSpinner').style.display = 'none';
                displayError('An error occurred while verifying the certificate. Please try again.');
                console.error('Error:', error);
            });
        });
        
        function displayVerificationResults(data) {
            const resultsDiv = document.getElementById('verificationResults');
            
            if (data.valid) {
                resultsDiv.innerHTML = `
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0">Certificate Verified ✓</h4>
                                <p class="mb-0">This certificate is valid and authentic</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Certificate Details:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Certificate Number:</strong> ${data.certificate.certificate_number}</li>
                                    <li><strong>Student Name:</strong> ${data.certificate.student_name}</li>
                                    <li><strong>Course:</strong> ${data.certificate.course_name}</li>
                                    <li><strong>State:</strong> ${data.certificate.state_code}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Completion Details:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Completion Date:</strong> ${data.certificate.completion_date}</li>
                                    <li><strong>Final Exam Score:</strong> ${data.certificate.final_exam_score}</li>
                                    <li><strong>Verified:</strong> ${new Date().toLocaleDateString()}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-times-circle fa-2x text-danger me-3"></i>
                            <div>
                                <h4 class="mb-0">Certificate Not Found ✗</h4>
                                <p class="mb-0">${data.message}</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Possible reasons:</h6>
                            <ul>
                                <li>Certificate number was entered incorrectly</li>
                                <li>Certificate has not been issued yet</li>
                                <li>Certificate has been revoked or expired</li>
                                <li>Student name does not match (if provided)</li>
                            </ul>
                            
                            <p class="mb-0">
                                <strong>Need help?</strong> Contact us at 
                                <a href="mailto:support@trafficschool.com">support@trafficschool.com</a>
                            </p>
                        </div>
                    </div>
                `;
            }
            
            resultsDiv.style.display = 'block';
        }
        
        function displayError(message) {
            const resultsDiv = document.getElementById('verificationResults');
            resultsDiv.innerHTML = `
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
                        <div>
                            <h4 class="mb-0">Verification Error</h4>
                            <p class="mb-0">${message}</p>
                        </div>
                    </div>
                </div>
            `;
            resultsDiv.style.display = 'block';
        }
    </script>
</body>
</html>