<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .certificate {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 60px;
            background: white;
            border: 20px solid #1e3a8a;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .seal {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }
        h1 {
            font-size: 48px;
            color: #1e3a8a;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .subtitle {
            font-size: 24px;
            color: #666;
            margin: 10px 0;
        }
        .content {
            text-align: center;
            margin: 40px 0;
            line-height: 2;
        }
        .student-name {
            font-size: 36px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 2px solid #1e3a8a;
            display: inline-block;
            padding: 10px 40px;
            margin: 20px 0;
        }
        .details {
            margin: 40px 0;
            text-align: left;
            font-size: 14px;
        }
        .detail-row {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
        }
        .signature-line {
            text-align: center;
            width: 250px;
        }
        .line {
            border-top: 2px solid #000;
            margin: 40px 0 10px;
        }
        .cert-number {
            text-align: center;
            margin-top: 40px;
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <div class="seal">
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#1e3a8a"/>
                    <text x="50" y="55" text-anchor="middle" fill="white" font-size="20" font-weight="bold">FL</text>
                </svg>
            </div>
            <h1>Certificate of Completion</h1>
            <div class="subtitle">Florida Basic Driver Improvement Course</div>
        </div>
        
        <div class="content">
            <p style="font-size: 18px;">This certifies that</p>
            <div class="student-name">{{ $certificate->student_name }}</div>
            <p style="font-size: 18px;">has successfully completed the</p>
            <p style="font-size: 20px; font-weight: bold;">{{ $certificate->course_name }}</p>
            <p style="font-size: 16px;">on {{ $certificate->completion_date->format('F d, Y') }}</p>
        </div>
        
        <div class="details">
            <div class="detail-row">
                <span><strong>Driver License:</strong> {{ $certificate->driver_license_number }}</span>
                <span><strong>Date of Birth:</strong> {{ $certificate->student_date_of_birth->format('m/d/Y') }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Citation Number:</strong> {{ $certificate->citation_number }}</span>
                <span><strong>Citation County:</strong> {{ $certificate->citation_county }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Final Exam Score:</strong> {{ number_format($certificate->final_exam_score, 1) }}%</span>
                <span><strong>Traffic School Due Date:</strong> {{ $certificate->traffic_school_due_date->format('m/d/Y') }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Court:</strong> {{ $certificate->court_name }}</span>
                <span><strong>State:</strong> {{ $certificate->state }}</span>
            </div>
        </div>
        
        <div class="footer">
            <div class="signature-line">
                <div class="line"></div>
                <div>School Administrator</div>
            </div>
            <div class="signature-line">
                <div class="line"></div>
                <div>Date Issued</div>
            </div>
        </div>
        
        <div class="cert-number">
            Certificate Number: {{ $certificate->dicds_certificate_number }}<br>
            Verification Code: {{ $certificate->verification_hash }}
        </div>
    </div>
</body>
</html>
