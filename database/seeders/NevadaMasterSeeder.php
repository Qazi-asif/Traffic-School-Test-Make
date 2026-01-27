<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\NevadaCourse;
use Illuminate\Database\Seeder;

class NevadaMasterSeeder extends Seeder
{
    public function run(): void
    {
        // Create Nevada Traffic Safety Course
        $course = Course::create([
            'title' => 'Nevada Traffic Safety Course',
            'description' => 'State-approved traffic safety course for Nevada residents',
            'state' => 'Nevada',
            'price' => 29.99,
            'duration_hours' => 8,
            'passing_score' => 80,
            'is_active' => true,
        ]);

        NevadaCourse::create([
            'course_id' => $course->id,
            'nevada_course_code' => 'NV-TS-001',
            'course_type' => 'traffic_safety',
            'approved_date' => now()->subYear(),
            'expiration_date' => now()->addYears(2),
            'approval_number' => 'NV-APPROVAL-2024-001',
            'required_hours' => 8.0,
            'max_completion_days' => 90,
            'is_active' => true,
        ]);

        // Create Nevada Defensive Driving Course
        $course2 = Course::create([
            'title' => 'Nevada Defensive Driving Course',
            'description' => 'State-approved defensive driving course for Nevada',
            'state' => 'Nevada',
            'price' => 34.99,
            'duration_hours' => 6,
            'passing_score' => 80,
            'is_active' => true,
        ]);

        NevadaCourse::create([
            'course_id' => $course2->id,
            'nevada_course_code' => 'NV-DD-001',
            'course_type' => 'defensive_driving',
            'approved_date' => now()->subYear(),
            'expiration_date' => now()->addYears(2),
            'approval_number' => 'NV-APPROVAL-2024-002',
            'required_hours' => 6.0,
            'max_completion_days' => 60,
            'is_active' => true,
        ]);

        $this->command->info('Nevada courses created successfully!');
    }
}
