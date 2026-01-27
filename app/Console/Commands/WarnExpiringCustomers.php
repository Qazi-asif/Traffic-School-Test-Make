<?php

namespace App\Console\Commands;

use App\Mail\ExpirationWarning;
use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WarnExpiringCustomers extends Command
{
    protected $signature = 'customers:warn-expiring {--days=7 : Days until expiration}';

    protected $description = 'Send expiration warnings to customers with courses expiring soon';

    public function handle()
    {
        $days = $this->option('days');

        $enrollments = UserCourseEnrollment::with(['user', 'course'])
            ->expiringWithin($days)
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->subDays(3));
            })
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            try {
                Mail::to($enrollment->user->email)->send(new ExpirationWarning($enrollment));

                $enrollment->update([
                    'reminder_sent_at' => now(),
                    'reminder_count' => $enrollment->reminder_count + 1,
                ]);

                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to send warning to {$enrollment->user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$count} expiration warnings.");

        return 0;
    }
}
