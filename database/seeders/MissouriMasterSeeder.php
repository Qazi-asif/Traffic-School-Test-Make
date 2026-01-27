<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MissouriMasterSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            MissouriCourseContentSeeder::class,  // Create course and chapters first
            MissouriQuizSeeder::class,
            MissouriQuizSeederPart2::class,
            MissouriQuizSeederPart3::class,
            MissouriFinalExamSeeder::class,
            MissouriFaqSeeder::class,
        ]);

        $this->command->info('✅ Missouri course, chapters, quizzes, and FAQs seeded successfully!');
    }
}
