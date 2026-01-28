<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Florida ADI Certificate - {{ $certificate->certificate_number }}</title>
    <style>
        @page {
            margin: 0.5in;
            size: 8.5in 11in;
        }
        
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .certificate-container {
            width: 100%;
            min-height: 10in;
            border: 3px solid #000;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .certificate-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .cert-number {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 10px;
            text-align: center;
            border: 1px solid #000;
            padding: 5px;
        }
        
        .content-section {
            margin: 15px 0;
        }
        
        .student-info {
            text-align: center;
            margin: 20px 0;
        }
        
        .student-name {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
        }
        
        .completion-text {
            text-align: center;
            margin: 15px 0;
        }
        
        .details-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        
        .details-row {
            display: table-row;
        }
        
        .details-cell {
            display: table-cell;
            padding: 5px 10px;
            vertical-align: top;
        }
        
        .label {
            font-weight: bold;
            width: 30%;
        }
        
        .value {
            border-bottom: 1px solid #000;
            min-height: 20px;
            width: 70%;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-cell {
            display: table-cell;
            text-align: center;
            width: 50%;
            padding: 20px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 30px auto 10px auto;
        }
        
        .state-seal {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
            border: 2px solid #000;
            border-radius: 50%;
        }
        
        .footer-text {
            position: absolute;
            bottom: 120px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            font-style: italic;
        }
        
        .dicds-info {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 10px;
            border: 1px solid #000;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- DICDS Information -->
        <div class="dicds-info">
            DICDS Approved<br>
            Course ID: {{ $course->dicds_course_id ?? 'FL-ADI-001' }}
        </div>
        
        <!-- Certificate Number -->
        <div class="cert-number">
            Certificate<br>
            Number:<br>
            <strong>{{ $certificate->certificate_number }}</strong>
        </div>
        
        <!-- Header -->
        <div class="header">
            <div class="school-name">{{ config('app.name', 'TRAFFIC SCHOOL') }}</div>
            <div class="certificate-title">CERTIFICATE OF COMPLETION</div>
            <div>Florida Advanced Driver Improvement (ADI) Course</div>
        </div>
        
        <!-- Student Information -->
        <div class="student-info">
            <div>This certifies that</div>
            <div class="student-name">{{ strtoupper($certificate->student_name) }}</div>
            <div>has successfully completed the</div>
        </div>
        
        <!-- Course Information -->
        <div class="completion-text">
            <strong>12-Hour Florida Advanced Driver Improvement Course</strong><br>
            as required by Florida Statute 318.14(9) and approved by the<br>
            Florida Department of Highway Safety and Motor Vehicles
        </div>
        
        <!-- Details Grid -->
        <div class="details-grid">
            <div class="details-row">
                <div class="details-cell label">Student Address:</div>
                <div class="details-cell value">{{ $user->mailing_address ?? '' }}, {{ $user->city ?? '' }}, {{ $user->state ?? '' }} {{ $user->zip ?? '' }}</div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Date of Birth:</div>
                <div class="details-cell value">
                    @if($user->birth_month && $user->birth_day && $user->birth_year)
                        {{ $user->birth_month }}/{{ $user->birth_day }}/{{ $user->birth_year }}
                    @endif
                </div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Driver License #:</div>
                <div class="details-cell value">{{ $user->driver_license ?? '' }}</div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Citation Number:</div>
                <div class="details-cell value">{{ $user->citation_number ?? '' }}</div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Court:</div>
                <div class="details-cell value">{{ $user->court_selected ?? '' }}</div>
            </div>
            <div class="details-row">
                <div class="details-cell label">County:</div>
                <div class="details-cell value">{{ $user->state ?? '' }}</div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Due Date:</div>
                <div class="details-cell value">
                    @if($user->due_month && $user->due_day && $user->due_year)
                        {{ $user->due_month }}/{{ $user->due_day }}/{{ $user->due_year }}
                    @endif
                </div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Completion Date:</div>
                <div class="details-cell value">{{ $certificate->completion_date->format('m/d/Y') }}</div>
            </div>
            <div class="details-row">
                <div class="details-cell label">Final Exam Score:</div>
                <div class="details-cell value">{{ number_format($certificate->final_exam_score, 1) }}%</div>
            </div>
        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-cell">
                <div class="signature-line"></div>
                <div>School Administrator</div>
                <div style="font-size: 10px;">{{ config('app.name') }}</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line"></div>
                <div>Course Instructor</div>
                <div style="font-size: 10px;">Certified by DHSMV</div>
            </div>
        </div>
        
        <!-- State Seal -->
        @if(isset($state_stamp) && $state_stamp)
            <img src="{{ $state_stamp->image_url }}" alt="Florida State Seal" class="state-seal">
        @else
            <div class="state-seal" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                <div style="text-align: center; font-size: 10px;">
                    <div>FLORIDA</div>
                    <div>STATE</div>
                    <div>SEAL</div>
                </div>
            </div>
        @endif
        
        <!-- Footer Text -->
        <div class="footer-text">
            This certificate is issued in compliance with Florida Statute 318.14(9)<br>
            and Florida Administrative Code 15A-10.0141<br>
            Only original certificates are acceptable to the Court. Photocopies are not acceptable.
        </div>
    </div>
</body>
</html>