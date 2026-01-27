<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
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
            return;
        }

        // Get all Missouri courses
        $missouriCourses = DB::table('florida_courses')
            ->where('state', 'Missouri')
            ->pluck('id');

        // Update all enrollments to point to the correct course
        DB::table('user_course_enrollments')
            ->whereIn('course_id', $missouriCourses)
            ->where('course_id', '!=', $correctCourse->id)
            ->update(['course_id' => $correctCourse->id]);

        // Delete duplicate Missouri courses (keep the one with chapters)
        DB::table('florida_courses')
            ->where('state', 'Missouri')
            ->where('id', '!=', $correctCourse->id)
            ->delete();
    }

    public function down()
    {
        // Cannot reverse this migration
    }
};
