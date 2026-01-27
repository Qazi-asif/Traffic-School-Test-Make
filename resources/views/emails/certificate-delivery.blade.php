@extends('emails.layout')

@section('content')
<div class="header">
    <h1>📜 Your Certificate Awaits!</h1>
    <p>Download Your Proof of Completion</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Your certificate of completion is now available for download. This document certifies your successful completion of the course.</p>
    
    <div class="details">
        <h3>Certificate Details</h3>
        <p><strong>Recipient:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
        <p><strong>Completion Date:</strong> {{ now()->format('M d, Y') }}</p>
        <p><strong>Status:</strong> <span class="accent-green">Ready for Download</span></p>
    </div>
    
    <div style="text-align: center;">
        <a href="{{ url('/certificates') }}" class="button button-success">Download Certificate</a>
    </div>
    
    <h3>What's Included</h3>
    <p>Your certificate includes:</p>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Your name and completion date</li>
        <li>Course title and details</li>
        <li>Unique certificate number for verification</li>
        <li>Official seal and signature</li>
    </ul>
    
    <div class="alert alert-info">
        <strong>💾 File Format:</strong> Your certificate is available as a PDF file that you can download, print, or share digitally.
    </div>
    
    <h3>How to Use Your Certificate</h3>
    <p>You can use this certificate to:</p>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Add to your LinkedIn profile</li>
        <li>Include in your resume</li>
        <li>Share with employers</li>
        <li>Print for your records</li>
        <li>Use for professional development</li>
    </ul>
    
    <p style="margin-top: 30px;">Congratulations on completing your course! We hope you gained valuable knowledge and skills.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
