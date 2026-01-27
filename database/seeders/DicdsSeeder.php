<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\DicdsUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DicdsSeeder extends Seeder
{
    public function run()
    {
        // Create default courses
        $courses = [
            ['course_type' => 'BDI', 'delivery_type' => 'In Person', 'description' => 'Basic Driver Improvement - In Person'],
            ['course_type' => 'BDI', 'delivery_type' => 'Internet', 'description' => 'Basic Driver Improvement - Online'],
            ['course_type' => 'ADI', 'delivery_type' => 'In Person', 'description' => 'Advanced Driver Improvement - In Person'],
            ['course_type' => 'ADI', 'delivery_type' => 'Internet', 'description' => 'Advanced Driver Improvement - Online'],
            ['course_type' => 'TLSAE', 'delivery_type' => 'In Person', 'description' => 'Traffic Law and Substance Abuse Education - In Person'],
            ['course_type' => 'TLSAE', 'delivery_type' => 'Internet', 'description' => 'Traffic Law and Substance Abuse Education - Online'],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate($course);
        }

        // Create test admin user
        DicdsUser::firstOrCreate(
            ['login_id' => 'TestAdmin'],
            [
                'user_last_name' => 'Administrator',
                'first_name' => 'Test',
                'contact_email' => 'admin@test.com',
                'retype_email' => 'admin@test.com',
                'phone_number' => '5551234567',
                'password' => Hash::make('TestPass123!'),
                'desired_application' => 'Driver School Certificates',
                'desired_role' => 'DRS_Provider_Admin',
                'user_group' => 'TEST PROVIDER',
                'status' => 'Active',
                'approved_at' => now(),
            ]
        );
    }
}
