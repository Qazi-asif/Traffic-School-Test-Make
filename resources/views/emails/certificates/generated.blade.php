@extends('emails.layout')

@section('content')
<div class="header">
    <h1>🏆 Certificate Generated!</h1>
    <p>Your Achievement is Ready</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Congratulations! Your certificate has been successfully generated and is now available in your account.</p>
    
    <div class="achievement-box">
        <h2>{{ $certificate->course->title ?? 'Course Certificate' }}</h2>
        <p style="color: #666; margin: 10px 0;">Certificate ID: <span class="highlight">{{ $certificate->certificate_number ?? 'N/A' }}</span></p>
        <p style="color: #228B22; font-weight: 600;">Issued on {{ $certificate->issued_at->format('M d, Y') }}</p>
    </div>
    
    <div class="details">
        <h3>Certificate Details</h3>
        <p><strong>Recipient:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
        <p><strong>Course:</strong> {{ $certificate->course->title ?? 'N/A' }}</p>
        <p><strong>Completion Date:</strong> {{ $certificate->issued_at->format('M d, Y') }}</p>
        @if(isset($certificate->expiry_date))
        <p><strong>Valid Until:</strong> {{ $certificate->expiry_date->format('M d, Y') }}</p>
        @endif
    </div>
    
    <div style="text-align: center;">
        <a href="{{ url('/certificates') }}" class="button button-success">View Certificate</a>
        <a href="{{ url('/certificates/' . ($certificate->id ?? '')) . '/download') }}" class="button">Download PDF</a>
    </div>
    
    <h3>What You Can Do Now</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Download your certificate as a PDF</li>
        <li>Share it on social media or professional networks</li>
        <li>Add it to your resume or LinkedIn profile</li>
        <li>Print it for your records</li>
    </ul>
    
    <div class="alert alert-success">
        <strong>✓ Verified Achievement:</strong> This certificate verifies your successful completion of the course and can be shared with employers or educational institutions.
    </div>
    
    <p style="margin-top: 30px;">Thank you for completing this course. We hope you found it valuable and look forward to seeing you in future courses!</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
