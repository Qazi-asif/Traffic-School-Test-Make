<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Florida Certificate Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h3><i class="fas fa-shield-alt"></i> Florida Certificate Verification</h3>
                    </div>
                    <div class="card-body">
                        <form id="verificationForm">
                            <div class="mb-3">
                                <label class="form-label">Certificate Number</label>
                                <input id="certificateNumber" type="text" class="form-control" placeholder="Enter DICDS certificate number" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Verified By (Optional)</label>
                                <input id="verifiedBy" type="text" class="form-control" placeholder="Your name or organization">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Verify Certificate
                            </button>
                        </form>

                        <!-- Verification Result -->
                        <div id="verificationResult" class="mt-4" style="display: none;">
                            <!-- Results will be inserted here -->
                        </div>

                        <!-- Information -->
                        <div class="mt-4">
                            <h6>About Certificate Verification</h6>
                            <ul class="small text-muted">
                                <li>This system verifies certificates issued by Florida-approved traffic schools</li>
                                <li>Enter the DICDS certificate number exactly as shown on the certificate</li>
                                <li>All verification attempts are logged for security purposes</li>
                                <li>For questions, contact the issuing traffic school directly</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('verificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const certificateNumber = document.getElementById('certificateNumber').value;
            const verifiedBy = document.getElementById('verifiedBy').value;
            const resultDiv = document.getElementById('verificationResult');
            
            try {
                const response = await fetch('/api/certificates/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        certificate_number: certificateNumber,
                        verified_by: verifiedBy
                    })
                });
                
                const result = await response.json();
                
                if (result.valid) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Certificate Valid</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Student Name:</strong> ${result.certificate.student_name}<br>
                                    <strong>Course:</strong> ${result.certificate.course_name}<br>
                                    <strong>Completion Date:</strong> ${new Date(result.certificate.completion_date).toLocaleDateString()}
                                </div>
                                <div class="col-md-6">
                                    <strong>Certificate Number:</strong> ${result.certificate.dicds_certificate_number}<br>
                                    <strong>Final Score:</strong> ${result.certificate.final_exam_score}%<br>
                                    <strong>Status:</strong> <span class="badge bg-success">Valid</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-times-circle"></i> Certificate Not Found</h5>
                            <p>The certificate number you entered could not be verified. Please check the number and try again.</p>
                        </div>
                    `;
                }
                
                resultDiv.style.display = 'block';
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Verification Error</h5>
                        <p>An error occurred while verifying the certificate. Please try again later.</p>
                    </div>
                `;
                resultDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>
