<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompleteSystemSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Seeding complete system data...\n";
        
        // USERS DATA
        $adminUserId = DB::table('users')->insertGetId([
            'name' => 'System Admin',
            'email' => 'admin@trafficschool.com',
            'password' => Hash::make('admin123'),
            'phone' => '555-0001',
            'state_code' => 'florida',
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('admin_users')->insert([
            'user_id' => $adminUserId,
            'permissions' => json_encode(['manage_all']),
            'can_manage_states' => json_encode(['florida', 'missouri', 'texas', 'delaware']),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // FLORIDA DATA
        $floridaCourseId = DB::table('florida_courses')->insertGetId([
            'title' => 'Florida Defensive Driving Course',
            'description' => 'FLHSMV approved defensive driving course',
            'duration_hours' => 8,
            'passing_score' => 80,
            'price' => 25.00,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('florida_chapters')->insert([
            [
                'course_id' => $floridaCourseId,
                'title' => 'Chapter 1: Florida Traffic Laws',
                'content' => 'Understanding Florida traffic laws and FLHSMV regulations...',
                'video_url' => '/videos/florida/chapter1.mp4',
                'duration_minutes' => 45,
                'order_number' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $floridaCourseId,
                'title' => 'Chapter 2: Safe Driving Practices',
                'content' => 'Safe driving practices and accident prevention...',
                'video_url' => '/videos/florida/chapter2.mp4',
                'duration_minutes' => 50,
                'order_number' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // MISSOURI DATA
        $missouriCourseId = DB::table('missouri_courses')->insertGetId([
            'title' => 'Missouri Point Reduction Course',
            'description' => 'Missouri approved point reduction program',
            'duration_hours' => 8,
            'passing_score' => 80,
            'price' => 30.00,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('missouri_chapters')->insert([
            'course_id' => $missouriCourseId,
            'title' => 'Chapter 1: Missouri Traffic Laws',
            'content' => 'Understanding Missouri traffic regulations...',
            'video_url' => '/videos/missouri/chapter1.mp4',
            'duration_minutes' => 45,
            'order_number' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // TEXAS DATA
        $texasCourseId = DB::table('texas_courses')->insertGetId([
            'title' => 'Texas Defensive Driving Course',
            'description' => 'Texas approved defensive driving course',
            'duration_hours' => 6,
            'passing_score' => 70,
            'price' => 25.00,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // DELAWARE DATA
        $delawareCourseId = DB::table('delaware_courses')->insertGetId([
            'title' => 'Delaware Driver Improvement Course',
            'description' => 'Delaware approved driver improvement program',
            'duration_hours' => 8,
            'passing_score' => 80,
            'price' => 35.00,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // SYSTEM SETTINGS
        DB::table('system_settings')->insert([
            [
                'state_code' => 'florida',
                'setting_key' => 'timer_minutes',
                'setting_value' => '480',
                'description' => 'Course timer duration',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'state_code' => 'global',
                'setting_key' => 'site_name',
                'setting_value' => 'Multi-State Traffic School',
                'description' => 'Site name',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        echo "✅ Sample data seeded successfully!\n";
    }
}