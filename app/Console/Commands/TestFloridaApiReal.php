<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoapClient;
use SoapFault;
use Exception;
use Illuminate\Support\Facades\Log;

class TestFloridaApiReal extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'florida:test-real {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Test Florida FLHSMV API with real SOAP call using official documentation format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏖️  Testing Florida FLHSMV API with Real SOAP Call');
        $this->info('Based on: Driver School Web Service User Guide V1.3');
        $this->newLine();

        // Get configuration
        $wsdlUrl = config('services.florida.wsdl_url');
        $username = config('services.florida.username');
        $password = config('services.florida.password');
        $schoolId = config('services.florida.school_id');
        $instructorId = config('services.florida.instructor_id');

        $this->info("Configuration:");
        $this->line("  WSDL URL: $wsdlUrl");
        $this->line("  Username: $username");
        $this->line("  Password: " . str_repeat('*', strlen($password)));
        $this->line("  School ID: $schoolId");
        $this->line("  Instructor ID: $instructorId");
        $this->newLine();

        // Test WSDL accessibility first
        if (!$this->testWsdlAccess($wsdlUrl)) {
            $this->error('❌ WSDL is not accessible. Make sure VPN is connected to US IP.');
            return 1;
        }

        // Create test data based on Florida API documentation
        $testData = $this->createTestData();
        
        if ($this->option('dry-run')) {
            $this->info('🧪 DRY RUN - Showing what would be sent:');
            $this->displayTestData($testData);
            return 0;
        }

        // Perform actual SOAP call
        return $this->performSoapCall($wsdlUrl, $testData);
    }

    /**
     * Test WSDL accessibility.
     */
    protected function testWsdlAccess(string $wsdlUrl): bool
    {
        $this->info('🔍 Testing WSDL accessibility...');
        
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'user_agent' => 'Laravel/SOAP Client',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $headers = @get_headers($wsdlUrl, 1, $context);
            
            if (!$headers) {
                $this->error('   ❌ Cannot retrieve headers from WSDL URL');
                return false;
            }

            $statusCode = null;
            if (is_array($headers) && isset($headers[0])) {
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
                $statusCode = $matches[1] ?? null;
            }

            if ($statusCode && $statusCode == 200) {
                $this->info('   ✅ WSDL accessible (HTTP 200)');
                return true;
            } else {
                $this->error("   ❌ WSDL not accessible (HTTP $statusCode)");
                return false;
            }

        } catch (Exception $e) {
            $this->error('   ❌ WSDL accessibility test failed: ' . $e->getMessage());
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
    }

    /**
     * Perform the actual SOAP call.
     */
    protected function performSoapCall(string $wsdlUrl, array $data): int
    {
        $this->info('🚀 Creating SOAP client...');
        
        try {
            $soapClient = new SoapClient($wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'encoding' => 'UTF-8',
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            $this->info('✅ SOAP client created successfully');
            
            // Get available methods
            $methods = $soapClient->__getFunctions();
            $this->info('📋 Available SOAP methods:');
            foreach ($methods as $method) {
                $this->line("   • $method");
            }
            $this->newLine();

            // Prepare SOAP call
            $this->info('📤 Sending SOAP request...');
            
            // Log the request (without password)
            $logData = $data;
            $logData['mvPassword'] = '[REDACTED]';
            Log::info('Florida SOAP test request', $logData);

            // Make the SOAP call
            // Note: The exact method name needs to be determined from the WSDL
            // Common method names: SubmitCertificate, ProcessStudent, etc.
            
            $this->warn('⚠️  About to make REAL API call to Florida FLHSMV');
            $this->warn('   This will submit test data to the production system.');
            
            if (!$this->confirm('Do you want to proceed with the REAL API call?')) {
                $this->info('❌ API call cancelled by user');
                return 0;
            }

            // Call the correct SOAP method as found in WSDL inspection
            // Method: wsVerifyData with wsVerifyData parameters
            $soapParams = new \stdClass();
            foreach ($data as $key => $value) {
                $soapParams->$key = $value;
            }
            
            $response = $soapClient->wsVerifyData($soapParams);

            $this->info('✅ SOAP call completed successfully!');
            $this->newLine();
            
            $this->info('📥 Response from Florida FLHSMV:');
            if (is_object($response) || is_array($response)) {
                $this->line(json_encode($response, JSON_PRETTY_PRINT));
            } else {
                $this->line($response);
            }

            // Log the response
            Log::info('Florida SOAP test response', ['response' => $response]);

            return 0;

        } catch (SoapFault $e) {
            $this->error('❌ SOAP Fault occurred:');
            $this->error("   Code: {$e->getCode()}");
            $this->error("   Message: {$e->getMessage()}");
            
            if (isset($e->faultcode)) {
                $this->error("   Fault Code: {$e->faultcode}");
            }
            
            if (isset($e->faultstring)) {
                $this->error("   Fault String: {$e->faultstring}");
            }

            // Log the error
            Log::error('Florida SOAP test fault', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'faultcode' => $e->faultcode ?? null,
                'faultstring' => $e->faultstring ?? null,
            ]);

            return 1;

        } catch (Exception $e) {
            $this->error('❌ Exception occurred:');
            $this->error("   Message: {$e->getMessage()}");

            // Log the error
            Log::error('Florida SOAP test exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}