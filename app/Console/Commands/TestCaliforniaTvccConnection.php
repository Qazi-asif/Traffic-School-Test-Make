<?php

namespace App\Console\Commands;

use App\Services\CaliforniaTvccService;
use Illuminate\Console\Command;

class TestCaliforniaTvccConnection extends Command
{
    protected $signature = 'california:test-tvcc-connection';
    protected $description = 'Test connection to California TVCC API';

    public function handle(CaliforniaTvccService $tvccService): int
    {
        $this->info('Testing California TVCC connection...');
        $this->newLine();

        // Check configuration
        $this->info('Checking configuration...');
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

        // Test connection
        $this->info('Testing SOAP connection...');
        $result = $tvccService->testConnection();

        if ($result['success']) {
            $this->info('✓ Connection successful!');
            $this->info("WSDL URL: {$result['wsdl_url']}");
            
            if (isset($result['methods'])) {
                $this->newLine();
                $this->info('Available SOAP methods:');
                
                if (isset($result['methods']['functions'])) {
                    foreach ($result['methods']['functions'] as $function) {
                        $this->line("  - {$function}");
                    }
                } else {
                    $this->line('  - Methods data available but not in expected format');
                }
            }
            
            return self::SUCCESS;
        } else {
            $this->error('✗ Connection failed!');
            $this->error("Error: {$result['error']}");
            
            if (isset($result['suggestion'])) {
                $this->warn("Suggestion: {$result['suggestion']}");
            }
            
            return self::FAILURE;
        }
    }
}