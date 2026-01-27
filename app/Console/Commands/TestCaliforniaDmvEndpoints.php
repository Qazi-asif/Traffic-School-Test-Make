<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestCaliforniaDmvEndpoints extends Command
{
    protected $signature = 'california:test-dmv-endpoints';
    protected $description = 'Test various California DMV endpoints to find the correct TVCC service';

    public function handle(): int
    {
        $this->info('Testing California DMV endpoints...');
        $this->newLine();

        $endpoints = [
            'https://www.dmv.ca.gov/tvcc/tvccservice',
            'https://www.dmv.ca.gov/tvcc/tvccservice?wsdl',
            'https://xsg.dmv.ca.gov/tvcc/tvccservice',
            'https://xsg.dmv.ca.gov/tvcc/tvccservice?wsdl',
            'https://services.dmv.ca.gov/tvcc/tvccservice',
            'https://services.dmv.ca.gov/tvcc/tvccservice?wsdl',
            'https://api.dmv.ca.gov/tvcc/tvccservice',
            'https://api.dmv.ca.gov/tvcc/tvccservice?wsdl',
            'https://webservices.dmv.ca.gov/tvcc/tvccservice',
            'https://webservices.dmv.ca.gov/tvcc/tvccservice?wsdl',
        ];

        foreach ($endpoints as $endpoint) {
            $this->info("Testing: {$endpoint}");
            
            try {
                // Test HTTP connectivity first
                $response = Http::timeout(10)
                    ->withOptions([
                        'verify' => false, // Skip SSL verification for testing
                    ])
                    ->get($endpoint);

                if ($response->successful()) {
                    $this->info("  ✓ HTTP 200 - Accessible");
                    
                    $contentType = $response->header('Content-Type');
                    $this->info("  Content-Type: {$contentType}");
                    
                    $body = $response->body();
                    if (strlen($body) > 0) {
                        $preview = substr($body, 0, 200);
                        $this->info("  Body preview: " . str_replace(["\n", "\r"], ' ', $preview) . "...");
                        
                        // Check if it looks like WSDL
                        if (strpos($body, '<wsdl:') !== false || strpos($body, '<definitions') !== false) {
                            $this->info("  ✓ Appears to be WSDL content");
                        } elseif (strpos($body, '<soap:') !== false || strpos($body, '<SOAP-ENV:') !== false) {
                            $this->info("  ✓ Appears to be SOAP content");
                        } elseif (strpos($body, '<?xml') !== false) {
                            $this->info("  ✓ XML content detected");
                        } elseif (strpos($body, '{') !== false && strpos($body, '}') !== false) {
                            $this->info("  ✓ JSON content detected");
                        }
                    }
                } else {
                    $this->warn("  ✗ HTTP {$response->status()} - {$response->reason()}");
                }
                
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        $this->info('Testing complete. Look for endpoints that return HTTP 200 with WSDL or XML content.');
        
        return self::SUCCESS;
    }
}