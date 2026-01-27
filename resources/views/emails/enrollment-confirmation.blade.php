@extends('emails.layout')

@section('content')
<div class="header">
    <h1>Enrollment Confirmed! ✓</h1>
    <p>You're Ready to Start Learning</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you for enrolling! We're excited to have you as a student in our course.</p>
    
    <div class="details">
        <h3>Enrollment Details</h3>
        <p><strong>Course:</strong> {{ $course->title }}</p>
        <p><strong>Enrollment Date:</strong> {{ now()->format('M d, Y') }}</p>
        @if(isset($enrollment->amount_paid))
        <p><strong>Amount Paid:</strong> <span class="accent-gold">${{ number_format($enrollment->amount_paid, 2) }}</span></p>
        @endif
    </div>
    
    <p>You now have full access to all course materials. Start learning at your own pace and progress through the modules.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/my-enrollments') }}" class="button button-dark-blue">Start Learning</a>
        <a href="{{ url('/courses') }}" class="button button-dark-blue">Browse More Courses</a>
    </div>
    
    <div class="alert alert-success">
        <strong>🎯 Getting Started:</strong> Access your course materials, watch videos, and complete quizzes. Your progress is automatically saved.
    </div>
    
    <h3>What to Expect</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Structured course modules with clear learning objectives</li>
        <li>Interactive quizzes to test your knowledge</li>
        <li>Certificate upon successful completion</li>
        <li>Lifetime access to course materials</li>
    </ul>
    
    <p style="margin-top: 30px;">If you have any questions or encounter any issues, please don't hesitate to contact our support team.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
