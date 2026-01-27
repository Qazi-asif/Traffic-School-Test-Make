<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestCertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some enrollments to create certificates for
        $enrollments = \App\Models\UserCourseEnrollment::with(['user', 'course'])->take(5)->get();

        if ($enrollments->isEmpty()) {
            $this->command->warn('No enrollments found. Please create enrollments first.');

            return;
        }

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->user || ! $enrollment->course) {
                continue;
            }

            \App\Models\FloridaCertificate::create([
                'enrollment_id' => $enrollment->id,
                'dicds_certificate_number' => 'FL-'.date('Y').'-'.str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
                'student_name' => $enrollment->user->first_name.' '.$enrollment->user->last_name,
                'completion_date' => now()->subDays(rand(1, 30)),
                'course_name' => $enrollment->course->title ?? 'Traffic School Course',
                'final_exam_score' => rand(80, 100),
                'driver_license_number' => 'D'.rand(1000000, 9999999),
                'citation_number' => 'C'.rand(100000, 999999),
                'citation_county' => collect(['Miami-Dade', 'Broward', 'Palm Beach', 'Orange', 'Hillsborough'])->random(),
                'traffic_school_due_date' => now()->addDays(rand(30, 90)),
                'student_address' => $enrollment->user->mailing_address ?? '123 Main St, Miami, FL 33101',
                'student_date_of_birth' => now()->subYears(rand(18, 65)),
                'court_name' => collect(['Miami-Dade County Court', 'Broward County Court', 'Orange County Court'])->random(),
                'state' => 'FL',
                'verification_hash' => \Illuminate\Support\Str::random(32),
                'is_sent_to_student' => rand(0, 1),
                'sent_at' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                'generated_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Created '.$enrollments->count().' test certificates successfully!');
    }
}
