<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4><i class="fas fa-certificate"></i> Certificate Verification</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> This certificate is valid and verified.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Certificate Number:</strong><br>
                                {{ $certificate->dicds_certificate_number }}
                            </div>
                            <div class="col-md-6">
                                <strong>Student Name:</strong><br>
                                {{ $certificate->student_name }}
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Course Name:</strong><br>
                                {{ $certificate->course_name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Completion Date:</strong><br>
                                {{ $certificate->completion_date ? $certificate->completion_date->format('F j, Y') : 'N/A' }}
                            </div>
                        </div>
                        
                        @if($certificate->final_exam_score)
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Final Exam Score:</strong><br>
                                {{ $certificate->final_exam_score }}%
                            </div>
                        </div>
                        @endif
                        
                        <hr>
                        
                        <div class="text-muted small">
                            <strong>Verification Hash:</strong> {{ $certificate->verification_hash }}<br>
                            <strong>Verified on:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
