<?php

namespace App\Notifications;

use App\Models\UserCourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseCompletedNotification extends Notification
{
    use Queueable;

    public $enrollment;

    public function __construct(UserCourseEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $course = $this->enrollment->course ?? $this->enrollment->floridaCourse;

        return (new MailMessage)
            ->subject('Congratulations! Course Completed - '.$course->title)
            ->view('emails.courses.completed', [
                'user' => $notifiable,
                'enrollment' => $this->enrollment,
                'course' => $course,
            ]);
    }
}
