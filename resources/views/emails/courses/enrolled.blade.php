@extends('emails.layout')

@section('content')
<div class="header">
    <h1>Welcome to Your Course! 📚</h1>
    <p>Your Learning Adventure Begins Now</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you for enrolling in our course! We're thrilled to have you as a student.</p>
    
    <div class="details">
        <h3>Course Information</h3>
        <p><strong>Course:</strong> {{ $course->title }}</p>
        <p><strong>Enrollment Date:</strong> {{ now()->format('M d, Y') }}</p>
        <p><strong>Duration:</strong> {{ $course->total_duration ?? 'Self-paced' }}</p>
    </div>
    
    <p>You now have full access to all course materials. Learn at your own pace and progress through the modules at a speed that works for you.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/my-enrollments') }}" class="button">Access Your Course</a>
    </div>
    
    <h3>Course Highlights</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Comprehensive video lessons and materials</li>
        <li>Interactive quizzes and assessments</li>
        <li>Certificate upon completion</li>
        <li>Lifetime access to course content</li>
        <li>24/7 support from our team</li>
    </ul>
    
    <div class="alert alert-info">
        <strong>💡 Pro Tip:</strong> Set aside dedicated time each week to complete the course modules. Consistency is key to success!
    </div>
    
    <h3>Getting Started</h3>
    <p>Log in to your account and navigate to "My Enrollments" to begin. Start with the first module and work your way through at your own pace.</p>
    
    <p style="margin-top: 30px;">If you have any questions or need technical support, our team is here to help!</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
