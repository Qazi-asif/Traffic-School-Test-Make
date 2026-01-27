<?php

namespace App\Console\Commands;

use App\Services\FlhsmvSoapService;
use App\Services\CaliforniaTvccService;
use App\Services\NevadaNtsaService;
use App\Services\CcsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use SoapClient;
use SoapFault;

class TestAllStateApis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'states:test-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all state API connections (Florida, California, Nevada, CCS)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing All State API Connections...');
        $this->info('Environment: ' . app()->environment());
        $this->info('Project Path: ' . base_path());
        $this->newLine();

        // First, run basic connectivity tests
        $this->runConnectivityDiagnostics();
        $this->newLine();

        $results = [];

        // Test each state with detailed error handling
        $results['Florida'] = $this->testFloridaDetailed();
        $this->newLine();

        $results['California'] = $this->testCaliforniaDetailed();
        $this->newLine();

        $results['Nevada'] = $this->testNevadaDetailed();
        $this->newLine();

        $results['CCS'] = $this->testCcsDetailed();
        $this->newLine();

        // Summary
        $this->displaySummary($results);
        
        // Provide next steps
        $this->provideNextSteps($results);
    }

    /**
     * Run basic connectivity diagnostics.
     */
    protected function runConnectivityDiagnostics(): void
    {
        $this->info('🔍 Running Connectivity Diagnostics...');
        
        // Test basic internet connectivity
        $this->testBasicConnectivity();
        
        // Test DNS resolution
        $this->testDnsResolution();
    }

    /**
     * Test basic internet connectivity.
     */
    protected function testBasicConnectivity(): void
    {
        try {
            // First try with SSL verification disabled for Windows compatibility
            $response = Http::withoutVerifying()
                ->timeout(5)
                ->get('https://google.com');
                
            if ($response->successful()) {
                $this->info('✅ Internet connectivity: OK (SSL verification disabled for testing)');
            } else {
                $this->warn('⚠️  Internet connectivity: Limited (' . $response->status() . ')');
            }
        } catch (Exception $e) {
            $this->error('❌ Internet connectivity: Failed - ' . $e->getMessage());
            $this->line('   💡 This is likely a Windows SSL certificate issue. See SSL_CERTIFICATE_FIX.md');
        }
    }

    /**
     * Test DNS resolution for all state endpoints.
     */
    protected function testDnsResolution(): void
    {
        $hosts = [
            'Florida FLHSMV' => 'services.flhsmv.gov',
            'California TVCC' => 'xsg.dmv.ca.gov',
            'Nevada NTSA' => 'secure.ntsa.us',
            'CCS' => 'testingprovider.com',
        ];

        foreach ($hosts as $name => $host) {
            if (gethostbyname($host) !== $host) {
                $this->info("✅ DNS Resolution ($name): OK");
            } else {
                $this->error("❌ DNS Resolution ($name): Failed - Cannot resolve $host");
            }
        }
    }

    /**
     * Test Florida FLHSMV connection with detailed diagnostics.
     */
    protected function testFloridaDetailed(): array
    {
        $this->info('🏖️  Testing Florida FLHSMV DICDS...');
        
        $wsdlUrl = config('services.florida.wsdl_url');
        if (!$wsdlUrl) {
            $this->error('❌ Florida: WSDL URL not configured in services.florida.wsdl_url');
            return ['status' => 'error', 'message' => 'WSDL URL not configured'];
        }

        $this->line("   WSDL URL: $wsdlUrl");

        // Step 1: Test WSDL accessibility
        $wsdlAccessible = $this->testWsdlAccessibility($wsdlUrl);
        if (!$wsdlAccessible) {
            $this->warn('⚠️  Florida: WSDL not accessible, fallback available');
            return ['status' => 'fallback', 'message' => 'WSDL not accessible, using fallback'];
        }

        // Step 2: Test SOAP client creation
        try {
            $soapClient = new SoapClient($wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            $methods = $soapClient->__getFunctions();
            $this->info('✅ Florida: SOAP client created successfully');
            $this->line('   Available methods: ' . count($methods));
            
            return ['status' => 'success', 'message' => 'SOAP connection working'];

        } catch (SoapFault $e) {
            $this->error('❌ Florida: SOAP fault - ' . $e->getMessage());
            Log::error('Florida SOAP fault', ['error' => $e->getMessage(), 'code' => $e->getCode()]);
            return ['status' => 'fallback', 'message' => 'SOAP fault, fallback available'];
        } catch (Exception $e) {
            $this->error('❌ Florida: SOAP error - ' . $e->getMessage());
            Log::error('Florida SOAP error', ['error' => $e->getMessage()]);
            return ['status' => 'fallback', 'message' => 'SOAP error, fallback available'];
        }
    }

    /**
     * Test California TVCC connection with detailed diagnostics.
     */
    protected function testCaliforniaDetailed(): array
    {
        $this->info('🌴 Testing California TVCC...');
        
        $wsdlUrl = config('state-integrations.california.tvcc.url');
        if (!$wsdlUrl) {
            $this->error('❌ California: WSDL URL not configured in state-integrations.california.tvcc.url');
            return ['status' => 'error', 'message' => 'WSDL URL not configured'];
        }

        $this->line("   WSDL URL: $wsdlUrl");

        // Step 1: Test WSDL accessibility
        $wsdlAccessible = $this->testWsdlAccessibility($wsdlUrl);
        if (!$wsdlAccessible) {
            $this->error('❌ California: WSDL not accessible');
            return ['status' => 'error', 'message' => 'WSDL not accessible'];
        }

        // Step 2: Test SOAP client creation
        try {
            $soapClient = new SoapClient($wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            $this->info('✅ California: SOAP client created successfully');

            // Step 3: Test password configuration
            $password = $this->getTvccPassword();
            if ($password) {
                $this->info('✅ California: Password configured');
                return ['status' => 'success', 'message' => 'TVCC API ready'];
            } else {
                $this->warn('⚠️  California: Password not set in database');
                return ['status' => 'warning', 'message' => 'Password not configured'];
            }

        } catch (SoapFault $e) {
            $this->error('❌ California: SOAP fault - ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'SOAP fault: ' . $e->getMessage()];
        } catch (Exception $e) {
            $this->error('❌ California: SOAP error - ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'SOAP error: ' . $e->getMessage()];
        }
    }

    /**
     * Test Nevada NTSA connection with detailed diagnostics.
     */
    protected function testNevadaDetailed(): array
    {
        $this->info('🎰 Testing Nevada NTSA...');
        
        $url = config('state-integrations.nevada.ntsa.url');
        if (!$url) {
            $this->error('❌ Nevada: URL not configured in state-integrations.nevada.ntsa.url');
            return ['status' => 'error', 'message' => 'URL not configured'];
        }

        $this->line("   URL: $url");

        // Step 1: Test DNS resolution
        $host = parse_url($url, PHP_URL_HOST);
        if (gethostbyname($host) === $host) {
            $this->error("❌ Nevada: Cannot resolve hostname '$host'");
            $this->line("   This suggests the domain may not exist or DNS issues");
            return ['status' => 'error', 'message' => "Cannot resolve hostname '$host'"];
        }

        // Step 2: Test HTTP connectivity
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withOptions([
                    'allow_redirects' => true,
                ])
                ->get($url);
            
            if ($response->successful() || $response->status() === 405) {
                // 405 Method Not Allowed is expected for GET on POST endpoint
                $this->info('✅ Nevada: HTTP endpoint accessible');
                return ['status' => 'success', 'message' => 'NTSA endpoint accessible'];
            } else {
                $this->warn("⚠️  Nevada: Unexpected response ({$response->status()})");
                $this->line("   Response: " . substr($response->body(), 0, 100));
                return ['status' => 'warning', 'message' => 'Unexpected response: ' . $response->status()];
            }
        } catch (Exception $e) {
            $this->error('❌ Nevada: Connection failed');
            $this->line('   Error: ' . $e->getMessage());
            
            // Provide specific guidance based on error type
            if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
                $this->line('   💡 DNS resolution error. Domain "secure.ntsa.us" may not exist.');
                $this->line('   💡 Contact Nevada NTSA for correct URL or disable this integration.');
            } elseif (strpos($e->getMessage(), 'Connection timed out') !== false) {
                $this->line('   💡 This is a timeout error. The server may be down or blocking requests.');
            }
            
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Test CCS connection with detailed diagnostics.
     */
    protected function testCcsDetailed(): array
    {
        $this->info('⚖️  Testing CCS (Court Compliance System)...');
        
        $url = config('state-integrations.ccs.url');
        if (!$url) {
            $this->error('❌ CCS: URL not configured in state-integrations.ccs.url');
            return ['status' => 'error', 'message' => 'URL not configured'];
        }

        $this->line("   URL: $url");

        // Step 1: Test DNS resolution
        $host = parse_url($url, PHP_URL_HOST);
        if (gethostbyname($host) === $host) {
            $this->error("❌ CCS: Cannot resolve hostname '$host'");
            $this->line("   This suggests the domain may not exist or DNS issues");
            return ['status' => 'error', 'message' => "Cannot resolve hostname '$host'"];
        }

        // Step 2: Test HTTP connectivity
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withOptions([
                    'allow_redirects' => true,
                ])
                ->get($url);
            
            if ($response->successful() || $response->status() === 405) {
                // 405 Method Not Allowed is expected for GET on POST endpoint
                $this->info('✅ CCS: HTTP endpoint accessible');
                return ['status' => 'success', 'message' => 'CCS endpoint accessible'];
            } else {
                $this->warn("⚠️  CCS: Unexpected response ({$response->status()})");
                $this->line("   Response: " . substr($response->body(), 0, 100));
                return ['status' => 'warning', 'message' => 'Unexpected response: ' . $response->status()];
            }
        } catch (Exception $e) {
            $this->error('❌ CCS: Connection failed');
            $this->line('   Error: ' . $e->getMessage());
            
            // Provide specific guidance based on error type
            if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
                $this->line('   💡 DNS resolution error. Domain "testingprovider.com" may not exist.');
                $this->line('   💡 Contact CCS provider for correct URL or disable this integration.');
            } elseif (strpos($e->getMessage(), 'Connection timed out') !== false) {
                $this->line('   💡 This is a timeout error. The server may be down or blocking requests.');
            }
            
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Test WSDL accessibility with detailed error reporting.
     */
    protected function testWsdlAccessibility(string $wsdlUrl): bool
    {
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
                $this->line("   ❌ Cannot retrieve headers from WSDL URL");
                return false;
            }

            $statusCode = null;
            if (is_array($headers) && isset($headers[0])) {
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
                $statusCode = $matches[1] ?? null;
            }

            if ($statusCode && $statusCode == 200) {
                $this->line("   ✅ WSDL accessible (HTTP $statusCode)");
                return true;
            } else {
                $this->line("   ❌ WSDL not accessible (HTTP $statusCode)");
                return false;
            }

        } catch (Exception $e) {
            $this->line("   ❌ WSDL accessibility test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Display summary of all tests.
     */
    protected function displaySummary(array $results): void
    {
        $this->info('📊 Summary of State API Tests:');
        $this->newLine();

        $successCount = 0;
        $totalCount = count($results);

        foreach ($results as $state => $result) {
            $icon = match($result['status']) {
                'success' => '✅',
                'warning' => '⚠️ ',
                'fallback' => '🔄',
                default => '❌'
            };

            $this->line("$icon $state: " . $result['message']);
            
            if ($result['status'] === 'success' || $result['status'] === 'fallback') {
                $successCount++;
            }
        }

        $this->newLine();
        $this->info("Operational APIs: $successCount/$totalCount");
        
        if ($successCount === $totalCount) {
            $this->info('🎉 All state APIs are operational!');
        } elseif ($successCount > 0) {
            $this->warn('⚠️  Some APIs need attention, but system can operate with fallbacks');
        } else {
            $this->error('❌ Multiple API issues detected - check configurations');
        }

    }

    /**
     * Provide next steps based on test results.
     */
    protected function provideNextSteps(array $results): void
    {
        $this->newLine();
        $this->info('🔧 Recommended Next Steps:');

        foreach ($results as $state => $result) {
            if ($result['status'] === 'error') {
                $this->line("• $state: " . $this->getStateSpecificAdvice($state, $result));
            }
        }

        $this->newLine();
        $this->info('🛠️  General Actions:');
        $this->line('• Check admin interface: /admin/state-transmissions');
        $this->line('• Review logs: storage/logs/laravel.log');
        $this->line('• Test individual states: php artisan states:test-individual {state}');
        $this->line('• Enable fallback mode in .env for production stability');
        
        $this->newLine();
        $this->info('📞 Contact Information:');
        $this->line('• Florida FLHSMV: Contact for current WSDL endpoint');
        $this->line('• California DMV: Verify TVCC service availability');
        $this->line('• Nevada NTSA: Confirm if secure.ntsa.us is correct domain');
        $this->line('• CCS: Verify if testingprovider.com is still active');
    }

    /**
     * Get state-specific advice based on test results.
     */
    protected function getStateSpecificAdvice(string $state, array $result): string
    {
        return match($state) {
            'Florida' => 'Contact FLHSMV for current WSDL endpoint. Enable fallback mode.',
            'California' => 'Verify TVCC service with CA DMV. Check if endpoint changed.',
            'Nevada' => 'Domain "secure.ntsa.us" may not exist. Contact Nevada NTSA for correct URL.',
            'CCS' => 'Domain "testingprovider.com" may not exist. Verify with CCS provider.',
            default => 'Check configuration and contact service provider.'
        };
    }

    /**
     * Get TVCC password from database.
     */
    protected function getTvccPassword(): ?string
    {
        try {
            $passwordRecord = \DB::table('tvcc_passwords')->latest('updated_at')->first();
            return $passwordRecord ? $passwordRecord->password : null;
        } catch (Exception $e) {
            return null;
        }
    }
}