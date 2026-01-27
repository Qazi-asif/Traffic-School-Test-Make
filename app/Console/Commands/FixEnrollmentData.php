<?php

namespace App\Console\Commands;

use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;

class FixEnrollmentData extends Command
{
    protected $signature = 'fix:enrollment-data';
    protected $description = 'Copy missing user data to enrollment records';

    public function handle()
    {
        $this->info('🔧 Fixing enrollment data by copying from user profiles...');

        $enrollments = UserCourseEnrollment::with('user')->get();
        $fixed = 0;

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            $updates = [];

            // Copy citation and court information if missing in enrollment
            if (!$enrollment->case_number && $user->case_number) {
                $updates['case_number'] = $user->case_number;
            }
            
            if (!$enrollment->citation_number && $user->citation_number) {
                $updates['citation_number'] = $user->citation_number;
            }
            
            if (!$enrollment->court_state && $user->state) {
                $updates['court_state'] = $user->state;
            }
            
            if (!$enrollment->court_county && $user->court_county) {
                $updates['court_county'] = $user->court_county;
            }
            
            if (!$enrollment->court_selected && $user->court_selected) {
                $updates['court_selected'] = $user->court_selected;
            }

            // Update enrollment if there are changes
            if (!empty($updates)) {
                $enrollment->update($updates);
                $fixed++;
                $this->info("  ✅ Fixed enrollment ID: {$enrollment->id} for user: {$user->first_name} {$user->last_name}");
            }
        }

        $this->info("🎉 Fixed {$fixed} enrollment records!");
        
        return 0;
    }
}