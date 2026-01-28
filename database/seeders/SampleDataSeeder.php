<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Starting sample data seeding...');

        try {
            // Create sample chapters for existing Florida courses
            $this->createSampleChapters();
            
            // Create sample enrollments
            $this->createSampleEnrollments();
            
            // Migrate existing courses to other state tables (sample data)
            $this->createSampleStateData();
            
            Log::info('Sample data seeding completed successfully');
        } catch (\Exception $e) {
            Log::error('Sample data seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createSampleChapters()
    {
        // Get existing Florida courses
        $floridaCourses = DB::table('florida_courses')->limit(3)->get();
        
        foreach ($floridaCourses as $course) {
            // Create sample chapters for each course
            $chapters = [
                [
                    'title' => 'Introduction to Defensive Driving',
                    'content' => 'This chapter covers the basics of defensive driving techniques and safety awareness.',
                    'duration' => 30,
                    'required_min_time' => 25,
                    'order_index' => 1
                ],
                [
                    'title' => 'Traffic Laws and Regulations',
                    'content' => 'Understanding state-specific traffic laws and regulations.',
                    'duration' => 45,
                    'required_min_time' => 40,
                    'order_index' => 2
                ],
                [
                    'title' => 'Hazard Recognition',
                    'content' => 'Learning to identify and respond to potential road hazards.',
                    'duration' => 35,
                    'required_min_time' => 30,
                    'order_index' => 3
                ]
            ];
            
            foreach ($chapters as $chapterData) {
                DB::table('chapters')->insert([
                    'course_id' => $course->id,
                    'course_table' => 'florida_courses',
                    'title' => $chapterData['title'],
                    'content' => $chapterData['content'],
                    'duration' => $chapterData['duration'],
                    'required_min_time' => $chapterData['required_min_time'],
                    'order_index' => $chapterData['order_index'],
                    'is_active' => true,
                    'has_quiz' => true,
                    'quiz_questions_count' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        Log::info('Sample chapters created for Florida courses');
    }

    private function createSampleEnrollments()
    {
        // Get existing users (if any)
        $users = DB::table('users')->limit(5)->get();
        $floridaCourses = DB::table('florida_courses')->limit(2)->get();
        
        if ($users->count() > 0 && $floridaCourses->count() > 0) {
            foreach ($users as $user) {
                foreach ($floridaCourses as $course) {
                    DB::table('user_course_enrollments')->insert([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'course_table' => 'florida_courses',
                        'status' => 'in_progress',
                        'payment_status' => 'paid',
                        'progress_percentage' => rand(10, 80),
                        'enrolled_at' => now()->subDays(rand(1, 30)),
                        'started_at' => now()->subDays(rand(1, 25)),
                        'attempts' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            Log::info('Sample enrollments created');
        } else {
            Log::info('No users found, skipping enrollment creation');
        }
    }

    private function createSampleStateData()
    {
        // Create sample courses in the main courses table for other states
        $sampleCourses = [
            [
                'title' => 'Missouri Defensive Driving Course',
                'description' => 'State-approved defensive driving course for Missouri residents.',
                'state' => 'Missouri',
                'state_code' => 'MO',
                'duration' => 480, // 8 hours
                'price' => 29.95,
                'passing_score' => 80,
                'is_active' => true,
                'course_type' => 'defensive_driving'
            ],
            [
                'title' => 'Texas Defensive Driving Course',
                'description' => 'TDLR-approved defensive driving course for Texas residents.',
                'state' => 'Texas',
                'state_code' => 'TX',
                'duration' => 360, // 6 hours
                'price' => 25.00,
                'passing_score' => 70,
                'is_active' => true,
                'course_type' => 'defensive_driving'
            ],
            [
                'title' => 'Delaware Defensive Driving Course',
                'description' => 'State-approved defensive driving course for Delaware residents.',
                'state' => 'Delaware',
                'state_code' => 'DE',
                'duration' => 480, // 8 hours
                'price' => 35.00,
                'passing_score' => 80,
                'is_active' => true,
                'course_type' => 'defensive_driving'
            ]
        ];

        foreach ($sampleCourses as $courseData) {
            // Insert into main courses table
            $courseId = DB::table('courses')->insertGetId(array_merge($courseData, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Insert into appropriate state table
            $stateTable = strtolower($courseData['state']) . '_courses';
            $stateCode = strtoupper(substr($courseData['state'], 0, 2));
            
            if (DB::getSchemaBuilder()->hasTable($stateTable)) {
                DB::table($stateTable)->insert([
                    'course_id' => $courseId,
                    $stateTable === 'missouri_courses' ? 'missouri_course_code' : 
                    ($stateTable === 'texas_courses' ? 'texas_course_code' : 
                    ($stateTable === 'delaware_courses' ? 'delaware_course_code' : 'nevada_course_code')) => $stateCode . '-' . $courseId,
                    'course_type' => 'defensive_driving',
                    'required_hours' => $courseData['duration'] / 60,
                    'max_completion_days' => 90,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info("Created sample course for {$courseData['state']}");
            }
        }
    }
}