<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Missouri Form 4444 - Driver Improvement Program Completion</title>
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
            background-color: #1e3a8a;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .instructions {
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .instructions h3 {
            color: #1e3a8a;
            margin-top: 0;
        }
        .instructions ol {
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
        }
        .form-details {
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .form-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .form-details td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .form-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1e3a8a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .contact-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Congratulations!</h1>
        <p>You have successfully completed the Missouri Driver Improvement Program</p>
    </div>

    <div class="content">
        <p>Dear {{ $user->first_name }} {{ $user->last_name }},</p>

        <p>Congratulations on successfully completing your Missouri Driver Improvement Program! Your Missouri Form 4444 (Record of Participation and Completion) is attached to this email.</p>

        <div class="form-details">
            <h3>Form 4444 Details</h3>
            <table>
                <tr>
                    <td>Form Number:</td>
                    <td>{{ $form->form_number }}</td>
                </tr>
                <tr>
                    <td>Completion Date:</td>
                    <td>{{ $form->completion_date->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td>Submission Deadline:</td>
                    <td>{{ $form->submission_deadline->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td>Purpose:</td>
                    <td>{{ ucwords(str_replace('_', ' ', $form->submission_method)) }}</td>
                </tr>
            </table>
        </div>

        @if($form->submission_method === 'point_reduction')
            <div class="alert alert-danger">
                <strong>IMPORTANT - Point Reduction:</strong> Your Form 4444 must be submitted to the Missouri Department of Revenue within 15 days of course completion (by {{ $form->submission_deadline->format('F j, Y') }}) to ensure your points are removed.
            </div>
        @endif

        <div class="instructions">
            <h3>{{ $instructions['title'] }}</h3>
            <ol>
                @foreach($instructions['steps'] as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
            
            <p><strong>Deadline:</strong> {{ $instructions['deadline'] }}</p>
            
            @if(isset($instructions['address']))
                <p><strong>Submit to:</strong><br>{{ $instructions['address'] }}</p>
            @endif
        </div>

        @if($form->court_signature_required)
            <div class="alert">
                <strong>Court Signature Required:</strong> Since you're taking this course for point reduction, you may need to have your court or judge sign the Form 4444 before submitting it to the Missouri Department of Revenue. Check with your court for specific requirements.
            </div>
        @endif

        <div class="contact-info">
            <h3>Need Help?</h3>
            <p>If you have any questions about your Form 4444 or submission requirements, please contact us:</p>
            <ul>
                <li><strong>Phone:</strong> (877) 388-0829</li>
                <li><strong>Email:</strong> support@dummiestrafficschool.com</li>
                <li><strong>Hours:</strong> Monday - Friday, 8:00 AM - 4:00 PM PST</li>
            </ul>
        </div>

        <p><strong>Important Reminders:</strong></p>
        <ul>
            <li>Keep a copy of your Form 4444 for your records</li>
            <li>Submit your form according to the instructions above</li>
            <li>Contact the receiving agency to confirm receipt if required</li>
            <li>You may only take a driver improvement course for point reduction once in a 36-month period</li>
        </ul>

        <p>Thank you for choosing our Missouri Driver Improvement Program. Drive safely!</p>

        <p>Best regards,<br>
        The Dummies Traffic School Team</p>
    </div>

    <div class="footer">
        <p>Dummies Traffic School | www.dummiestrafficschool.com | (877) 388-0829</p>
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>