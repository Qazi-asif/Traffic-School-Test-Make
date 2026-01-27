<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background: #fff;
        }
        .certificate {
            border: 10px solid #1e3a8a;
            padding: 40px;
            text-align: center;
            min-height: 600px;
            position: relative;
        }
        .header {
            font-size: 36px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 20px;
        }
        .subheader {
            font-size: 24px;
            color: #374151;
            margin-bottom: 40px;
        }
        .student-name {
            font-size: 48px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 30px 0;
            text-decoration: underline;
        }
        .course-info {
            font-size: 18px;
            margin: 20px 0;
            line-height: 1.6;
        }
        .completion-date {
            font-size: 16px;
            margin: 30px 0;
        }
        .certificate-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #6b7280;
        }
        .seal {
            position: absolute;
            bottom: 20px;
            left: 20px;
            width: 80px;
            height: 80px;
            border: 3px solid #1e3a8a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">CERTIFICATE OF COMPLETION</div>
        <div class="subheader">Traffic School Course</div>
        
        <div style="margin: 40px 0;">
            This is to certify that
        </div>
        
        <div class="student-name">{{ $certificate->student_name }}</div>
        
        <div style="margin: 40px 0;">
            has successfully completed the requirements for
        </div>
        
        <div class="course-info">
            <strong>{{ $certificate->course_name }}</strong><br>
            State of {{ $certificate->state_code }}<br>
            Traffic Safety Education Program
        </div>
        
        <div class="completion-date">
            Completed on {{ $certificate->completion_date ? $certificate->completion_date->format('F j, Y') : 'N/A' }}<br>
            Issued on {{ $certificate->issued_at ? $certificate->issued_at->format('F j, Y') : now()->format('F j, Y') }}
        </div>
        
        <div class="seal">
            OFFICIAL<br>SEAL
        </div>
        
        <div class="certificate-number">
            Certificate No: {{ $certificate->certificate_number }}
        </div>
    </div>
</body>
</html>
