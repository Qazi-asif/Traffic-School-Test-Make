<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDataSeeder extends Seeder
{
    public function run()
    {
        // Create sample admin user (compatible with existing admin system)
        $adminUserId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@trafficschool.com',
            'password' => Hash::make('password123'),
            'phone' => '555-0001',
            'state_code' => 'florida',
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Note: admin_users table is handled by AdminUserSeeder with separate structure
        // This creates a regular user with admin role for backward compatibility

        // Create sample students across all states
        DB::table('users')->insert([
            // Florida Students
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0002',
                'date_of_birth' => '1990-01-15',
                'driver_license' => 'FL123456789',
                'state_code' => 'florida',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0003',
                'date_of_birth' => '1988-03-22',
                'driver_license' => 'FL987654321',
                'state_code' => 'florida',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Missouri Students
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0004',
                'date_of_birth' => '1985-05-20',
                'driver_license' => 'MO987654321',
                'state_code' => 'missouri',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0005',
                'date_of_birth' => '1992-07-10',
                'driver_license' => 'MO123456789',
                'state_code' => 'missouri',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Texas Students
            [
                'name' => 'Robert Johnson',
                'email' => 'robert.johnson@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0006',
                'date_of_birth' => '1987-09-15',
                'driver_license' => 'TX456789123',
                'state_code' => 'texas',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Jennifer Davis',
                'email' => 'jennifer.davis@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0007',
                'date_of_birth' => '1991-11-30',
                'driver_license' => 'TX789123456',
                'state_code' => 'texas',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Delaware Students
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0008',
                'date_of_birth' => '1989-04-12',
                'driver_license' => 'DE321654987',
                'state_code' => 'delaware',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'David Miller',
                'email' => 'david.miller@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0009',
                'date_of_birth' => '1986-12-05',
                'driver_license' => 'DE654987321',
                'state_code' => 'delaware',
                'role' => 'student',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Additional test users with various statuses
            [
                'name' => 'Lisa Wilson',
                'email' => 'lisa.wilson@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0010',
                'date_of_birth' => '1993-08-18',
                'driver_license' => 'FL555666777',
                'state_code' => 'florida',
                'role' => 'student',
                'is_active' => 0, // Inactive user for testing
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Christopher Moore',
                'email' => 'christopher.moore@example.com',
                'password' => Hash::make('password123'),
                'phone' => '555-0011',
                'date_of_birth' => '1984-02-28',
                'driver_license' => 'MO888999000',
                'state_code' => 'missouri',
                'role' => 'student',
                'is_active' => 1,
                'email_verified_at' => null, // Unverified email for testing
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Create system settings for each state
        DB::table('system_settings')->insert([
            [
                'key' => 'florida_course_timer_minutes',
                'value' => '480',
                'type' => 'integer',
                'group' => 'states',
                'description' => 'Florida course timer duration in minutes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'missouri_course_timer_minutes',
                'value' => '480',
                'type' => 'integer',
                'group' => 'states',
                'description' => 'Missouri course timer duration in minutes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'texas_course_timer_minutes',
                'value' => '480',
                'type' => 'integer',
                'group' => 'states',
                'description' => 'Texas course timer duration in minutes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'delaware_course_timer_minutes',
                'value' => '480',
                'type' => 'integer',
                'group' => 'states',
                'description' => 'Delaware course timer duration in minutes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_course_timer',
                'value' => true,
                'type' => 'boolean',
                'group' => 'courses',
                'description' => 'Enable course timer enforcement',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'minimum_passing_score',
                'value' => 80,
                'type' => 'integer',
                'group' => 'courses',
                'description' => 'Minimum passing score for all courses',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $this->command->info('Created sample users and system settings successfully.');
    }
}