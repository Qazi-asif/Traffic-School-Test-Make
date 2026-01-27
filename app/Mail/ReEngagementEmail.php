<?php

namespace App\Mail;

use App\Models\UserCourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReEngagementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $enrollment;

    public function __construct(UserCourseEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function build()
    {
        return $this->subject('We Miss You! Complete Your Course')
            ->view('emails.reminders.re-engagement');
    }
}
