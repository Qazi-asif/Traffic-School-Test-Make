<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic School Certificate</title>
    <style>
        * { margin: 0; padding: 0; }
        html, body { width: 100%; height: 100%; }
        body { 
            font-family: Arial, sans-serif; 
            background: white;
            padding: 0;
            margin: 0;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 150%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.12);
            text-align: center;
            z-index: 0;
            pointer-events: none;
            white-space: nowrap;
        }
        .certificate { 
            width: 100%;
            border: 2px solid #000;
            position: relative;
            z-index: 1;
            background: white;
            font-size: 11px;
            line-height: 1.2;
        }
        .row { display: flex; width: 100%; }
        .col-33 { flex: 0 0 33.33%; border-right: 2px solid #000; padding: 8px; }
        .col-50 { flex: 0 0 50%; border-right: 1px solid #000; padding: 5px; }
        .col-50:last-child { border-right: none; }
        .border-bottom { border-bottom: 2px solid #000; }
        .border-bottom-thin { border-bottom: 1px solid #000; }
        .highlight { background: #90EE90; padding: 1px 2px; }
        .text-center { text-align: center; }
        .school-info { font-size: 10px; }
        .cert-number { font-size: 10px; }
        .student-info { padding: 8px; border-top: 2px solid #000; font-size: 10px; }
        .photo-section { padding: 8px; text-align: center; }
        .photo-placeholder { width: 80px; height: 100px; background: #f0f0f0; border: 1px solid #ccc; margin: 0 auto; }
        .completion-section { padding: 8px; border-bottom: 2px solid #000; font-size: 10px; line-height: 1.3; }
        .details-section { display: flex; }
        .detail-col { flex: 1; }
        .detail-row { display: flex; border-bottom: 1px solid #000; }
        .detail-label { flex: 1; padding: 4px; border-right: 1px solid #000; font-weight: bold; font-size: 9px; }
        .detail-value { flex: 1; padding: 4px; background: #90EE90; font-size: 9px; }
        .address-section { padding: 8px; border-bottom: 2px solid #000; font-size: 9px; line-height: 1.3; }
        .signature-section { padding: 10px; font-size: 9px; }
        .sig-row { display: flex; margin-bottom: 10px; }
        .sig-box { flex: 1; text-align: center; }
        .sig-line { height: 25px; border-bottom: 1px solid #000; margin-bottom: 3px; }
    </style>
</head>
<body>
    <div class="watermark">DummiesTrafficSchool.com</div>
    <div class="certificate">
        <!-- Header -->
        <div class="row border-bottom">
            <div class="col-33 school-info">
                <strong>DummiesTrafficSchool.com</strong><br>
                4235 Hillsboro Pike #300644<br>
                Nashville, TN 37215
            </div>
            <div class="col-33"></div>
            <div class="col-33 cert-number text-center">
                (TVS OL 016)<br>
                Certificate<br>
                Number:<br>
                <span class="highlight">{{ $certificate_number }}</span>
            </div>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <span class="highlight">{{ $student_name ?? 'N/A' }}</span><br>
            <span class="highlight">{!! $student_address ? str_replace("\n", "<br>", $student_address) : 'N/A' !!}</span>
        </div>

        <!-- Photo -->
        <div class="photo-section">
            <div class="photo-placeholder"></div>
        </div>

        <!-- Completion Text -->
        <div class="completion-section">
            This Certifies that ( <span class="highlight">{{ $student_name ?? 'N/A' }}</span> ) has <span class="highlight">completed</span> on ( <span class="highlight">{{ $completion_date ?? 'N/A' }}</span> ) a Court-approved ( <span class="highlight">English</span> ) (Internet course) ( <span class="highlight">{{ $course_type ?? 'Traffic School Course' }}</span> ), and has correctly answered ( <span class="highlight">{{ $score ?? 'N/A' }}</span> ) of the questions on the Final Exam for this course.
        </div>

        <!-- Details -->
        <div class="details-section">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Driver License Number:</div>
                    <div class="detail-value">{{ $license_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Citation Number:</div>
                    <div class="detail-value">{{ $citation_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Court:</div>
                    <div class="detail-value">{{ $court ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value">{{ $birth_date ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Due Date:</div>
                    <div class="detail-value">{{ $due_date ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">County</div>
                    <div class="detail-value">{{ $county ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="address-section">
            <strong>Students Address:</strong><br>
            ( {{ $student_name ?? 'N/A' }} )<br>
            {!! $student_address ? str_replace("\n", "<br>", $student_address) : 'N/A' !!}<br><br>
            Only original certificates are acceptable To the Court. Photocopies are not acceptable.
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <p><strong>To be completed by the HSTS Owner :</strong></p>
            <p>I CERTIFY UNDER PENALTY THAT THE FOREGOING IS TRUE AND CORRECT.<br>( PERJURY IS PUNISHABLE BY IMPRISONMENT , FINE OR BOTH. )</p>
            
            <div class="sig-row">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    Signature of HSTS Owner
                </div>
                <div class="sig-box">
                    L. Morera<br>
                    Printed Name of HSTS Owner
                </div>
            </div>
            
            <div class="sig-row">
                <div class="sig-box">
                    {{ date('m/d/Y') }}<br>
                    Date
                </div>
                <div class="sig-box"></div>
            </div>
            
            <div class="sig-row">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    Signature of Defendant
                </div>
                <div class="sig-box">
                    Signed under penalty of perjury.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
