<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Generated</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .certificate-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .important {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Congratulations!</h1>
        <h2>Your Certificate is Ready</h2>
    </div>

    <p>Dear {{ $studentName }},</p>

    <p>Congratulations on successfully completing your <strong>{{ $courseName }}</strong>!</p>

    <div class="certificate-info">
        <h3>📋 Certificate Details</h3>
        <ul>
            <li><strong>Student Name:</strong> {{ $studentName }}</li>
            <li><strong>Course:</strong> {{ $courseName }}</li>
            <li><strong>Certificate Number:</strong> {{ $certificateNumber }}</li>
            <li><strong>Issue Date:</strong> {{ now()->format('F j, Y') }}</li>
        </ul>
    </div>

    <div class="important">
        <h4>📎 Certificate Attached</h4>
        <p>Your official certificate is attached to this email as a PDF file. Please save this certificate for your records.</p>
    </div>

    <h3>📝 Important Instructions</h3>
    <ul>
        <li><strong>Court Submission:</strong> If you took this course for ticket dismissal, present the attached certificate to your court by the due date.</li>
        <li><strong>Insurance Discount:</strong> Contact your insurance company to apply for defensive driving discounts using this certificate.</li>
        <li><strong>Keep Records:</strong> Save this email and certificate for your permanent records.</li>
        <li><strong>Verification:</strong> Your certificate can be verified online using certificate number {{ $certificateNumber }}.</li>
    </ul>

    @if($course && isset($course->state_code))
        @switch(strtoupper($course->state_code))
            @case('FL')
                <div class="important">
                    <h4>🏛️ Florida Specific Information</h4>
                    <p>This certificate has been submitted to the Florida Department of Highway Safety and Motor Vehicles (FLHSMV) through the DICDS system as required by Florida law.</p>
                </div>
                @break
            @case('MO')
                <div class="important">
                    <h4>🏛️ Missouri Specific Information</h4>
                    <p>This certificate satisfies Missouri Form 4444 requirements for point reduction and insurance discount eligibility.</p>
                </div>
                @break
            @case('TX')
                <div class="important">
                    <h4>🏛️ Texas Specific Information</h4>
                    <p>This course is approved by the Texas Department of Licensing and Regulation (TDLR) for ticket dismissal and insurance discounts.</p>
                </div>
                @break
            @case('DE')
                <div class="important">
                    <h4>🏛️ Delaware Specific Information</h4>
                    <p>This certificate is approved by the Delaware Department of Motor Vehicles for point reduction and insurance discount programs.</p>
                </div>
                @break
        @endswitch
    @endif

    <h3>🔍 Certificate Verification</h3>
    <p>Your certificate can be verified by courts, insurance companies, or other authorized parties using:</p>
    <ul>
        <li><strong>Certificate Number:</strong> {{ $certificateNumber }}</li>
        <li><strong>Student Name:</strong> {{ $studentName }}</li>
        <li><strong>Verification URL:</strong> <a href="{{ url('/verify-certificate') }}">{{ url('/verify-certificate') }}</a></li>
    </ul>

    <h3>📞 Need Help?</h3>
    <p>If you have any questions about your certificate or need assistance, please contact us:</p>
    <ul>
        <li><strong>Email:</strong> {{ config('mail.from.address') }}</li>
        <li><strong>Phone:</strong> {{ config('app.phone', '1-800-TRAFFIC') }}</li>
        <li><strong>Website:</strong> <a href="{{ url('/') }}">{{ url('/') }}</a></li>
    </ul>

    <p>Thank you for choosing {{ config('app.name') }} for your defensive driving education!</p>

    <p>Best regards,<br>
    <strong>{{ config('app.name') }} Team</strong></p>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>{{ config('app.name') }} | {{ url('/') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>