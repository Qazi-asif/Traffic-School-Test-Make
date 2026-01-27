<?php

namespace App\Console\Commands;

use App\Services\FlhsmvHttpService;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Log;

class TestFloridaHttpReal extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'florida:test-http-real {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Test Florida FLHSMV API with real HTTP SOAP call (works without SOAP extension)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏖️  Testing Florida FLHSMV API with Real HTTP SOAP Call');
        $this->info('HTTP-based SOAP (No SOAP Extension Required)');
        $this->info('Based on: Driver School Web Service User Guide V1.3');
        $this->newLine();

        // Check SOAP extension availability
        $soapAvailable = extension_loaded('soap');
        $this->info('SOAP Extension Available: ' . ($soapAvailable ? 'Yes' : 'No'));
        $this->info('Using HTTP SOAP Service: Yes (cPanel compatible)');
        $this->newLine();

        // Get configuration
        $endpoint = config('services.florida.wsdl_url');
        $username = config('services.florida.username');
        $password = config('services.florida.password');
        $schoolId = config('services.florida.school_id');
        $instructorId = config('services.florida.instructor_id');

        // Convert WSDL URL to service endpoint
        $endpoint = str_replace('?wsdl', '', $endpoint);

        $this->info("Configuration:");
        $this->line("  Endpoint: $endpoint");
        $this->line("  Username: $username");
        $this->line("  Password: " . str_repeat('*', strlen($password)));
        $this->line("  School ID: $schoolId");
        $this->line("  Instructor ID: $instructorId");
        $this->newLine();

        // Test endpoint accessibility first
        if (!$this->testEndpointAccess($endpoint)) {
            $this->error('❌ Endpoint is not accessible. Make sure VPN is connected to US IP.');
            return 1;
        }

        // Create test data based on Florida API documentation
        $testData = $this->createTestData();
        
        if ($this->option('dry-run')) {
            $this->info('🧪 DRY RUN - Showing what would be sent:');
            $this->displayTestData($testData);
            return 0;
        }

        // Perform actual HTTP SOAP call
        return $this->performHttpSoapCall($testData);
    }

    /**
     * Test endpoint accessibility.
     */
    protected function testEndpointAccess(string $endpoint): bool
    {
        $this->info('🔍 Testing endpoint accessibility...');
        
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'user_agent' => 'Laravel/HTTP SOAP Client',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $headers = @get_headers($endpoint, 1, $context);
            
            if (!$headers) {
                $this->error('   ❌ Cannot retrieve headers from endpoint URL');
                return false;
            }

            $statusCode = null;
            if (is_array($headers) && isset($headers[0])) {
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
                $statusCode = $matches[1] ?? null;
            }

            if ($statusCode && in_array($statusCode, [200, 405, 500])) {
                $this->info("   ✅ Endpoint accessible (HTTP $statusCode)");
                return true;
            } else {
                $this->error("   ❌ Endpoint not accessible (HTTP $statusCode)");
                return false;
            }

        } catch (Exception $e) {
            $this->error('   ❌ Endpoint accessibility test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create test data based on Florida API documentation.
     */
    protected function createTestData(): array
    {
        // Based on the example in the documentation:
        // UID|PW|4743|04172013|1800|5337|6443|Joe|Sam|Hunt||11231955|F|0967|04172013|Putnam|4257WPP|B1|H123456781020|FL|||Po box 674||Tallahassee|FL|32399||8501234567|
        
        return [
            // Authentication (required)
            'mvUserid' => config('services.florida.username'),
            'mvPassword' => config('services.florida.password'),
            'mvSchoolid' => config('services.florida.school_id'),
            'mvSchoolIns' => config('services.florida.instructor_id'),
            'mvSchoolCourse' => '40585', // From your .env
            
            // Class Information (required)
            'mvClassDate' => date('mdY'), // Today's date in MMDDYYYY format
            'mvStartTime' => '0001', // Technology-based delivery (internet)
            
            // Student Information (required)
            'mvFirstName' => 'John',
            'mvMiddleName' => 'Test',
            'mvLastName' => 'Doe',
            'mvSuffix' => '', // Optional
            'mvDob' => '01011990', // MMDDYYYY format
            'mvSex' => 'M', // M or F
            
            // Driver License (required for BDI/ADI)
            'mvDriversLicense' => 'D123456789012', // Florida format: A999999999999
            'mvdlStateOfRecordCode' => 'FL',
            
            // SSN (optional for BDI/ADI, required for TLSAE/DETS if no FL DL)
            'mvSocialSN' => '1234', // Last 4 digits
            
            // Citation Information (required for BDI)
            'mvCitationDate' => date('mdY'), // MMDDYYYY format
            'mvCitationCounty' => 'LEON', // From code table
            'mvCitationNumber' => '1234567', // 7 characters exactly
            
            // Reason for Attending (required)
            'mvReasonAttending' => 'B1', // BDI Election
            
            // Address (optional)
            'mvStreet' => '123 Test Street',
            'mvApartment' => '',
            'mvCity' => 'Tallahassee',
            'mvState' => 'FL',
            'mvZipCode' => '32301',
            'mvZipPlus' => '',
            'mvPhone' => '8501234567',
            'mvEmail' => 'test@example.com',
            
            // Additional fields (optional)
            'mvAlienNumber' => '',
            'mvNonAlien' => '',
        ];
    }

    /**
     * Display test data.
     */
    protected function displayTestData(array $data): void
    {
        $this->table(
            ['Parameter', 'Value', 'Description'],
            [
                ['mvUserid', $data['mvUserid'], 'FLHSMV Username'],
                ['mvPassword', str_repeat('*', strlen($data['mvPassword'])), 'FLHSMV Password'],
                ['mvSchoolid', $data['mvSchoolid'], 'School ID'],
                ['mvSchoolIns', $data['mvSchoolIns'], 'Instructor ID'],
                ['mvSchoolCourse', $data['mvSchoolCourse'], 'Course ID'],
                ['mvClassDate', $data['mvClassDate'], 'Class Date (MMDDYYYY)'],
                ['mvStartTime', $data['mvStartTime'], 'Start Time (HHMM)'],
                ['mvFirstName', $data['mvFirstName'], 'Student First Name'],
                ['mvLastName', $data['mvLastName'], 'Student Last Name'],
                ['mvDob', $data['mvDob'], 'Date of Birth (MMDDYYYY)'],
                ['mvSex', $data['mvSex'], 'Gender (M/F)'],
                ['mvDriversLicense', $data['mvDriversLicense'], 'Driver License Number'],
                ['mvdlStateOfRecordCode', $data['mvdlStateOfRecordCode'], 'State of Record'],
                ['mvCitationNumber', $data['mvCitationNumber'], 'Citation Number (7 chars)'],
                ['mvCitationCounty', $data['mvCitationCounty'], 'Citation County'],
                ['mvReasonAttending', $data['mvReasonAttending'], 'Reason Code (B1=BDI Election)'],
            ]
        );
        
        $this->newLine();
        $this->info('📋 HTTP SOAP Request Details:');
        $this->line('  Method: POST');
        $this->line('  Content-Type: text/xml; charset=utf-8');
        $this->line('  SOAPAction: http://tempuri.org/wsVerifyData');
        $this->line('  Body: SOAP XML envelope with above parameters');
    }

    /**
     * Perform the actual HTTP SOAP call.
     */
    protected function performHttpSoapCall(array $data): int
    {
        $this->info('🚀 Creating HTTP SOAP service...');
        
        try {
            $httpService = new FlhsmvHttpService();
            $this->info('✅ HTTP SOAP service created successfully');
            
            // Prepare payload for HTTP SOAP call
            $payload = [
                'user' => (object) [
                    'first_name' => $data['mvFirstName'],
                    'middle_name' => $data['mvMiddleName'],
                    'last_name' => $data['mvLastName'],
                    'date_of_birth' => \Carbon\Carbon::createFromFormat('mdY', $data['mvDob']),
                    'gender' => $data['mvSex'],
                    'driver_license' => $data['mvDriversLicense'],
                    'ssn_last_four' => $data['mvSocialSN'],
                    'address' => $data['mvStreet'],
                    'city' => $data['mvCity'],
                    'state' => $data['mvState'],
                    'zip_code' => $data['mvZipCode'],
                    'phone' => $data['mvPhone'],
                    'email' => $data['mvEmail'],
                ],
                'enrollment' => (object) [
                    'citation_number' => $data['mvCitationNumber'],
                    'court_county' => $data['mvCitationCounty'],
                    'completed_at' => \Carbon\Carbon::createFromFormat('mdY', $data['mvClassDate']),
                ],
                'first_name' => $data['mvFirstName'],
                'last_name' => $data['mvLastName'],
                'citation_number' => $data['mvCitationNumber'],
                'driver_license_number' => $data['mvDriversLicense'],
            ];

            $this->info('📤 Sending HTTP SOAP request...');
            
            // Log the request (without password)
            $logData = $data;
            $logData['mvPassword'] = '[REDACTED]';
            Log::info('Florida HTTP SOAP test request', $logData);

            $this->warn('⚠️  About to make REAL API call to Florida FLHSMV');
            $this->warn('   This will submit test data to the production system via HTTP SOAP.');
            $this->warn('   No SOAP extension required - works on cPanel hosting.');
            
            if (!$this->confirm('Do you want to proceed with the REAL HTTP SOAP API call?')) {
                $this->info('❌ API call cancelled by user');
                return 0;
            }

            // Make the HTTP SOAP call
            $response = $httpService->submitCertificate($payload);

            $this->info('✅ HTTP SOAP call completed!');
            $this->newLine();
            
            $this->info('📥 Response from Florida FLHSMV:');
            $this->displayResponse($response);

            // Log the response
            Log::info('Florida HTTP SOAP test response', ['response' => $response]);

            return $response['success'] ? 0 : 1;

        } catch (Exception $e) {
            $this->error('❌ HTTP SOAP Exception occurred:');
            $this->error("   Message: {$e->getMessage()}");

            // Log the error
            Log::error('Florida HTTP SOAP test exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Display the response in a formatted way.
     */
    protected function displayResponse(array $response): void
    {
        if ($response['success']) {
            $this->info('✅ SUCCESS');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Status', 'Success'],
                    ['Certificate Number', $response['certificate_number'] ?? 'N/A'],
                    ['Response Code', $response['response_code'] ?? 'N/A'],
                    ['Message', $response['message'] ?? 'N/A'],
                    ['HTTP Status', $response['status'] ?? 'N/A'],
                ]
            );
        } else {
            $this->error('❌ FAILURE');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Status', 'Failed'],
                    ['Error', $response['error'] ?? 'Unknown error'],
                    ['Error Code', $response['code'] ?? 'N/A'],
                    ['HTTP Status', $response['status'] ?? 'N/A'],
                    ['Retryable', isset($response['retryable']) ? ($response['retryable'] ? 'Yes' : 'No') : 'N/A'],
                ]
            );
        }

        $this->newLine();
        $this->info('📋 Full Response:');
        $this->line(json_encode($response, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info('💡 HTTP SOAP Benefits:');
        $this->line('  ✅ Works without SOAP extension');
        $this->line('  ✅ Compatible with cPanel hosting');
        $this->line('  ✅ Same functionality as native SOAP');
        $this->line('  ✅ Handles SSL certificate issues');
        $this->line('  ✅ Automatic retry and error handling');
    }
}