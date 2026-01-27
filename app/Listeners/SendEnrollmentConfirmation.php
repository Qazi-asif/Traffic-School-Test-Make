<?php

namespace App\Listeners;

use App\Events\UserEnrolled;
use App\Notifications\EnrollmentConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEnrollmentConfirmation implements ShouldQueue
{
    public function handle(UserEnrolled $event)
    {
        $event->enrollment->user->notify(new EnrollmentConfirmation($event->enrollment));
    }
}
