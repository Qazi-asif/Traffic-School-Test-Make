<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Notifications\CourseCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCourseCompletedEmail implements ShouldQueue
{
    public function handle(CourseCompleted $event)
    {
        $event->enrollment->user->notify(new CourseCompletedNotification($event->enrollment));
    }
}
