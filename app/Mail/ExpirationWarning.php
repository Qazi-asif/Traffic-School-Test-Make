<?php

namespace App\Mail;

use App\Models\UserCourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpirationWarning extends Mailable
{
    use Queueable, SerializesModels;

    public $enrollment;

    public function __construct(UserCourseEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function build()
    {
        $daysRemaining = $this->enrollment->court_date ? $this->enrollment->court_date->diffInDays(now()) : 0;

        return $this->subject("Your Course Expires in {$daysRemaining} Days")
            ->view('emails.reminders.expiration-warning');
    }
}
