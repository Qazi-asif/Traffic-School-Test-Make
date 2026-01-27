<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateCourseTitle extends Seeder
{
    public function run()
    {
        DB::table('florida_courses')
            ->where('id', 1)
            ->update([
                'title' => '4-Hour Florida BDI Course',
                'updated_at' => now(),
            ]);

        $this->command->info('Course title updated!');
    }
}
