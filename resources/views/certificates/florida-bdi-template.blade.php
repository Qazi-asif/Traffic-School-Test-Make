<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Florida BDI Certificate</title>
    <style>
        @page {
            margin: 0.5in;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
        }
        .certificate-container {
            width: 100%;
            max-width: 8.5in;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 10pt;
            margin-bottom: 15px;
        }
        .student-info {
            margin-bottom: 10px;
        }
        .certificate-number {
            font-size: 14pt;
            font-weight: bold;
            text-align: right;
            margin-bottom: 20px;
        }
        .certification-text {
            text-align: center;
            font-size: 12pt;
            margin: 20px 0;
            line-height: 1.8;
        }
        .info-row {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
        }
        .info-value {
            display: inline-block;
            border-bottom: 1px solid #000;
            flex: 1;
            padding-left: 10px;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            text-align: center;
        }
        .footer-note {
            margin-top: 20px;
            font-size: 10pt;
            font-style: italic;
            text-align: center;
        }
        .warning {
            margin-top: 15px;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="header">
            <div class="company-name">DummiesTrafficSchool.com</div>
            <div class="company-address">
                524 N. Mountain View Ave. #2<br>
                San Bernardino, CA 92401
            </div>
        </div>

        <div class="student-info">
            <strong>{{ $user->first_name ?? '' }} {{ $user->middle_name ?? '' }} {{ $user->last_name ?? '' }}</strong><br>
            {{ $user->mailing_address ?? $user->address ?? '' }}<br>
            {{ $user->city ?? '' }}, {{ $user->state ?? '' }} {{ $user->zip ?? $user->zip_code ?? '' }}
        </div>

        <div class="certificate-number">
            (TVS 10076) Certificate Number: <strong>{{ $certificate_number }}</strong>
        </div>

        <div class="certification-text">
            This Certifies that <strong>( {{ $user->first_name }} {{ $user->last_name }} )</strong> has completed on <strong>( {{ $completion_date }} )</strong> a<br>
            Florida Superior Court-approved <strong>( English )</strong> <strong>(Internet course)</strong><br>
            <strong>(Florida 4-Hour Basic Driver Improvement Course (BDI))</strong>, and has correctly answered<br>
            <strong>( {{ $exam_score }}% )</strong> of the questions on the Final Exam for this course.
        </div>

        <div class="info-row">
            <span class="info-label">Student's Driver Lic. Number:</span>
            <span class="info-value">{{ $user->driver_license ?? $user->drivers_license_number ?? 'N/A' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Student's Date of Birth:</span>
            <span class="info-value">{{ $date_of_birth }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Citation / Docket Number:</span>
            <span class="info-value">{{ $certificate->citation_number ?? 'N/A' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Traffic School Due Date:</span>
            <span class="info-value">{{ $certificate->due_date ? $certificate->due_date->format('m/d/Y') : 'N/A' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Court:</span>
            <span class="info-value">{{ $certificate->citation_county ?? 'N/A' }} County</span>
        </div>

        <div class="info-row">
            <span class="info-label">The State of Florida</span>
        </div>

        <div class="info-row">
            <span class="info-label">Students Address:</span>
            <span class="info-value">
                {{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}<br>
                {{ $user->mailing_address ?? $user->address ?? '' }},<br>
                {{ $user->city ?? '' }}, {{ $user->state ?? '' }} {{ $user->zip ?? $user->zip_code ?? '' }}
            </span>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div style="margin-bottom: 10px;">To be completed by the HSTS Owner:</div>
                <div style="margin-bottom: 20px;">
                    I CERTIFY UNDER PENALTY THAT THE FOREGOING IS TRUE AND CORRECT.<br>
                    <small>( PERJURY IS PUNISHABLE BY IMPRISONMENT, FINE OR BOTH. )</small>
                </div>
                <div class="signature-line">
                    L. Morera<br>
                    <small>Signature of HSTS Owner / Printed Name of HSTS Owner</small>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    {{ $completion_date }}<br>
                    <small>Date</small>
                </div>
            </div>

            <div class="signature-box">
                <div style="margin-top: 60px;">
                    <div style="margin-bottom: 20px; text-align: center;">
                        Signed under penalty of perjury.
                    </div>
                    <div class="signature-line">
                        <small>Signature of Defendant</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="warning">
            Only original certificates are acceptable to the Court. Photocopies are not acceptable.
        </div>

        @if(isset($qr_code))
        <div style="text-align: center; margin-top: 20px;">
            <small>Verification Code: {{ $certificate->certificate_number }}</small>
        </div>
        @endif
    </div>
</body>
</html>
