<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Florida Certificate - {{ $certificate->dicds_certificate_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 3px solid #0066cc;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Times New Roman', serif;
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .certificate-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
        }
        .certificate-subtitle {
            font-size: 1.2rem;
            color: #666;
        }
        .certificate-body {
            text-align: center;
            margin: 40px 0;
        }
        .student-name {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #0066cc;
            display: inline-block;
            padding-bottom: 5px;
            margin: 20px 0;
        }
        .certificate-details {
            margin: 30px 0;
            text-align: left;
        }
        .detail-row {
            margin: 15px 0;
            font-size: 1.1rem;
        }
        .detail-label {
            font-weight: bold;
            color: #0066cc;
        }
        .certificate-footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #333;
            width: 300px;
            margin: 20px auto 10px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
        }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button class="btn btn-primary me-2" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/api/florida-certificates/{{ $certificate->id }}/download" class="btn btn-success">
            <i class="fas fa-download"></i> Download PDF
        </a>
    </div>

    <div class="certificate-container">
        <div class="certificate-header">
            <div class="certificate-title">CERTIFICATE OF COMPLETION</div>
            <div class="certificate-subtitle">State of Florida Traffic School</div>
            <div class="certificate-subtitle">Basic Driver Improvement Course</div>
        </div>

        <div class="certificate-body">
            <p style="font-size: 1.3rem; margin-bottom: 10px;">This certifies that</p>
            
            <div class="student-name">{{ $certificate->student_name }}</div>
            
            <p style="font-size: 1.2rem; margin-top: 20px;">
                has successfully completed the<br>
                <strong>{{ $certificate->course_name }}</strong>
            </p>
        </div>

        <div class="certificate-details">
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">Certificate Number:</span><br>
                        {{ $certificate->dicds_certificate_number }}
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Completion Date:</span><br>
                        {{ \Carbon\Carbon::parse($certificate->completion_date)->format('F j, Y') }}
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Final Exam Score:</span><br>
                        {{ $certificate->final_exam_score }}%
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">Citation Number:</span><br>
                        {{ $certificate->citation_number ?? 'N/A' }}
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">County:</span><br>
                        {{ $certificate->citation_county ?? 'N/A' }}
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Court:</span><br>
                        {{ $certificate->court_name ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="certificate-footer">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-line"></div>
                    <p><strong>School Administrator</strong></p>
                    <p>Florida Traffic School</p>
                </div>
                <div class="col-md-6">
                    <div class="signature-line"></div>
                    <p><strong>Date Issued</strong></p>
                    <p>{{ \Carbon\Carbon::parse($certificate->generated_at)->format('F j, Y') }}</p>
                </div>
            </div>
            
            <div style="margin-top: 30px; font-size: 0.9rem; color: #666;">
                <p><strong>Verification:</strong> This certificate can be verified online at our website using certificate number {{ $certificate->dicds_certificate_number }}</p>
                <p><em>This certificate is valid only when bearing the official seal and signature.</em></p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
