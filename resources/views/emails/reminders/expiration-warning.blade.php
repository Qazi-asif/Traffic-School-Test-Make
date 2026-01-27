<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ef4444; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 30px; background: #ef4444; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Course Expiring Soon</h1>
        </div>
        <div class="content">
            <p>Hi {{ $enrollment->user->name }},</p>
            
            <div class="warning-box">
                <strong>URGENT:</strong> Your course access is expiring soon!
            </div>
            
            <p>Your <strong>{{ $enrollment->course->title ?? 'course' }}</strong> will expire on <strong>{{ $enrollment->court_date?->format('M d, Y') }}</strong>.</p>
            
            <p>That's only <strong>{{ $enrollment->court_date?->diffInDays(now()) }} days</strong> away!</p>
            
            <p>Current Progress: <strong>{{ $enrollment->progress_percentage }}%</strong></p>
            
            @if($enrollment->progress_percentage < 100)
            <p>You still need to complete {{ 100 - $enrollment->progress_percentage }}% of the course. Don't wait until the last minute!</p>
            @endif
            
            <p style="text-align: center;">
                <a href="{{ url('/course-player/' . $enrollment->course_id) }}" class="button">Complete Course Now</a>
            </p>
            
            <p><strong>What happens if I don't complete on time?</strong><br>
            Your enrollment may expire and you may need to re-enroll or face court penalties.</p>
            
            <p>If you need an extension or have questions, please contact us immediately.</p>
            
            <p>Best regards,<br>The Traffic School Team</p>
        </div>
        <div class="footer">
            <p>This is an automated reminder. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
