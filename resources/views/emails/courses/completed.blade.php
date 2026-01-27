@extends('emails.layout')

@section('content')
<div class="header">
    <h1>🎉 Congratulations!</h1>
    <p>You've Successfully Completed the Course</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Amazing work! You have successfully completed:</p>
    
    <div class="achievement-box">
        <h2>{{ $course->title }}</h2>
        <p style="font-size: 18px; color: #228B22; margin: 0;">✓ 100% Complete</p>
    </div>
    
    <div class="stats">
        <div class="stat">
            <div class="stat-value">{{ $enrollment->progress->count() ?? 'N/A' }}</div>
            <div class="stat-label">Chapters</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $course->total_duration ?? 'N/A' }}</div>
            <div class="stat-label">Minutes</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $enrollment->enrolled_at->diffInDays(now()) }}</div>
            <div class="stat-label">Days</div>
        </div>
    </div>
    
    <div class="alert alert-success">
        <strong>📜 Certificate Ready:</strong> Your certificate is being generated and will be available in your account shortly.
    </div>
    
    <div style="text-align: center;">
        <a href="{{ url('/certificates') }}" class="button button-success">View Certificate</a>
        <a href="{{ url('/courses') }}" class="button button-dark-blue">Browse More Courses</a>
    </div>
    
    <h3>What's Next?</h3>
    <p>Continue your learning journey by exploring our other courses. Each course is designed to help you achieve your goals.</p>
    
    <div class="details">
        <h3>Your Achievement</h3>
        <p>You've demonstrated commitment and dedication by completing this course. This certificate is a testament to your hard work and can be shared with employers or educational institutions.</p>
    </div>
    
    <p style="margin-top: 30px;">Thank you for choosing us for your learning needs. We hope you found the course valuable!</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
