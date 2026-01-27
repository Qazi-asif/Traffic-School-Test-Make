<?php

namespace App\Console\Commands;

use App\Services\FlhsmvSoapService;
use App\Services\FlhsmvHttpService;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Log;

class CompareFloridaServices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'florida:compare-services {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Compare SOAP vs HTTP SOAP services for Florida FLHSMV API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Comparing Florida FLHSMV Services');
        $this->info('SOAP Extension vs HTTP SOAP (cPanel Compatible)');
        $this->newLine();

        // Check SOAP extension availability
        $soapAvailable = extension_loaded('soap');
        $this->info('SOAP Extension Available: ' . ($soapAvailable ? 'Yes ✅' : 'No ❌'));
        $this->info('HTTP SOAP Available: Yes ✅ (Always works)');
        $this->newLine();

        // Create test payload
        $payload = $this->createTestPayload();
        
        if ($this->option('dry-run')) {
            $this->info('🧪 DRY RUN - Showing comparison without API calls');
            $this->displayComparison();
            return 0;
        }

        $this->warn('⚠️  About to make REAL API calls to Florida FLHSMV');
        $this->warn('   This will test both SOAP and HTTP SOAP services.');
        
        if (!$this->confirm('Do you want to proceed with the comparison test?')) {
            $this->info('❌ Comparison cancelled by user');
            return 0;
        }

        // Test both services
        $results = [];
        
        // Test HTTP SOAP first (always available)
        $this->info('🌐 Testing HTTP SOAP Service...');
        $results['http_soap'] = $this->testHttpSoapService($payload);
        
        // Test native SOAP if available
        if ($soapAvailable) {
            $this->info('🔧 Testing Native SOAP Service...');
            $results['native_soap'] = $this->testNativeSoapService($payload);
        } else {
            $results['native_soap'] = [
                'success' => false,
                'error' => 'SOAP extension not available',
                'code' => 'NO_SOAP_EXTENSION',
                'status' => 'N/A',
            ];
        }

        // Display comparison results
        $this->displayResults($results);

        return 0;
    }

    /**
     * Create test payload.
     */
    protected function createTestPayload(): array
    {
        return [
            'user' => (object) [
                'first_name' => 'John',
                'middle_name' => 'Test',
                'last_name' => 'Doe',
                'date_of_birth' => \Carbon\Carbon::createFromFormat('mdY', '01011990'),
                'gender' => 'M',
                'driver_license' => 'D123456789012',
                'ssn_last_four' => '1234',
                'address' => '123 Test Street',
                'city' => 'Tallahassee',
                'state' => 'FL',
                'zip_code' => '32301',
                'phone' => '8501234567',
                'email' => 'test@example.com',
            ],
            'enrollment' => (object) [
                'citation_number' => '1234567',
                'court_county' => 'LEON',
                'completed_at' => now(),
            ],
            'first_name' => 'John',
            'last_name' => 'Doe',
            'citation_number' => '1234567',
            'driver_license_number' => 'D123456789012',
        ];
    }

    /**
     * Test HTTP SOAP service.
     */
    protected function testHttpSoapService(array $payload): array
    {
        try {
            $startTime = microtime(true);
            $service = new FlhsmvHttpService();
            $response = $service->submitCertificate($payload);
            $endTime = microtime(true);
            
            $response['response_time'] = round(($endTime - $startTime) * 1000, 2); // milliseconds
            $response['service_type'] = 'HTTP SOAP';
            
            return $response;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'HTTP_SOAP_EXCEPTION',
                'status' => 500,
                'service_type' => 'HTTP SOAP',
                'response_time' => 0,
            ];
        }
    }

    /**
     * Test native SOAP service.
     */
    protected function testNativeSoapService(array $payload): array
    {
        try {
            $startTime = microtime(true);
            $service = new FlhsmvSoapService();
            $response = $service->submitCertificate($payload);
            $endTime = microtime(true);
            
            $response['response_time'] = round(($endTime - $startTime) * 1000, 2); // milliseconds
            $response['service_type'] = 'Native SOAP';
            
            return $response;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'NATIVE_SOAP_EXCEPTION',
                'status' => 500,
                'service_type' => 'Native SOAP',
                'response_time' => 0,
            ];
        }
    }

    /**
     * Display comparison without API calls.
     */
    protected function displayComparison(): void
    {
        $this->table(
            ['Feature', 'Native SOAP', 'HTTP SOAP'],
            [
                ['Requires SOAP Extension', '✅ Yes', '❌ No'],
                ['Works on cPanel', '❌ Usually No', '✅ Yes'],
                ['Works on Shared Hosting', '❌ Usually No', '✅ Yes'],
                ['SSL Certificate Issues', '⚠️  Common', '✅ Handled'],
                ['Performance', '✅ Faster', '⚠️  Slightly Slower'],
                ['Error Handling', '✅ Good', '✅ Enhanced'],
                ['Retry Logic', '❌ Manual', '✅ Built-in'],
                ['Deployment Complexity', '⚠️  High', '✅ Low'],
                ['Maintenance', '⚠️  Extension Dependent', '✅ Self-contained'],
            ]
        );

        $this->newLine();
        $this->info('💡 Recommendations:');
        $this->line('  🎯 Use HTTP SOAP for production deployment on shared hosting');
        $this->line('  🔧 Use Native SOAP for development if extension is available');
        $this->line('  🚀 HTTP SOAP provides better compatibility and reliability');
        $this->line('  📦 No server configuration changes needed for HTTP SOAP');
    }

    /**
     * Display test results.
     */
    protected function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 Service Comparison Results');
        $this->newLine();

        // HTTP SOAP Results
        $this->info('🌐 HTTP SOAP Service Results:');
        $this->displayServiceResult($results['http_soap']);
        
        $this->newLine();
        
        // Native SOAP Results
        $this->info('🔧 Native SOAP Service Results:');
        $this->displayServiceResult($results['native_soap']);

        $this->newLine();
        
        // Performance Comparison
        if (isset($results['http_soap']['response_time']) && isset($results['native_soap']['response_time'])) {
            $this->info('⚡ Performance Comparison:');
            $this->table(
                ['Service', 'Response Time (ms)', 'Status'],
                [
                    [
                        'HTTP SOAP',
                        $results['http_soap']['response_time'] . ' ms',
                        $results['http_soap']['success'] ? '✅ Success' : '❌ Failed'
                    ],
                    [
                        'Native SOAP',
                        $results['native_soap']['response_time'] . ' ms',
                        $results['native_soap']['success'] ? '✅ Success' : '❌ Failed'
                    ],
                ]
            );
        }

        $this->newLine();
        $this->info('🎯 Recommendation for Production:');
        
        if ($results['http_soap']['success'] && !$results['native_soap']['success']) {
            $this->info('✅ Use HTTP SOAP - Native SOAP failed, HTTP SOAP succeeded');
        } elseif (!$results['http_soap']['success'] && $results['native_soap']['success']) {
            $this->warn('⚠️  Use Native SOAP - HTTP SOAP failed, but check hosting compatibility');
        } elseif ($results['http_soap']['success'] && $results['native_soap']['success']) {
            $this->info('✅ Both services work - Use HTTP SOAP for better hosting compatibility');
        } else {
            $this->error('❌ Both services failed - Check credentials and network connectivity');
        }

        $this->newLine();
        $this->info('📋 Deployment Notes:');
        $this->line('  • HTTP SOAP works on any hosting environment');
        $this->line('  • Native SOAP requires PHP SOAP extension');
        $this->line('  • Your system automatically falls back from HTTP to SOAP');
        $this->line('  • Both services use identical error handling and retry logic');
    }

    /**
     * Display individual service result.
     */
    protected function displayServiceResult(array $result): void
    {
        if ($result['success']) {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Status', '✅ Success'],
                    ['Service Type', $result['service_type'] ?? 'Unknown'],
                    ['Certificate Number', $result['certificate_number'] ?? 'N/A'],
                    ['Response Code', $result['response_code'] ?? 'N/A'],
                    ['Message', $result['message'] ?? 'N/A'],
                    ['Response Time', ($result['response_time'] ?? 0) . ' ms'],
                ]
            );
        } else {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Status', '❌ Failed'],
                    ['Service Type', $result['service_type'] ?? 'Unknown'],
                    ['Error', $result['error'] ?? 'Unknown error'],
                    ['Error Code', $result['code'] ?? 'N/A'],
                    ['HTTP Status', $result['status'] ?? 'N/A'],
                    ['Response Time', ($result['response_time'] ?? 0) . ' ms'],
                ]
            );
        }
    }
}