@extends('emails.layout')

@section('content')
<div class="header">
    <h1>📧 Support Ticket Update</h1>
    <p>We've Received Your Message</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you for contacting our support team. We've received your support ticket and will get back to you shortly.</p>
    
    <div class="details">
        <h3>Ticket Information</h3>
        <p><strong>Ticket ID:</strong> <span class="highlight">#{{ $ticket->id ?? 'N/A' }}</span></p>
        <p><strong>Subject:</strong> {{ $ticket->subject ?? 'Support Request' }}</p>
        <p><strong>Status:</strong> <span class="accent-gold">Open</span></p>
        <p><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i A') }}</p>
    </div>
    
    <h3>Your Message</h3>
    <div style="background: white; padding: 15px; border-left: 4px solid #6B8E23; margin: 15px 0; border-radius: 4px;">
        <p>{{ $ticket->message ?? 'Your support request has been recorded.' }}</p>
    </div>
    
    <div style="text-align: center;">
        <a href="{{ url('/support/tickets/' . ($ticket->id ?? '')) }}" class="button">View Ticket</a>
    </div>
    
    <div class="alert alert-info">
        <strong>⏱️ Response Time:</strong> Our support team typically responds within 24 hours during business days.
    </div>
    
    <h3>What Happens Next</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Our support team will review your ticket</li>
        <li>We'll investigate your issue thoroughly</li>
        <li>You'll receive a response via email</li>
        <li>We'll work with you to resolve the issue</li>
    </ul>
    
    <div class="details">
        <h3>Track Your Ticket</h3>
        <p>You can track the status of your support ticket anytime by logging into your account and visiting the Support section. Use your Ticket ID <span class="highlight">#{{ $ticket->id ?? 'N/A' }}</span> to reference your request.</p>
    </div>
    
    <p style="margin-top: 30px;">If you need to add more information to your ticket, please reply to this email or log into your account.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Support Team</strong></p>
</div>
@endsection
