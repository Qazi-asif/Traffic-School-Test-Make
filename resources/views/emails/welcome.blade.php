@extends('emails.layout')

@section('content')
<div class="header">
    <h1>Welcome! 🎓</h1>
    <p>Your Learning Journey Starts Here</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you for registering with us! We're thrilled to have you join our learning community.</p>
    
    <div class="details">
        <h3>Your Account is Ready</h3>
        <p>Your account has been successfully created and is ready to use. You can now:</p>
        <ul style="margin-left: 20px; margin-top: 10px;">
            <li>Browse our comprehensive course catalog</li>
            <li>Enroll in courses that match your goals</li>
            <li>Track your learning progress</li>
            <li>Earn certificates upon completion</li>
        </ul>
    </div>
    
    <p>Whether you're looking to improve your skills or complete a specific requirement, we have the perfect course for you.</p>
    
    <div style="text-align: center;">
        <a href="{{ route('login') }}" class="button button-dark-blue">Start Learning Now</a>
    </div>
    
    <div class="alert alert-info">
        <strong>💡 Tip:</strong> Check out our featured courses to get started quickly. Most courses can be completed at your own pace.
    </div>
    
    <p style="margin-top: 30px;">If you have any questions or need assistance, our support team is here to help.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
