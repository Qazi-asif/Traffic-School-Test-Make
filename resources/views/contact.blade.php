@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <h1 class="mb-4"><i class="fas fa-envelope"></i> Contact Us</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-phone"></i> Phone Support</h5>
                </div>
                <div class="card-body">
                    <p><strong>Toll Free:</strong> <a href="tel:8773823700">(877) 382-3700</a></p>
                    <p><strong>TTY/TDD:</strong> (877) 735-2929</p>
                    <p><strong>Fax:</strong> (310) 388-0828</p>
                    <p class="text-muted"><i class="fas fa-clock"></i> 24-Hour Information Line Available</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-envelope"></i> Email Support</h5>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> <a href="mailto:Support@DummiesTrafficSchool.com">Support@DummiesTrafficSchool.com</a></p>
                    <p class="text-muted">We typically respond within 24 hours during business hours.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-clock"></i> Office Hours</h5>
                </div>
                <div class="card-body">
                    <p><strong>Monday - Friday</strong><br>8:00 AM - 4:00 PM PST</p>
                    <p class="text-muted">(Excluding Court Holidays)</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5><i class="fas fa-map-marker-alt"></i> Mailing Address</h5>
                </div>
                <div class="card-body">
                    <address>
                        <strong>DummiesTrafficSchool.com</strong><br>
                        524 N. Mountain View Ave. #2<br>
                        San Bernardino, CA 92401
                    </address>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5><i class="fas fa-question-circle"></i> Quick Help</h5>
                </div>
                <div class="card-body">
                    <p>Before contacting us, check our <a href="/faq">FAQs page</a> for quick answers to common questions.</p>
                    <a href="/faq" class="btn btn-outline-primary">
                        <i class="fas fa-question-circle"></i> View FAQs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h5><i class="fas fa-headset"></i> Contact Methods</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                    <h6>ONLINE HELP</h6>
                    <p>Our FAQs page gives you quick access to the answers you need.</p>
                    <a href="/faq" class="btn btn-sm btn-primary">View FAQs</a>
                </div>
                <div class="col-md-3">
                    <i class="fas fa-envelope fa-3x text-success mb-3"></i>
                    <h6>EMAIL HELP</h6>
                    <p>Email us with your questions</p>
                    <a href="mailto:Support@DummiesTrafficSchool.com" class="btn btn-sm btn-success">Send Email</a>
                </div>
                <div class="col-md-3">
                    <i class="fas fa-phone fa-3x text-info mb-3"></i>
                    <h6>PHONE HELP</h6>
                    <p>Call us to speak with our support team</p>
                    <a href="tel:8773823700" class="btn btn-sm btn-info">Call Now</a>
                </div>
                <div class="col-md-3">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                    <h6>24/7 INFO LINE</h6>
                    <p>24-hour information line available</p>
                    <a href="tel:8773823700" class="btn btn-sm btn-warning">Call Anytime</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
