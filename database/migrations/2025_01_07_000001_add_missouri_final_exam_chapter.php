<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $course = DB::table('courses')->where('title', 'LIKE', '%Missouri%')->first();

        if ($course) {
            DB::table('chapters')->insert([
                'course_id' => $course->id,
                'title' => 'Final Exam',
                'content' => 'Missouri Final Exam - 50 Questions',
                'order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        $course = DB::table('courses')->where('title', 'LIKE', '%Missouri%')->first();

        if ($course) {
            DB::table('chapters')
                ->where('course_id', $course->id)
                ->where('order', 11)
                ->delete();
        }
    }
};
