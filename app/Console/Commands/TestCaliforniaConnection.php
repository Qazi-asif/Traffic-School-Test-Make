<?php

namespace App\Console\Commands;

use App\Services\CaliforniaTvccService;
use Illuminate\Console\Command;
use SoapClient;
use Exception;

class TestCaliforniaConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'california:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test California TVCC SOAP connection and API availability';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing California TVCC Connection...');
        $this->newLine();

        // Test WSDL accessibility
        $wsdlUrl = config('state-integrations.california.tvcc.url');
        $username = config('state-integrations.california.tvcc.user');
        
        $this->info('Configuration:');
        $this->line('WSDL URL: ' . $wsdlUrl);
        $this->line('Username: ' . $username);
        $this->line('Password: ' . ($this->getTvccPassword() ? '[SET]' : '[NOT SET]'));
        $this->newLine();

        // Test WSDL accessibility
        if ($this->testWsdlAccessibility($wsdlUrl)) {
            $this->info('✅ WSDL URL is accessible');
            
            // Test SOAP client creation
            if ($this->testSoapClient($wsdlUrl)) {
                $this->info('✅ SOAP Client created successfully');
                $this->info('✅ California TVCC API is ready for use');
            } else {
                $this->error('❌ SOAP Client creation failed');
            }
        } else {
            $this->error('❌ WSDL URL is not accessible');
            $this->warn('This may be due to:');
            $this->line('1. Network restrictions or firewall');
            $this->line('2. California DMV server access controls');
            $this->line('3. Incorrect WSDL endpoint');
            $this->line('4. SSL/TLS certificate issues');
        }

        $this->newLine();
        $this->info('Testing password retrieval...');
        
        $password = $this->getTvccPassword();
        if ($password) {
            $this->info('✅ TVCC password found in database');
        } else {
            $this->warn('⚠️  TVCC password not set');
            $this->line('Set password using: php artisan tvcc:password "your_password"');
        }

        $this->newLine();
        $this->info('Test complete.');
    }

    /**
     * Test if WSDL URL is accessible.
     */
    protected function testWsdlAccessibility(string $wsdlUrl): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $headers = @get_headers($wsdlUrl, 1, $context);
            return $headers && strpos($headers[0], '200') !== false;
        } catch (Exception $e) {
            $this->error('Error checking WSDL: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test SOAP client creation.
     */
    protected function testSoapClient(string $wsdlUrl): bool
    {
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

            // Get available methods
            $methods = $soapClient->__getFunctions();
            
            if ($methods) {
                $this->info('Available SOAP methods:');
                foreach ($methods as $method) {
                    $this->line('  - ' . $method);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->error('SOAP Client error: ' . $e->getMessage());
            return false;
        }
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
            $this->error('Database error: ' . $e->getMessage());
            return null;
        }
    }
}