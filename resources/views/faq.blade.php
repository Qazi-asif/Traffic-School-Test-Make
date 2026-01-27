@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <h1 class="mb-4"><i class="fas fa-question-circle"></i> Frequently Asked Questions</h1>
    
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    How do I enroll in a course?
                </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Browse available courses, select your course, and complete the enrollment process with payment.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    How long do I have to complete the course?
                </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Course completion times vary by state requirements. Check your specific course details for time limits.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    When will I receive my certificate?
                </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Certificates are generated immediately upon course completion and sent to your email in PDF format. We also submit completion data to the DMV electronically.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                    What are your office hours?
                </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Monday through Friday, 8:00 AM - 4:00 PM PST (Excluding Court Holidays). We also have a 24-hour information line available at (877) 382-3700.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                    How do I contact support?
                </button>
            </h2>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <strong>Email:</strong> Support@DummiesTrafficSchool.com<br>
                    <strong>Phone:</strong> (877) 382-3700<br>
                    <strong>TTY/TDD:</strong> (877) 735-2929<br>
                    <strong>Fax:</strong> (310) 388-0828
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                    What is your refund policy?
                </button>
            </h2>
            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Please refer to our <a href="/refund-policy">Refund Policy</a> page for detailed information.
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5>Still have questions?</h5>
            <p>Contact our support team:</p>
            <a href="mailto:Support@DummiesTrafficSchool.com" class="btn btn-primary">
                <i class="fas fa-envelope"></i> Email Support
            </a>
            <a href="tel:8773823700" class="btn btn-success ms-2">
                <i class="fas fa-phone"></i> Call (877) 382-3700
            </a>
        </div>
    </div>
</div>
@endsection
