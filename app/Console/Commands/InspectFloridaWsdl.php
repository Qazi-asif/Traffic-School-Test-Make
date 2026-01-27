<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoapClient;
use Exception;

class InspectFloridaWsdl extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'florida:inspect-wsdl';

    /**
     * The console command description.
     */
    protected $description = 'Inspect Florida FLHSMV WSDL to find available methods and their parameters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Inspecting Florida FLHSMV WSDL...');
        $this->newLine();

        $wsdlUrl = config('services.florida.wsdl_url');
        $this->info("WSDL URL: $wsdlUrl");
        $this->newLine();

        try {
            $soapClient = new SoapClient($wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            $this->info('✅ SOAP client created successfully');
            $this->newLine();

            // Get available functions/methods
            $functions = $soapClient->__getFunctions();
            $this->info('📋 Available SOAP Methods:');
            foreach ($functions as $function) {
                $this->line("   • $function");
            }
            $this->newLine();

            // Get available types
            $types = $soapClient->__getTypes();
            $this->info('📊 Available SOAP Types:');
            foreach ($types as $type) {
                $this->line("   • $type");
            }
            $this->newLine();

            // Try to get more detailed information
            $this->info('🔧 SOAP Client Details:');
            $this->line('   Location: ' . ($soapClient->__getLocation() ?? 'Not available'));
            
            return 0;

        } catch (Exception $e) {
            $this->error('❌ Failed to inspect WSDL:');
            $this->error("   Message: {$e->getMessage()}");
            return 1;
        }
    }
}