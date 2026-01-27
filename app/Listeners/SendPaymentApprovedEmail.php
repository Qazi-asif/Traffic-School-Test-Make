<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use App\Notifications\PaymentApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentApprovedEmail implements ShouldQueue
{
    public function handle(PaymentApproved $event)
    {
        $event->payment->user->notify(new PaymentApprovedNotification($event->payment));
    }
}
