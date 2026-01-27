@extends('emails.layout')

@section('content')
<div class="header">
    <h1>✓ Payment Confirmed!</h1>
    <p>Your Transaction is Complete</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you! Your payment has been successfully processed and confirmed.</p>
    
    <div class="details">
        <h3>Payment Details</h3>
        <p><strong>Transaction ID:</strong> {{ $payment->transaction_id ?? 'N/A' }}</p>
        <p><strong>Amount:</strong> <span class="accent-gold">${{ number_format($payment->amount, 2) }}</span></p>
        <p><strong>Payment Date:</strong> {{ $payment->created_at->format('M d, Y H:i A') }}</p>
        <p><strong>Status:</strong> <span class="accent-green">Approved</span></p>
    </div>
    
    <h3>Course Access</h3>
    <p>Your course enrollment is now active. You have immediate access to all course materials.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/my-enrollments') }}" class="button button-dark-blue">Start Learning Now</a>
        <a href="{{ url('/courses') }}" class="button">Browse More Courses</a>
    </div>
    
    <div class="alert alert-info">
        <strong>📧 Invoice:</strong> A detailed invoice has been sent to your email. You can also access it from your account dashboard.
    </div>
    
    <h3>Next Steps</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Log in to your account</li>
        <li>Navigate to "My Enrollments"</li>
        <li>Start with the first course module</li>
        <li>Complete lessons at your own pace</li>
    </ul>
    
    <div class="details">
        <h3>Course Information</h3>
        @if(isset($payment->enrollment->course))
        <p><strong>Course:</strong> {{ $payment->enrollment->course->title }}</p>
        <p><strong>Duration:</strong> {{ $payment->enrollment->course->total_duration ?? 'Self-paced' }}</p>
        @endif
    </div>
    
    <p style="margin-top: 30px;">If you have any questions about your payment or need technical support, please don't hesitate to contact us.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
