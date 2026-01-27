<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMissouriCourses extends Command
{
    protected $signature = 'fix:missouri-courses';

    protected $description = 'Fix Missouri course enrollments to point to correct course with chapters';

    public function handle()
    {
        $this->info('Fixing Missouri course enrollments...');

        // Find the Missouri course that has chapters
        $correctCourse = DB::table('florida_courses')
            ->where('state', 'Missouri')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('chapters')
                    ->whereColumn('chapters.course_id', 'florida_courses.id');
            })
            ->first();

        if (! $correctCourse) {
            $this->error('No Missouri course with chapters found!');

            return 1;
        }

        $this->info("Correct Missouri course ID: {$correctCourse->id} - {$correctCourse->title}");

        // Get all Missouri courses
        $missouriCourses = DB::table('florida_courses')
            ->where('state', 'Missouri')
            ->pluck('id')
            ->toArray();

        $this->info('All Missouri course IDs: '.implode(', ', $missouriCourses));

        // Update all enrollments to point to the correct course
        $updated = DB::table('user_course_enrollments')
            ->whereIn('course_id', $missouriCourses)
            ->where('course_id', '!=', $correctCourse->id)
            ->update(['course_id' => $correctCourse->id]);

        $this->info("Updated {$updated} enrollments");

        // Delete duplicate Missouri courses (keep the one with chapters)
        $deleted = DB::table('florida_courses')
            ->where('state', 'Missouri')
            ->where('id', '!=', $correctCourse->id)
            ->delete();

        $this->info("Deleted {$deleted} duplicate Missouri courses");
        $this->info('✓ Done!');

        return 0;
    }
}
