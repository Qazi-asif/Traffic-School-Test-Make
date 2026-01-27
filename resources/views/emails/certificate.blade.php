@extends('emails.layout')

@section('content')
<div class="header">
    <h1>🎓 Certificate of Completion</h1>
    <p>Your Achievement Awaits</p>
</div>

<div class="content">
    <p>Congratulations <span class="highlight">{{ $certificate->student_name }}</span>!</p>
    
    <p>You have successfully completed your traffic school course. Your certificate is attached to this email.</p>
    
    <div class="achievement-box">
        <h3>Certificate Details</h3>
        <p><strong>Certificate Number:</strong> {{ $certificate->certificate_number }}</p>
        <p><strong>Student Name:</strong> {{ $certificate->student_name }}</p>
        <p><strong>Course:</strong> {{ $certificate->course_name }}</p>
        <p><strong>State:</strong> {{ $certificate->state_code }}</p>
        <p><strong>Completion Date:</strong> {{ \Carbon\Carbon::parse($certificate->completion_date)->format('M d, Y') }}</p>
        <p><strong>Status:</strong> <span class="accent-green">{{ ucfirst($certificate->status) }}</span></p>
    </div>
    
    <p>You can verify this certificate online using the link below:</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/certificates/' . $certificate->verification_hash . '/verify') }}" class="button button-success">
            Verify Certificate Online
        </a>
    </div>
    
    <div class="alert alert-success">
        <strong>✓ Certificate Ready:</strong> Please keep this certificate for your records. You can download additional copies from your student dashboard anytime.
    </div>
    
    <p>Congratulations again on completing your course!</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
