<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            FloridaRolesSeeder::class,
            CourseSeeder::class, // Basic Florida BDI course
            FloridaBDICourseSeeder::class, // 4-Hour Florida BDI Course
            FloridaDefensiveDrivingMasterSeeder::class, // Florida 6-Hour Defensive Driving
            MissouriMasterSeeder::class, // Missouri courses
            TexasMasterSeeder::class, // Texas Defensive Driving
            DelawareMasterSeeder::class, // Delaware 3 courses
            PrivacyPolicySeeder::class,
            StateSeeder::class,
            EmailTemplateSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
