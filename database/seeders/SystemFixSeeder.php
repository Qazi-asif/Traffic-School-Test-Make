<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemFixSeeder extends Seeder
{
    public function run()
    {
        // 1. Fix user roles
        $this->fixUserRoles();
        
        // 2. Create sample courses if none exist
        $this->createSampleCourses();
        
        // 3. Ensure admin user exists
        $this->ensureAdminUser();
    }
    
    private function fixUserRoles()
    {
        // Update users without roles
        DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => 'user']);
            
        echo "Fixed user roles\n";
    }
    
    private function createSampleCourses()
    {
        $courseCount = DB::table('florida_courses')->count();
        
        if ($courseCount === 0) {
            $sampleCourses = [
                [
                    'title' => 'Florida Basic Driver Improvement (BDI)',
                    'description' => 'State-approved 4-hour Basic Driver Improvement course for Florida drivers.',
                    'state' => 'FL',
                    'state_code' => 'FL',
                    'duration' => 240,
                    'total_duration' => 240,
                    'price' => 29.99,
                    'passing_score' => 80,
                    'min_pass_score' => 80,
                    'is_active' => true,
                    'course_type' => 'BDI',
                    'delivery_type' => 'Online',
                    'dicds_course_id' => 'FL_BDI_001',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Florida Advanced Driver Improvement (ADI)',
                    'description' => 'State-approved 12-hour Advanced Driver Improvement course for serious violations.',
                    'state' => 'FL',
                    'state_code' => 'FL',
                    'duration' => 720,
                    'total_duration' => 720,
                    'price' => 79.99,
                    'passing_score' => 80,
                    'min_pass_score' => 80,
                    'is_active' => true,
                    'course_type' => 'ADI',
                    'delivery_type' => 'Online',
                    'dicds_course_id' => 'FL_ADI_001',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ];
            
            foreach ($sampleCourses as $course) {
                DB::table('florida_courses')->insert($course);
            }
            
            echo "Created sample courses\n";
        }
    }
    
    private function ensureAdminUser()
    {
        $adminCount = DB::table('users')
            ->whereIn('role', ['admin', 'super-admin'])
            ->count();
            
        if ($adminCount === 0) {
            $firstUser = DB::table('users')->first();
            
            if ($firstUser) {
                DB::table('users')
                    ->where('id', $firstUser->id)
                    ->update(['role' => 'super-admin']);
                    
                echo "Made user '{$firstUser->email}' a super-admin\n";
            } else {
                // Create default admin user
                DB::table('users')->insert([
                    'name' => 'System Administrator',
                    'email' => 'admin@trafficschool.com',
                    'password' => Hash::make('admin123'),
                    'role' => 'super-admin',
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "Created default admin user: admin@trafficschool.com / admin123\n";
            }
        }
    }
}