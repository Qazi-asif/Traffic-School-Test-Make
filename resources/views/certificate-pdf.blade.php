<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic School Certificate</title>
    <style>
        @page {
            size: 8.5in 11in; /* Standard letter size portrait */
            margin: 0.5in;
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0; 
            font-size: 11px; 
            background: #fff;
            color: #000;
        }
        .certificate { 
            width: 7.5in; 
            max-height: 10in;
            border: 2px solid #000; 
            background: #fff;
            color: #000;
            display: flex;
            flex-direction: column;
            margin: 0 auto;
            page-break-inside: avoid;
        }
        
        /* Top Section */
        .top-section { 
            display: table; 
            width: 100%; 
            border-bottom: 1px solid #000; 
            height: 120px;
        }
        .school-info { 
            display: table-cell; 
            width: 33%; 
            padding: 10px; 
            border-right: 1px solid #000; 
            vertical-align: top; 
            font-size: 12px;
        }
        .state-seal-section { 
            display: table-cell; 
            width: 34%; 
            padding: 10px; 
            border-right: 1px solid #000; 
            text-align: center; 
            vertical-align: middle;
        }
        .cert-number-section { 
            display: table-cell; 
            width: 33%; 
            padding: 10px; 
            text-align: center; 
            vertical-align: top;
            font-size: 12px;
        }
        
        /* Student Info Section - No Photo */
        .student-section { 
            display: table; 
            width: 100%; 
            border-bottom: 1px solid #000; 
            height: 80px;
        }
        .student-info { 
            display: table-cell; 
            width: 100%; 
            padding: 10px; 
            vertical-align: top;
            font-size: 12px;
        }
        
        /* Course Completion Section */
        .completion-section { 
            padding: 15px; 
            border-bottom: 1px solid #000; 
            font-size: 12px;
            line-height: 1.5;
            min-height: 80px;
        }
        
        /* Details Table */
        .details-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px;
        }
        .details-table td { 
            border: 1px solid #000; 
            padding: 8px; 
            vertical-align: top;
            font-size: 11px;
        }
        .detail-label { 
            background: #f5f5f5; 
            font-weight: bold; 
            width: 25%;
        }
        .detail-value { 
            background: #90EE90; 
            color: #000; 
            font-weight: bold;
        }
        
        /* Address Section */
        .address-section { 
            padding: 15px; 
            border-bottom: 1px solid #000; 
            font-size: 12px;
            min-height: 60px;
        }
        
        /* Signature Section */
        .signature-section { 
            padding: 15px; 
            flex-grow: 1;
            font-size: 11px;
        }
        .signature-row { 
            display: table; 
            width: 100%; 
            margin-bottom: 15px; 
        }
        .signature-left, .signature-right { 
            display: table-cell; 
            width: 50%; 
            vertical-align: top;
            padding: 8px;
        }
        .signature-line { 
            border-bottom: 1px solid #000; 
            height: 30px; 
            margin-bottom: 5px; 
            position: relative;
        }
        
        /* HSTS Owner Signature Image */
        .hsts-signature {
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 150px;
            height: 25px;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjI1IiB2aWV3Qm94PSIwIDAgMTUwIDI1IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMTAgMTVDMTUgMTAgMjAgNSAzMCA4QzQwIDEyIDUwIDIwIDYwIDE1QzcwIDEwIDgwIDUgOTAgOEMxMDAgMTIgMTEwIDIwIDEyMCAxNUMxMzAgMTAgMTQwIDUgMTQ1IDgiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIi8+Cjwvc3ZnPgo=');
            background-repeat: no-repeat;
            background-size: contain;
        }
        
        /* Green highlighting for dynamic content */
        .highlight { 
            background: #90EE90; 
            color: #000; 
            padding: 2px 4px; 
            font-weight: bold;
        }
        
        /* State seal styling */
        .state-seal img {
            max-width: 100px;
            max-height: 100px;
        }
        .state-seal-placeholder {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 9px;
            text-align: center;
        }
        
        /* Typography adjustments */
        .cert-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-name {
            font-size: 13px;
            font-weight: bold;
        }
        
        .student-name {
            font-size: 14px;
            font-weight: bold;
        }
        
        /* Defendant signature area */
        .defendant-signature-area {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Top Section -->
        <div class="top-section">
            <div class="school-info">
                <div class="company-name">DummiesTrafficSchool.com</div>
                524 N. Mountain View Ave. #2<br>
                San Bernardino, CA 92401
            </div>
            <div class="state-seal-section">
                @if(isset($state_stamp) && $state_stamp && $state_stamp->logo_path)
                    @php
                        $imagePath = public_path('storage/' . $state_stamp->logo_path);
                        $imageData = null;
                        $mimeType = 'image/png';
                        
                        if (file_exists($imagePath)) {
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                            
                            switch($extension) {
                                case 'jpg':
                                case 'jpeg':
                                    $mimeType = 'image/jpeg';
                                    break;
                                case 'png':
                                    $mimeType = 'image/png';
                                    break;
                                case 'svg':
                                    $mimeType = 'image/svg+xml';
                                    break;
                                case 'gif':
                                    $mimeType = 'image/gif';
                                    break;
                            }
                        }
                    @endphp
                    
                    @if($imageData)
                        <div class="state-seal">
                            <img src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="{{ $state_stamp->state_name }} Seal" style="max-width: 100px; max-height: 100px;">
                        </div>
                    @else
                        <div class="state-seal-placeholder">
                            {{ $state_stamp->state_name }}<br>SEAL
                        </div>
                    @endif
                @else
                    <div class="state-seal-placeholder">
                        STATE<br>SEAL
                    </div>
                @endif
            </div>
            <div class="cert-number-section">
                (TVS 10076)<br>
                <div class="cert-title">Certificate<br>Number:</div>
                <span class="highlight">{{ $certificate_number ?? 'N/A' }}</span>
            </div>
        </div>
        
        <!-- Student Info Section (No Photo) -->
        <div class="student-section">
            <div class="student-info">
                <div class="student-name">
                    <span class="highlight">{{ $student_name ?? 'N/A' }}</span>
                </div>
                <div style="margin-top: 8px;">
                    <span class="highlight">{{ $student_address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Course Completion Section -->
        <div class="completion-section">
            This Certifies that ( <span class="highlight">{{ $student_name ?? 'N/A' }}</span> ) has 
            completed on ( <span class="highlight">{{ $completion_date ?? 'N/A' }}</span> ) a 
            {{ $state ?? 'Florida' }} Superior Court-approved ( <span class="highlight">English</span> ) 
            (<span class="highlight">Internet course</span>) 
            (<span class="highlight">{{ $course_type ?? 'Traffic School Course' }}</span>), and has 
            correctly answered ( <span class="highlight">{{ $score ?? 'N/A' }}</span> ) of the questions on 
            the Final Exam for this course.
        </div>

        <!-- Details Table -->
        <table class="details-table">
            <tr>
                <td class="detail-label">Student's Driver<br>Lic. Number</td>
                <td class="detail-value">{{ $license_number ?? 'N/A' }}</td>
                <td class="detail-label">Student's Date of Birth:</td>
                <td class="detail-value">{{ $birth_date ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Citation / Docket<br>Number:</td>
                <td class="detail-value">{{ $citation_number ?? 'N/A' }}</td>
                <td class="detail-label">Traffic School Due Date:</td>
                <td class="detail-value">{{ $due_date ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Court:</td>
                <td class="detail-value">{{ $court ?? 'N/A' }}</td>
                <td class="detail-label">County</td>
                <td class="detail-value">The State of<br>{{ $state ?? 'Florida' }}</td>
            </tr>
        </table>

        <!-- Address Section -->
        <div class="address-section">
            <strong>Students Address:</strong><br>
            ( <span class="highlight">{{ $student_name ?? 'N/A' }}</span> )<br>
            <span class="highlight">{{ $student_address ?? 'N/A' }}</span><br>
            <div style="margin-top: 8px;">
                Only original certificates are acceptable To the Court. Photocopies are not acceptable.
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div style="margin-bottom: 10px;">
                <strong>To be completed by the HSTS Owner :</strong>
            </div>
            <div style="margin-bottom: 15px;">
                <strong>I CERTIFY UNDER PENALTY THAT THE FOREGOING IS TRUE AND CORRECT.<br>
                ( PERJURY IS PUNISHABLE BY IMPRISONMENT , FINE OR BOTH. )</strong>
            </div>
            
            <div class="signature-row">
                <div class="signature-left">
                    <div class="signature-line">
                        <div class="hsts-signature"></div>
                    </div>
                    <strong>Signature of HSTS Owner</strong>
                </div>
                <div class="signature-right">
                    <strong>L. Morera</strong><br>
                    <strong>Printed Name of HSTS Owner</strong>
                </div>
            </div>
            
            <div class="signature-row">
                <div class="signature-left">
                    <strong>{{ $completion_date ?? date('m/d/Y') }}</strong><br>
                    <strong>Date</strong>
                </div>
                <div class="signature-right">
                    <!-- Empty for spacing -->
                </div>
            </div>
            
            <div class="defendant-signature-area">
                <div class="signature-row">
                    <div class="signature-left">
                        <div class="signature-line"></div>
                        <strong>Signature of Defendant</strong>
                    </div>
                    <div class="signature-right">
                        <strong>Signed under penalty of perjury.</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>