@extends('layouts.app')

@section('title', 'Refund Policy')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <h1 class="mb-4"><i class="fas fa-undo"></i> Refund and Cancellation Policy</h1>
    
    <div class="card">
        <div class="card-body">
            <h3>Refund Policy</h3>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Important:</strong> Refund eligibility depends on your course progress and state-specific regulations.
            </div>
            
            <h4 class="mt-4">1. Full Refund</h4>
            <p>Available if requested before accessing any course materials. To request a full refund, contact us immediately after enrollment.</p>
            
            <h4 class="mt-4">2. Partial Refund</h4>
            <p>May be available if less than 25% of the course has been completed, subject to state regulations. Partial refunds are evaluated on a case-by-case basis.</p>
            
            <h4 class="mt-4">3. No Refund</h4>
            <p>Once you have completed more than 25% of the course or received your certificate, no refunds will be issued. This policy ensures compliance with state regulations.</p>
            
            <h4 class="mt-4">4. Processing Time</h4>
            <p>Approved refunds are processed within 7-10 business days. Refunds will be issued to the original payment method.</p>
            
            <h4 class="mt-4">5. State-Specific Rules</h4>
            <p>Some states have specific refund requirements that supersede this policy. We comply with all state-mandated refund regulations.</p>
            
            <hr class="my-4">
            
            <h3>Cancellation Policy</h3>
            <p>You may cancel your enrollment at any time by contacting our support team. Refund eligibility will be determined based on the refund policy above.</p>
            
            <h4 class="mt-4">How to Request a Refund or Cancellation</h4>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                            <h5>Email</h5>
                            <p><a href="mailto:Support@DummiesTrafficSchool.com">Support@DummiesTrafficSchool.com</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-phone fa-3x text-success mb-3"></i>
                            <h5>Phone</h5>
                            <p><a href="tel:8773823700">(877) 382-3700</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-3x text-info mb-3"></i>
                            <h5>Office Hours</h5>
                            <p>Mon-Fri<br>8am-4pm PST</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Please include your full name, email address, and order number when requesting a refund.
            </div>
        </div>
    </div>
</div>
@endsection
