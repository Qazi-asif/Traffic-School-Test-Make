<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCourseCompletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'course:check-completion {enrollment_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update course completion status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enrollmentId = $this->argument('enrollment_id');

        if ($enrollmentId) {
            $enrollments = [\App\Models\UserCourseEnrollment::findOrFail($enrollmentId)];
        } else {
            $enrollments = \App\Models\UserCourseEnrollment::where('status', 'active')->get();
        }

        $this->info('Checking '.count($enrollments).' enrollment(s)...');

        foreach ($enrollments as $enrollment) {
            $enrollment->load(['course', 'progress']);

            $totalChapters = $enrollment->course->chapters()->count();
            $completedChapters = $enrollment->progress()->where('is_completed', true)->count();

            $progressPercentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;

            $this->line("Enrollment #{$enrollment->id}:");
            $this->line("  User: {$enrollment->user->first_name} {$enrollment->user->last_name}");
            $this->line("  Course: {$enrollment->course->title}");
            $this->line("  Total Chapters: {$totalChapters}");
            $this->line("  Completed: {$completedChapters}");
            $this->line("  Progress: {$progressPercentage}%");
            $this->line("  Current Status: {$enrollment->status}");

            if ($progressPercentage == 100 && $enrollment->status !== 'completed') {
                $enrollment->update([
                    'progress_percentage' => $progressPercentage,
                    'completed_at' => now(),
                    'status' => 'completed',
                ]);
                $this->info('  ✓ Updated to COMPLETED!');

                // Check if certificate exists
                $hasCert = $enrollment->floridaCertificate()->exists();
                $this->line('  Certificate: '.($hasCert ? 'EXISTS' : 'WILL BE AUTO-GENERATED'));
            } else {
                $this->line('  Status: No change needed');
            }

            $this->line('');
        }

        $this->info('Done!');
    }
}
