<?php

namespace App\Console\Commands;

use App\Models\UserCourseEnrollment;
use App\Models\User;
use App\Jobs\SendFloridaTransmissionJob;
use App\Services\FlhsmvSoapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestFloridaIntegration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'florida:test-integration {--enrollment-id= : Test with specific enrollment ID} {--create-test : Create test enrollment}';

    /**
     * The console command description.
     */
    protected $description = 'Test complete Florida integration with real enrollment data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏖️  Testing Complete Florida Integration');
        $this->newLine();

        // Test API connection first
        $this->testApiConnection();

        if ($this->option('create-test')) {
            $enrollment = $this->createTestEnrollment();
        } elseif ($this->option('enrollment-id')) {
            $enrollment = UserCourseEnrollment::with(['user', 'course'])->find($this->option('enrollment-id'));
            if (!$enrollment) {
                $this->error('Enrollment not found');
                return 1;
            }
        } else {
            // Find a completed enrollment
            $enrollment = $this->findTestEnrollment();
        }

        if (!$enrollment) {
            $this->error('No suitable enrollment found for testing');
            return 1;
        }

        $this->info("Using enrollment ID: {$enrollment->id}");
        $this->info("Student: {$enrollment->user->first_name} {$enrollment->user->last_name}");
        $this->info("Email: {$enrollment->user->email}");
        $this->newLine();

        // Test the transmission job
        return $this->testTransmissionJob($enrollment);
    }

    /**
     * Test API connection.
     */
    protected function testApiConnection(): void
    {
        $this->info('🔍 Testing API Connection...');
        
        try {
            $service = new FlhsmvSoapService();
            $result = $service->testConnection();

            if ($result['success']) {
                $this->info('✅ API Connection: SUCCESS');
                $this->line('   Available methods: ' . count($result['methods'] ?? []));
            } else {
                $this->error('❌ API Connection: FAILED');
                $this->error('   Error: ' . $result['error']);
                if (isset($result['suggestion'])) {
                    $this->warn('   Suggestion: ' . $result['suggestion']);
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ API Connection: EXCEPTION');
            $this->error('   Error: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Find a suitable test enrollment.
     */
    protected function findTestEnrollment(): ?UserCourseEnrollment
    {
        $this->info('🔍 Finding test enrollment...');

        // Look for completed enrollments (any state for testing)
        $enrollment = UserCourseEnrollment::with(['user', 'course'])
            ->whereNotNull('completed_at')
            ->whereHas('user', function ($q) {
                $q->whereNotNull('first_name')
                  ->whereNotNull('last_name')
                  ->whereNotNull('email');
            })
            ->orderBy('completed_at', 'desc')
            ->first();

        if ($enrollment) {
            $this->info('✅ Found completed enrollment for testing');
        } else {
            $this->warn('⚠️  No completed enrollments found');
        }

        return $enrollment;
    }

    /**
     * Create a test enrollment for testing.
     */
    protected function createTestEnrollment(): UserCourseEnrollment
    {
        $this->info('🧪 Creating test enrollment...');

        // This would create a test user and enrollment
        // Implementation depends on your specific models and requirements
        $this->warn('⚠️  Test enrollment creation not implemented');
        $this->warn('   Use --enrollment-id option with existing enrollment');
        
        return $this->findTestEnrollment();
    }

    /**
     * Test the transmission job.
     */
    protected function testTransmissionJob(UserCourseEnrollment $enrollment): int
    {
        $this->info('🚀 Testing Transmission Job...');

        try {
            // Create transmission record
            $transmission = \App\Models\StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'FL',
                'system' => 'FLHSMV',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            $this->info("✅ Created transmission record: {$transmission->id}");

            // Execute the job synchronously
            $job = new SendFloridaTransmissionJob($transmission->id);
            
            $this->info('📤 Executing transmission job...');
            $job->handle();

            // Check the result
            $transmission->refresh();
            
            $this->newLine();
            $this->info('📊 Transmission Results:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Status', $transmission->status],
                    ['Response Code', $transmission->response_code ?? 'N/A'],
                    ['Response Message', $transmission->response_message ?? 'N/A'],
                    ['Retry Count', $transmission->retry_count],
                    ['Sent At', $transmission->sent_at ? $transmission->sent_at->format('Y-m-d H:i:s') : 'N/A'],
                ]
            );

            if ($transmission->status === 'success') {
                $this->info('🎉 Transmission completed successfully!');
                return 0;
            } elseif ($transmission->status === 'error') {
                $this->error('❌ Transmission failed');
                
                // Show error code explanation
                if ($transmission->response_code) {
                    $service = new FlhsmvSoapService();
                    $errorInfo = $service->mapFloridaErrorCode($transmission->response_code);
                    
                    $this->newLine();
                    $this->error('Error Details:');
                    $this->line("   Code: {$transmission->response_code}");
                    $this->line("   Message: {$errorInfo['message']}");
                    $this->line("   Retryable: " . ($errorInfo['retryable'] ? 'Yes' : 'No'));
                }
                
                return 1;
            } else {
                $this->warn('⚠️  Transmission status unclear');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Transmission job failed with exception:');
            $this->error("   {$e->getMessage()}");
            
            Log::error('Florida integration test failed', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }
}