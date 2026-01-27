<?php

namespace App\Console\Commands;

use App\Services\FlhsmvSoapService;
use Illuminate\Console\Command;

class TestFlhsmvConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flhsmv:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test FLHSMV SOAP connection and API availability';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing FLHSMV SOAP Connection...');
        $this->newLine();

        $service = new FlhsmvSoapService();
        $result = $service->testConnection();

        if ($result['success']) {
            $this->info('✅ SOAP Connection Successful!');
            $this->info('Available Methods:');
            
            if (isset($result['methods'])) {
                foreach ($result['methods'] as $method) {
                    $this->line('  - ' . $method);
                }
            }
        } else {
            $this->error('❌ SOAP Connection Failed');
            $this->error('Error: ' . $result['error']);
            
            if (isset($result['suggestion'])) {
                $this->warn('Suggestion: ' . $result['suggestion']);
            }
            
            $this->newLine();
            $this->info('Fallback options:');
            $this->line('1. Check if WSDL URL is correct');
            $this->line('2. Verify network connectivity to FLHSMV servers');
            $this->line('3. Check firewall settings');
            $this->line('4. Contact FLHSMV for current API endpoints');
            $this->line('5. System will use HTTP fallback or simulation mode');
        }

        $this->newLine();
        $this->info('Configuration:');
        $this->line('WSDL URL: ' . config('services.florida.wsdl_url'));
        $this->line('Service URL: ' . config('services.florida.service_url'));
        $this->line('Username: ' . config('services.florida.username'));
        $this->line('School ID: ' . config('services.florida.school_id'));
        $this->line('Instructor ID: ' . config('services.florida.instructor_id'));
    }
}