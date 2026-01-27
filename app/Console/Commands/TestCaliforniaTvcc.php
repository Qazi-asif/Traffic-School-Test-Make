<?php

namespace App\Console\Commands;

use App\Models\CaliforniaCertificate;
use App\Models\UserCourseEnrollment;
use App\Services\CaliforniaTvccService;
use Illuminate\Console\Command;

class TestCaliforniaTvcc extends Command
{
    protected $signature = 'test:ca-tvcc {enrollment_id?}';
    protected $description = 'Test California TVCC integration';

    public function handle(CaliforniaTvccService $tvccService): int
    {
        $this->info('California TVCC Test');
        $this->info('===================');

        // Check configuration
        $this->info("\n1. Checking Configuration...");
        
        if (!$tvccService->isEnabled()) {
            $this->error('❌ California TVCC is not enabled');
            $this->info('Set CA_TVCC_ENABLED=true in .env');
            return 1;
        }
        $this->info('✅ TVCC is enabled');

        $configErrors = $tvccService->validateConfig();
        if (!empty($configErrors)) {
            $this->error('❌ Configuration errors:');
            foreach ($configErrors as $error) {
                $this->error('  - ' . $error);
            }
            return 1;
        }
        $this->info('✅ Configuration is valid');

        // Get or create test certificate
        $enrollmentId = $this->argument('enrollment_id');
        
        if ($enrollmentId) {
            $enrollment = UserCourseEnrollment::with(['user', 'course'])->find($enrollmentId);
            
            if (!$enrollment) {
                $this->error("❌ Enrollment {$enrollmentId} not found");
                return 1;
            }

            $this->info("\n2. Testing with Enrollment #{$enrollmentId}");
            $this->info("Student: {$enrollment->user->first_name} {$enrollment->user->last_name}");
            $this->info("Course: {$enrollment->course->title}");

            // Check if certificate exists
            $certificate = $enrollment->californiaCertificate;
            
            if (!$certificate) {
                $this->info("\n3. Creating California Certificate...");
                
                $certificate = CaliforniaCertificate::create([
                    'enrollment_id' => $enrollment->id,
                    'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                    'completion_date' => $enrollment->completed_at ?? now(),
                    'driver_license' => $enrollment->user->driver_license ?? 'D1234567',
                    'birth_date' => $enrollment->user->birth_date ?? now()->subYears(25),
                    'citation_number' => $enrollment->citation_number ?? 'TEST123456',
                    'court_code' => 'LAX001',
                    'status' => 'pending',
                ]);
                
                $this->info('✅ Certificate created');
            } else {
                $this->info("\n3. Using existing certificate #{$certificate->id}");
            }

            // Test submission
            $this->info("\n4. Testing TVCC Submission...");
            $this->info('This will make a real API call to California DMV');
            
            if (!$this->confirm('Do you want to proceed?', false)) {
                $this->info('Test cancelled');
                return 0;
            }

            try {
                $result = $tvccService->submitCertificate($certificate);

                if ($result['success']) {
                    $this->info('✅ Submission successful!');
                    $this->info('Response:');
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['CC Seq Number', $result['response']['ccSeqNbr'] ?? 'N/A'],
                            ['CC Status Code', $result['response']['ccStatCd'] ?? 'N/A'],
                            ['CC Timestamp', $result['response']['ccSubTstamp'] ?? 'N/A'],
                        ]
                    );
                } else {
                    $this->error('❌ Submission failed');
                    $this->error('Error: ' . ($result['error'] ?? 'Unknown error'));
                    $this->error('Code: ' . ($result['code'] ?? 'N/A'));
                }
            } catch (\Exception $e) {
                $this->error('❌ Exception: ' . $e->getMessage());
                return 1;
            }

        } else {
            $this->info("\n2. No enrollment ID provided");
            $this->info('Usage: php artisan test:ca-tvcc {enrollment_id}');
            
            // Show recent California enrollments
            $recentEnrollments = UserCourseEnrollment::with(['user', 'course'])
                ->whereHas('course', function($q) {
                    // Assuming California courses have 'CA' in state or specific table
                    $q->where('state', 'CA');
                })
                ->latest()
                ->limit(5)
                ->get();

            if ($recentEnrollments->count() > 0) {
                $this->info("\nRecent California Enrollments:");
                $this->table(
                    ['ID', 'Student', 'Course', 'Status'],
                    $recentEnrollments->map(function($e) {
                        return [
                            $e->id,
                            $e->user->first_name . ' ' . $e->user->last_name,
                            $e->course->title,
                            $e->status
                        ];
                    })
                );
            } else {
                $this->info('No California enrollments found');
            }
        }

        return 0;
    }
}
