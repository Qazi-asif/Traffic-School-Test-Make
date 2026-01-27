<?php

namespace App\Console\Commands;

use App\Services\CaliforniaTVCC\TvccClient;
use Illuminate\Console\Command;

class TestCaliforniaTvccClient extends Command
{
    protected $signature = 'california:test-tvcc-client';
    protected $description = 'Test the California TVCC client with local WSDL files';

    public function handle(): int
    {
        $this->info('Testing California TVCC Client with Local WSDL...');
        $this->newLine();

        try {
            // 1. Initialize client
            $this->info('1. Initializing TVCC client...');
            $tvccClient = new TvccClient();
            $this->info('✓ TVCC client initialized successfully');
            $this->newLine();

            // 2. Test connection
            $this->info('2. Testing connection...');
            $connectionResult = $tvccClient->testConnection();
            
            if ($connectionResult['success']) {
                $this->info('✓ Connection test successful!');
                $this->info("WSDL Path: {$connectionResult['wsdl_path']}");
                $this->info("Endpoint: {$connectionResult['endpoint']}");
                
                if (isset($connectionResult['methods']['functions'])) {
                    $this->newLine();
                    $this->info('Available SOAP functions:');
                    foreach ($connectionResult['methods']['functions'] as $function) {
                        $this->line("  - {$function}");
                    }
                }
                
                if (isset($connectionResult['methods']['types'])) {
                    $this->newLine();
                    $this->info('Available SOAP types:');
                    foreach ($connectionResult['methods']['types'] as $type) {
                        $this->line("  - {$type}");
                    }
                }
            } else {
                $this->error('✗ Connection test failed!');
                $this->error("Error: {$connectionResult['error']}");
                return self::FAILURE;
            }

            $this->newLine();

            // 3. Test certificate submission (mock)
            $this->info('3. Testing certificate submission...');
            $testStudentId = '12345';
            
            $this->warn('Note: This will attempt to call the real TVCC API endpoint.');
            $this->warn('If you are not on an authorized network, this will fail.');
            
            if ($this->confirm('Do you want to proceed with the API call test?', false)) {
                $result = $tvccClient->submitCertificate($testStudentId);
                
                if ($result['success']) {
                    $this->info('✓ Certificate submission successful!');
                    $this->info("Response: {$result['response']}");
                    $this->info("Certificate Number: {$result['certificate_number']}");
                } else {
                    $this->warn('⚠ Certificate submission failed (expected if not on authorized network)');
                    $this->warn("Error: {$result['error']}");
                    $this->warn("Code: {$result['code']}");
                }
            } else {
                $this->info('Skipping API call test');
            }

            $this->newLine();
            $this->info('California TVCC client test completed!');
            $this->info('The client is properly configured with local WSDL files.');
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Test failed with exception: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return self::FAILURE;
        }
    }
}