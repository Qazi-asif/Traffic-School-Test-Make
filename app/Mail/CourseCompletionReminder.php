<?php

namespace App\Mail;

use App\Models\UserCourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseCompletionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $enrollment;

    public function __construct(UserCourseEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function build()
    {
        return $this->subject('Complete Your Course - Reminder')
            ->view('emails.reminders.course-completion');
    }
}
