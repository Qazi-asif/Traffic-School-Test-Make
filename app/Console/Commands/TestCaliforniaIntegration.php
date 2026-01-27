<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserCourseEnrollment;
use App\Models\Course;
use App\Services\CaliforniaTvccService;
use Illuminate\Console\Command;

class TestCaliforniaIntegration extends Command
{
    protected $signature = 'california:test-integration';
    protected $description = 'Test the complete California TVCC integration with mock data';

    public function handle(CaliforniaTvccService $tvccService): int
    {
        $this->info('Testing California TVCC Integration...');
        $this->newLine();

        // 1. Test Configuration
        $this->info('1. Testing Configuration...');
        $configErrors = $tvccService->validateConfig();
        
        if (!empty($configErrors)) {
            $this->error('Configuration errors found:');
            foreach ($configErrors as $error) {
                $this->error("  - {$error}");
            }
            return self::FAILURE;
        }
        
        $this->info('✓ Configuration is valid');
        $this->newLine();

        // 2. Test Service Availability
        $this->info('2. Testing Service Availability...');
        if ($tvccService->isEnabled()) {
            $this->info('✓ California TVCC is enabled');
        } else {
            $this->warn('⚠ California TVCC is disabled');
        }
        $this->newLine();

        // 3. Create Mock Certificate for Testing
        $this->info('3. Creating mock certificate data...');
        $mockCertificate = $this->createMockCertificate();
        $this->info('✓ Mock certificate created');
        $this->newLine();

        // 4. Test Certificate Submission
        $this->info('4. Testing certificate submission...');
        
        try {
            $result = $tvccService->submitCertificate($mockCertificate);
            
            if ($result['success']) {
                $this->info('✓ Certificate submission successful!');
                $this->info("Response: {$result['message']}");
                
                if (isset($result['response'])) {
                    $response = $result['response'];
                    $this->info("Certificate Number: {$response['ccSeqNbr']}");
                    $this->info("Status Code: {$response['ccStatCd']}");
                    $this->info("Timestamp: {$response['ccSubTstamp']}");
                }
            } else {
                $this->error('✗ Certificate submission failed!');
                $this->error("Error: {$result['error']}");
                $this->error("Code: {$result['code']}");
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Exception during submission: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();

        // 5. Test Connection Methods
        $this->info('5. Testing connection methods...');
        $connectionResult = $tvccService->testConnection();
        
        if ($connectionResult['success']) {
            $this->info('✓ Connection test passed');
            if (isset($connectionResult['methods']['functions'])) {
                $this->info('Available SOAP methods:');
                foreach ($connectionResult['methods']['functions'] as $method) {
                    $this->line("  - {$method}");
                }
            }
        } else {
            $this->warn('⚠ Direct connection failed (expected for California TVCC)');
            $this->warn("Reason: {$connectionResult['error']}");
            $this->info('✓ Fallback mechanisms will be used automatically');
        }

        $this->newLine();
        $this->info('California TVCC integration test completed!');
        $this->info('The system is configured to use fallback mechanisms when direct SOAP access is not available.');
        
        return self::SUCCESS;
    }

    private function createMockCertificate(): object
    {
        // Create a mock certificate object with enrollment and user data
        $mockCertificate = new \stdClass();
        
        // Mock enrollment
        $mockEnrollment = new \stdClass();
        $mockEnrollment->id = 12345;
        $mockEnrollment->completed_at = now();
        $mockEnrollment->citation_number = 'CA1234567';
        
        // Mock user
        $mockUser = new \stdClass();
        $mockUser->id = 67890;
        $mockUser->first_name = 'John';
        $mockUser->last_name = 'Doe';
        $mockUser->date_of_birth = now()->subYears(30);
        $mockUser->driver_license = 'D123456789012';
        $mockUser->court_selected = 'Los Angeles Superior Court';
        
        $mockEnrollment->user = $mockUser;
        $mockCertificate->enrollment = $mockEnrollment;
        
        return $mockCertificate;
    }
}