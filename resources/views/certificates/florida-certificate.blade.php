<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 10px solid #0066cc;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .certificate-title {
            font-size: 36px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
        }
        .certificate-subtitle {
            font-size: 18px;
            color: #666;
        }
        .certificate-body {
            margin: 30px 0;
        }
        .student-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            border-bottom: 2px solid #0066cc;
        }
        .certificate-details {
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .certificate-footer {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #0066cc;
        }
        .verification-code {
            font-family: monospace;
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 text-center">
        <button onclick="window.print()" class="btn btn-primary">Print Certificate</button>
        <a href="/api/florida-certificates/{{ $certificate->id }}/download" class="btn btn-success">Download PDF</a>
    </div>

    <div class="certificate-container">
        <div class="certificate-header">
            <div class="certificate-title">CERTIFICATE OF COMPLETION</div>
            <div class="certificate-subtitle">Florida Traffic School</div>
        </div>

        <div class="certificate-body">
            <p class="text-center">This certifies that</p>
            <div class="student-name">{{ $certificate->student_name }}</div>
            <p class="text-center">has successfully completed</p>
            <h4 class="text-center" style="color: #0066cc;">{{ $certificate->course_name }}</h4>

            <div class="certificate-details">
                <div class="detail-row">
                    <span class="detail-label">Certificate Number:</span>
                    <span class="detail-value">{{ $certificate->dicds_certificate_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Completion Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($certificate->completion_date)->format('F d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Final Exam Score:</span>
                    <span class="detail-value">{{ $certificate->final_exam_score }}%</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">State:</span>
                    <span class="detail-value">{{ $certificate->state }}</span>
                </div>
                @if($certificate->driver_license_number)
                <div class="detail-row">
                    <span class="detail-label">Driver License:</span>
                    <span class="detail-value">{{ $certificate->driver_license_number }}</span>
                </div>
                @endif
                @if($certificate->citation_number)
                <div class="detail-row">
                    <span class="detail-label">Citation Number:</span>
                    <span class="detail-value">{{ $certificate->citation_number }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="certificate-footer">
            <p><strong>Issued on {{ \Carbon\Carbon::parse($certificate->generated_at)->format('F d, Y') }}</strong></p>
            <div class="verification-code">
                Verification Code: {{ $certificate->verification_hash }}<br>
                Verify at: {{ url('/certificates/' . $certificate->verification_hash . '/verify') }}
            </div>
        </div>
    </div>
</body>
</html>
