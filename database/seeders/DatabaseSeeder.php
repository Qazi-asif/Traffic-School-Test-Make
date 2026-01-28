<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
<<<<<<< HEAD
            RoleSeeder::class,
=======
            // Core System Seeders
            RoleSeeder::class,
            AdminUserSeeder::class, // Admin accounts
            SystemSettingSeeder::class, // System configuration
            UserDataSeeder::class, // Sample student users
            
            // Course Content Seeders
>>>>>>> e8fe972 (Humayun Work)
            FloridaRolesSeeder::class,
            CourseSeeder::class, // Basic Florida BDI course
            FloridaBDICourseSeeder::class, // 4-Hour Florida BDI Course
            FloridaDefensiveDrivingMasterSeeder::class, // Florida 6-Hour Defensive Driving
            MissouriMasterSeeder::class, // Missouri courses
            TexasMasterSeeder::class, // Texas Defensive Driving
            DelawareMasterSeeder::class, // Delaware 3 courses
<<<<<<< HEAD
=======
            
            // Additional System Data
>>>>>>> e8fe972 (Humayun Work)
            PrivacyPolicySeeder::class,
            StateSeeder::class,
            EmailTemplateSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
