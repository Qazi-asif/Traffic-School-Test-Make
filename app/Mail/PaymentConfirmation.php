<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Payment;
use App\Models\UserCourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $payment;
    public $enrollment;

    public function __construct(User $user, $course, Payment $payment, UserCourseEnrollment $enrollment)
    {
        $this->user = $user;
        $this->course = $course;
        $this->payment = $payment;
        $this->enrollment = $enrollment;
    }

    public function build()
    {
        return $this->subject('Payment Confirmation - ' . $this->course->title)
                    ->view('emails.payment-confirmation')
                    ->with([
                        'user' => $this->user,
                        'course' => $this->course,
                        'payment' => $this->payment,
                        'enrollment' => $this->enrollment,
                        'courseUrl' => route('course.player', $this->enrollment->id),
                    ]);
    }
}