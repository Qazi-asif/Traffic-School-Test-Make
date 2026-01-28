<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🌟 Starting State Data Seeding...');

        // FLORIDA DATA
        $this->command->info('📍 Seeding Florida courses...');
        $floridaCourseId = DB::table('florida_courses')->insertGetId([
            'title' => 'Florida Defensive Driving Course',
            'description' => 'FLHSMV approved 8-hour defensive driving course',
            'total_duration' => 480, // 8 hours in minutes
            'min_pass_score' => 80,
            'price' => 25.00,
            'dicds_course_id' => 'FL-BDI-001',
            'course_type' => 'BDI',
            'delivery_type' => 'Internet',
            'state_code' => 'FL',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Florida Chapters
        DB::table('florida_chapters')->insert([
            [
                'course_id' => $floridaCourseId,
                'title' => 'Chapter 1: Florida Traffic Laws',
                'content' => '<h2>Florida Traffic Laws</h2><p>Understanding Florida traffic laws and FLHSMV regulations...</p>',
                'duration_minutes' => 120,
                'order_index' => 1,
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $floridaCourseId,
                'title' => 'Chapter 2: Defensive Driving Techniques',
                'content' => '<h2>Defensive Driving</h2><p>Safe driving practices and accident prevention...</p>',
                'duration_minutes' => 120,
                'order_index' => 2,
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // MISSOURI DATA
        $this->command->info('📍 Seeding Missouri courses...');
        $missouriCourseId = DB::table('missouri_courses')->insertGetId([
            'title' => 'Missouri Point Reduction Course',
            'description' => 'Missouri approved point reduction program',
            'total_duration' => 480, // 8 hours in minutes
            'min_pass_score' => 80,
            'price' => 30.00,
            'course_type' => 'Point Reduction',
            'delivery_type' => 'Internet',
            'state_code' => 'MO',
            'requires_form_4444' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Missouri Chapters
        DB::table('missouri_chapters')->insert([
            [
                'course_id' => $missouriCourseId,
                'title' => 'Chapter 1: Missouri Traffic Laws',
                'content' => '<h2>Missouri Traffic Laws</h2><p>Understanding Missouri traffic regulations...</p>',
                'duration_minutes' => 120,
                'order_index' => 1,
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $missouriCourseId,
                'title' => 'Chapter 2: Point System and Safe Driving',
                'content' => '<h2>Point System</h2><p>Understanding Missouri point system and safe driving practices...</p>',
                'duration_minutes' => 120,
                'order_index' => 2,
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // TEXAS DATA
        $this->command->info('📍 Seeding Texas courses...');
        $texasCourseId = DB::table('texas_courses')->insertGetId([
            'title' => 'Texas Defensive Driving Course',
            'description' => 'Texas approved defensive driving course',
            'total_duration' => 360, // 6 hours in minutes
            'min_pass_score' => 70,
            'price' => 25.00,
            'tdlr_course_id' => 'TX-DD-001',
            'course_type' => 'defensive_driving',
            'delivery_type' => 'Internet',
            'state_code' => 'TX',
            'requires_proctoring' => 0,
            'defensive_driving_hours' => 6,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Texas Chapters
        DB::table('texas_chapters')->insert([
            [
                'course_id' => $texasCourseId,
                'title' => 'Chapter 1: Texas Traffic Laws',
                'content' => '<h2>Texas Traffic Laws</h2><p>Understanding Texas traffic regulations...</p>',
                'duration_minutes' => 90,
                'order_index' => 1,
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'requires_video_completion' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $texasCourseId,
                'title' => 'Chapter 2: Defensive Driving Fundamentals',
                'content' => '<h2>Defensive Driving</h2><p>Core principles of defensive driving...</p>',
                'duration_minutes' => 90,
                'order_index' => 2,
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'requires_video_completion' => 1,
                'video_url' => 'https://example.com/texas-defensive-video.mp4',
                'video_duration_minutes' => 15,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // DELAWARE DATA
        $this->command->info('📍 Seeding Delaware courses...');
        $delawareCourseId = DB::table('delaware_courses')->insertGetId([
            'title' => 'Delaware Driver Improvement Course',
            'description' => 'Delaware approved driver improvement program',
            'total_duration' => 360, // 6 hours in minutes
            'min_pass_score' => 80,
            'price' => 35.00,
            'dmv_course_id' => 'DE-DD-001',
            'course_type' => 'defensive_driving',
            'delivery_type' => 'Internet',
            'state_code' => 'DE',
            'quiz_rotation_enabled' => 1,
            'insurance_discount_eligible' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Delaware Chapters
        DB::table('delaware_chapters')->insert([
            [
                'course_id' => $delawareCourseId,
                'title' => 'Chapter 1: Delaware Traffic Laws',
                'content' => '<h2>Delaware Traffic Laws</h2><p>Understanding Delaware traffic regulations...</p>',
                'duration_minutes' => 90,
                'order_index' => 1,
                'quiz_rotation_set' => 'A',
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'has_interactive_content' => 1,
                'interactive_content_url' => 'https://example.com/delaware-interactive-1',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $delawareCourseId,
                'title' => 'Chapter 2: Defensive Driving Techniques',
                'content' => '<h2>Defensive Driving</h2><p>Proven defensive driving techniques...</p>',
                'duration_minutes' => 90,
                'order_index' => 2,
                'quiz_rotation_set' => 'B',
                'is_active' => 1,
                'enforce_minimum_time' => 1,
                'has_interactive_content' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $this->command->info('✅ State data seeding completed successfully!');
        $this->displaySeedingSummary();
    }

    /**
     * Display seeding summary
     */
    private function displaySeedingSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SEEDING SUMMARY:');
        $this->command->info('==================');
        
        // Count courses by state
        $floridaCourses = DB::table('florida_courses')->count();
        $missouriCourses = DB::table('missouri_courses')->count();
        $texasCourses = DB::table('texas_courses')->count();
        $delawareCourses = DB::table('delaware_courses')->count();
        
        $this->command->info("🏛️  Florida: {$floridaCourses} courses created");
        $this->command->info("🏛️  Missouri: {$missouriCourses} courses created");
        $this->command->info("🏛️  Texas: {$texasCourses} courses created");
        $this->command->info("🏛️  Delaware: {$delawareCourses} courses created");
        
        $totalCourses = $floridaCourses + $missouriCourses + $texasCourses + $delawareCourses;
        $this->command->info("📚 Total courses: {$totalCourses}");
        
        // Count chapters
        $floridaChapters = DB::table('florida_chapters')->count();
        $missouriChapters = DB::table('missouri_chapters')->count();
        $texasChapters = DB::table('texas_chapters')->count();
        $delawareChapters = DB::table('delaware_chapters')->count();
        
        $totalChapters = $floridaChapters + $missouriChapters + $texasChapters + $delawareChapters;
        $this->command->info("📖 Total chapters: {$totalChapters}");
        
        $this->command->info('');
        $this->command->info('🎯 Ready to test state-specific course functionality!');
        $this->command->info('');
    }
}