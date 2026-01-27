<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #516425; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 30px; background: #516425; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .progress-bar { width: 100%; height: 30px; background: #e5e7eb; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: #516425; text-align: center; line-height: 30px; color: white; font-weight: bold; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Complete Your Course</h1>
        </div>
        <div class="content">
            <p>Hi {{ $enrollment->user->name }},</p>
            
            <p>You're making great progress on your <strong>{{ $enrollment->course->title ?? 'course' }}</strong>!</p>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $enrollment->progress_percentage }}%">
                    {{ $enrollment->progress_percentage }}%
                </div>
            </div>
            
            <p>You've paid for this course and you're {{ 100 - $enrollment->progress_percentage }}% away from completion. Let's finish strong!</p>
            
            @if($enrollment->court_date)
            <p><strong>Important:</strong> Your court date is {{ $enrollment->court_date->format('M d, Y') }}. Make sure to complete the course before then.</p>
            @endif
            
            <p style="text-align: center;">
                <a href="{{ url('/course-player/' . $enrollment->course_id) }}" class="button">Continue Learning</a>
            </p>
            
            <p>Need help? Our support team is here for you.</p>
            
            <p>Best regards,<br>The Traffic School Team</p>
        </div>
        <div class="footer">
            <p>This is an automated reminder. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
