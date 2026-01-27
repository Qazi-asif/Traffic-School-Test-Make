<?php

namespace App\Console\Commands;

use App\Services\FlhsmvHttpService;
use App\Services\CaliforniaTvccHttpService;
use Illuminate\Console\Command;

class TestHttpSoapServices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'soap:test-http {service?} {--detailed : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Test HTTP SOAP services (works without SOAP extension)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = $this->argument('service');
        $verbose = $this->option('detailed');

        $this->info('Testing HTTP SOAP Services (No SOAP Extension Required)');
        $this->line('');

        // Check if SOAP extension is available
        $soapAvailable = extension_loaded('soap');
        $this->info('SOAP Extension Available: ' . ($soapAvailable ? 'Yes' : 'No'));
        $this->line('');

        if (!$service || $service === 'florida') {
            $this->testFloridaService($verbose);
        }

        if (!$service || $service === 'california') {
            $this->testCaliforniaService($verbose);
        }

        $this->line('');
        $this->info('HTTP SOAP testing completed!');
    }

    /**
     * Test Florida FLHSMV HTTP service.
     */
    protected function testFloridaService(bool $verbose): void
    {
        $this->line('🏛️  Testing Florida FLHSMV HTTP Service...');

        try {
            $service = new FlhsmvHttpService();
            $result = $service->testConnection();

            if ($result['success']) {
                $this->info('✅ Florida HTTP SOAP connection successful');
                if ($verbose) {
                    $this->line('   Endpoint: ' . ($result['endpoint'] ?? 'N/A'));
                }
            } else {
                $this->error('❌ Florida HTTP SOAP connection failed');
                $this->line('   Error: ' . $result['error']);
                if (isset($result['suggestion'])) {
                    $this->line('   Suggestion: ' . $result['suggestion']);
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Florida HTTP SOAP test failed with exception');
            $this->line('   Error: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Test California TVCC HTTP service.
     */
    protected function testCaliforniaService(bool $verbose): void
    {
        $this->line('🌴 Testing California TVCC HTTP Service...');

        try {
            $service = new CaliforniaTvccHttpService();
            $result = $service->testConnection();

            if ($result['success']) {
                $this->info('✅ California TVCC HTTP SOAP connection successful');
                if ($verbose) {
                    $this->line('   Endpoint: ' . ($result['endpoint'] ?? 'N/A'));
                }
            } else {
                $this->error('❌ California TVCC HTTP SOAP connection failed');
                $this->line('   Error: ' . $result['error']);
                if (isset($result['suggestion'])) {
                    $this->line('   Suggestion: ' . $result['suggestion']);
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ California TVCC HTTP SOAP test failed with exception');
            $this->line('   Error: ' . $e->getMessage());
        }

        $this->line('');
    }
}