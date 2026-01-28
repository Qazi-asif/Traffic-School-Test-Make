<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            margin: 0;
            size: 8.5in 11in;
        }
        
        body {
            margin: 0;
            padding: 20px;
            font-family: "Times New Roman", serif;
            background: white;
            color: #000;
        }
        
        .certificate-container {
            width: 100%;
            height: 100vh;
            position: relative;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border: 8px solid #2c3e50;
            box-sizing: border-box;
        }
        
        .certificate-header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 3px solid #34495e;
            margin-bottom: 40px;
        }
        
        .certificate-title {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .certificate-subtitle {
            font-size: 18px;
            color: #7f8c8d;
            margin: 10px 0 0 0;
        }
        
        .certificate-body {
            text-align: center;
            padding: 0 60px;
        }
        
        .completion-text {
            font-size: 24px;
            margin: 40px 0;
            line-height: 1.6;
        }
        
        .student-name {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #34495e;
            display: inline-block;
            padding: 10px 40px;
            margin: 20px 0;
        }
        
        .course-info {
            font-size: 20px;
            margin: 30px 0;
            color: #34495e;
        }
        
        .certificate-footer {
            position: absolute;
            bottom: 60px;
            left: 60px;
            right: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .signature-section {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            border-top: 2px solid #34495e;
            width: 200px;
            margin: 0 auto 10px auto;
        }
        
        .state-seal {
            width: 96px;
            height: 96px;
            border: 2px solid #34495e;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 4px;
        }
        
        .certificate-number {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .completion-date {
            font-size: 16px;
            color: #7f8c8d;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-number">
            Certificate #: {{ $certificate->certificate_number ?? "CERT-" . date("Y") . "-" . str_pad(($certificate->id ?? 1), 6, "0", STR_PAD_LEFT) }}
        </div>
        
        <div class="certificate-header">
            <h1 class="certificate-title">Certificate of Completion</h1>
            <p class="certificate-subtitle">Traffic Safety Education Program</p>
        </div>
        
        <div class="certificate-body">
            <div class="completion-text">
                This certifies that
            </div>
            
            <div class="student-name">
                {{ $certificate->student_name ?? "Student Name" }}
            </div>
            
            <div class="completion-text">
                has successfully completed the
            </div>
            
            <div class="course-info">
                <strong>{{ $certificate->course_title ?? "Traffic Safety Course" }}</strong>
                <br>
                {{ $certificate->course_description ?? "State-approved traffic safety education program" }}
            </div>
            
            <div class="completion-date">
                Completed on: {{ $certificate->completion_date ? date("F j, Y", strtotime($certificate->completion_date)) : date("F j, Y") }}
            </div>
        </div>
        
        <div class="certificate-footer">
            <div class="signature-section">
                <div class="signature-line"></div>
                <div>Instructor Signature</div>
            </div>
            
            @if(isset($stateCode) && $stateCode)
                <div class="state-seal-container">
                    <img src="{{ $sealUrl ?? "/images/state-stamps/" . strtoupper($stateCode) . "-seal.png" }}" 
                         alt="{{ $stateCode }} State Seal" 
                         class="state-seal"
                         onerror="this.style.display='none'">
                </div>
            @endif
            
            <div class="signature-section">
                <div class="signature-line"></div>
                <div>School Administrator</div>
            </div>
        </div>
    </div>
</body>
</html>