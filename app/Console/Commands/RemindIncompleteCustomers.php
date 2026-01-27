<?php

namespace App\Console\Commands;

use App\Mail\CourseCompletionReminder;
use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindIncompleteCustomers extends Command
{
    protected $signature = 'customers:remind-incomplete {--days=7 : Days since payment}';

    protected $description = 'Send reminders to paid customers who haven\'t completed their course';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $enrollments = UserCourseEnrollment::with(['user', 'course'])
            ->paidNotCompleted()
            ->where('enrolled_at', '<=', $cutoffDate)
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->subDays(7));
            })
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            try {
                Mail::to($enrollment->user->email)->send(new CourseCompletionReminder($enrollment));

                $enrollment->update([
                    'reminder_sent_at' => now(),
                    'reminder_count' => $enrollment->reminder_count + 1,
                ]);

                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to send reminder to {$enrollment->user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$count} reminders to incomplete customers.");

        return 0;
    }
}
