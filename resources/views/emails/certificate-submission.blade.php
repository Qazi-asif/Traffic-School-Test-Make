@extends('emails.layout')

@section('content')
<div class="header">
    <h1>📋 Certificate Submission</h1>
    <p>State Compliance Notification</p>
</div>

<div class="content">
    <p>A certificate has been submitted for state compliance:</p>
    
    <div class="details">
        <h3>Certificate Information</h3>
        <ul style="margin-left: 20px; margin-top: 10px;">
            <li><strong>Student Name:</strong> {{ $certificate->student_name }}</li>
            <li><strong>Course:</strong> {{ $certificate->course_name }}</li>
            <li><strong>Completion Date:</strong> {{ $certificate->completion_date->format('M d, Y') }}</li>
            <li><strong>Certificate ID:</strong> {{ $certificate->certificate_number }}</li>
        </ul>
    </div>
    
    <div class="alert alert-info">
        <strong>✓ Submitted:</strong> This certificate has been automatically submitted for state compliance.
    </div>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Administration</strong></p>
</div>
@endsection
