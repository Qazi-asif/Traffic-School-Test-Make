<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

class StateSpecificCoursesSeeder extends Seeder
{
    public function run(): void
    {
        // Create base courses for each state if they don't exist
        $states = [
            'Missouri' => [
                'title' => 'Missouri Defensive Driving Course',
                'description' => 'State-approved defensive driving course for Missouri',
                'state' => 'Missouri',
                'duration' => 480, // 8 hours in minutes
                'price' => 29.95,
                'passing_score' => 80,
                'course_type' => 'defensive_driving',
            ],
            'Texas' => [
                'title' => 'Texas Defensive Driving Course',
                'description' => 'TDLR-approved defensive driving course for Texas',
                'state' => 'Texas',
                'duration' => 360, // 6 hours in minutes
                'price' => 25.95,
                'passing_score' => 70,
                'course_type' => 'defensive_driving',
            ],
            'Delaware' => [
                'title' => 'Delaware Defensive Driving Course',
                'description' => 'DMV-approved defensive driving course for Delaware',
                'state' => 'Delaware',
                'duration' => 480, // 8 hours in minutes
                'price' => 24.95,
                'passing_score' => 80,
                'course_type' => 'defensive_driving',
            ],
        ];

        foreach ($states as $state => $courseData) {
            // Create or find the base course
            $baseCourse = Course::firstOrCreate(
                ['title' => $courseData['title'], 'state' => $state],
                $courseData
            );

            // Create state-specific course record
            switch ($state) {
                case 'Missouri':
                    DB::table('missouri_courses')->insertOrIgnore([
                        'course_id' => $baseCourse->id,
                        'missouri_course_code' => 'MO-DD-001',
                        'course_type' => 'defensive_driving',
                        'form_4444_template' => 'missouri_form_4444_template.pdf',
                        'requires_form_4444' => true,
                        'required_hours' => 8.00,
                        'max_completion_days' => 90,
                        'approval_number' => 'MO-2024-DD-001',
                        'approved_date' => '2024-01-01',
                        'expiration_date' => '2025-12-31',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'Texas':
                    DB::table('texas_courses')->insertOrIgnore([
                        'course_id' => $baseCourse->id,
                        'texas_course_code' => 'TX-DD-001',
                        'tdlr_course_id' => 'TDLR-CP-001',
                        'course_type' => 'defensive_driving',
                        'requires_proctoring' => false,
                        'defensive_driving_hours' => 6,
                        'required_hours' => 6.00,
                        'max_completion_days' => 90,
                        'approval_number' => 'TX-2024-DD-001',
                        'approved_date' => '2024-01-01',
                        'expiration_date' => '2025-12-31',
                        'certificate_template' => 'texas_certificate_template.pdf',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'Delaware':
                    DB::table('delaware_courses')->insertOrIgnore([
                        'course_id' => $baseCourse->id,
                        'delaware_course_code' => 'DE-DD-001',
                        'course_type' => 'defensive_driving',
                        'required_hours' => 8.00,
                        'max_completion_days' => 90,
                        'approval_number' => 'DE-2024-DD-001',
                        'approved_date' => '2024-01-01',
                        'expiration_date' => '2025-12-31',
                        'certificate_template' => 'delaware_certificate_template.pdf',
                        'quiz_rotation_enabled' => true,
                        'quiz_pool_size' => 50,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
            }
        }

        $this->command->info('State-specific courses seeded successfully!');
    }
}