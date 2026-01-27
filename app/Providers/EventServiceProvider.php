<?php

namespace App\Providers;

use App\Events\CertificateGenerated;
use App\Events\CourseCompleted;
use App\Events\PaymentApproved;
use App\Events\SurveyCompleted;
use App\Events\UserEnrolled;
use App\Listeners\CreateStateTransmission;
use App\Listeners\LogSurveyCompletion;
use App\Listeners\SendCertificateEmail;
use App\Listeners\SendCourseCompletedEmail;
use App\Listeners\SendEnrollmentConfirmation;
use App\Listeners\SendPaymentApprovedEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserEnrolled::class => [
            SendEnrollmentConfirmation::class,
        ],
        PaymentApproved::class => [
            SendPaymentApprovedEmail::class,
        ],
        CourseCompleted::class => [
            SendCourseCompletedEmail::class,
            CreateStateTransmission::class,
        ],
        CertificateGenerated::class => [
            SendCertificateEmail::class,
        ],
        SurveyCompleted::class => [
            LogSurveyCompletion::class,
        ],
    ];

    public function boot()
    {
        //
    }
}
