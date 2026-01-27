<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { display: inline-block; padding: 12px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>We Miss You!</h1>
        </div>
        <div class="content">
            <p>Hi {{ $enrollment->user->name }},</p>
            
            <p>We noticed you haven't been active in your <strong>{{ $enrollment->course->title ?? 'course' }}</strong> for a while.</p>
            
            <p>You've already completed <strong>{{ $enrollment->progress_percentage }}%</strong> of the course. Don't let your progress go to waste!</p>
            
            <p>Your course details:</p>
            <ul>
                <li><strong>Course:</strong> {{ $enrollment->course->title ?? 'N/A' }}</li>
                <li><strong>Progress:</strong> {{ $enrollment->progress_percentage }}%</li>
                <li><strong>Enrolled:</strong> {{ $enrollment->enrolled_at?->format('M d, Y') }}</li>
                @if($enrollment->court_date)
                <li><strong>Due Date:</strong> {{ $enrollment->court_date->format('M d, Y') }}</li>
                @endif
            </ul>
            
            <p style="text-align: center;">
                <a href="{{ url('/course-player/' . $enrollment->course_id) }}" class="button">Continue Your Course</a>
            </p>
            
            <p>If you're having any issues or need assistance, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>The Traffic School Team</p>
        </div>
        <div class="footer">
            <p>This is an automated reminder. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
