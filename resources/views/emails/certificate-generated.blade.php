@extends('emails.layout')

@section('content')
<div class="header">
    <h1>🎓 Your Certificate is Ready!</h1>
    <p>Celebrate Your Achievement</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Excellent news! Your certificate of completion has been generated and is ready for download.</p>
    
    <div class="achievement-box">
        <h2>Certificate of Completion</h2>
        <p style="color: #666; margin: 10px 0;">This certifies that you have successfully completed the course requirements.</p>
    </div>
    
    <div class="details">
        <h3>Certificate Information</h3>
        <p><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
        <p><strong>Issued Date:</strong> {{ now()->format('M d, Y') }}</p>
        @if(isset($certificate_number))
        <p><strong>Certificate Number:</strong> {{ $certificate_number }}</p>
        @endif
    </div>
    
    <div style="text-align: center;">
        <a href="{{ url('/certificates') }}" class="button button-success">View My Certificates</a>
        <a href="{{ url('/my-certificates') }}" class="button">Download Certificate</a>
    </div>
    
    <h3>Share Your Achievement</h3>
    <p>You can now:</p>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Download the certificate as a PDF</li>
        <li>Share it on LinkedIn and social media</li>
        <li>Add it to your professional portfolio</li>
        <li>Include it in job applications</li>
    </ul>
    
    <div class="alert alert-success">
        <strong>🌟 Verified Credential:</strong> This certificate is a verified proof of your course completion and can be shared with confidence.
    </div>
    
    <p style="margin-top: 30px;">Thank you for your dedication and hard work. We're proud of your achievement!</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
