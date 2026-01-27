<?php

namespace App\Console\Commands;

use App\Models\StateTransmission;
use App\Services\FlhsmvSoapService;
use Illuminate\Console\Command;

class TestFloridaTransmissionData extends Command
{
    protected $signature = 'florida:test-transmission-data {--fix : Fix data format issues}';
    protected $description = 'Test and fix Florida transmission data format issues';

    public function handle()
    {
        $this->info('🔍 Analyzing Florida Transmission Data');
        $this->newLine();

        // Get recent Florida transmissions
        $transmissions = StateTransmission::where('state', 'FL')
            ->with(['enrollment.user', 'enrollment.course'])
            ->latest()
            ->limit(10)
            ->get();

        if ($transmissions->isEmpty()) {
            $this->warn('No Florida transmissions found');
            return;
        }

        $this->info("Found {$transmissions->count()} Florida transmissions");
        $this->newLine();

        $issues = [];
        $soapService = new FlhsmvSoapService();

        foreach ($transmissions as $transmission) {
            $this->line("Transmission #{$transmission->id}:");
            
            $user = $transmission->enrollment->user;
            $enrollment = $transmission->enrollment;
            
            // Check driver license format
            $driverLicense = $user->driver_license ?? '';
            if (empty($driverLicense)) {
                $issues[] = "#{$transmission->id}: Missing driver license";
                $this->error("  ❌ Missing driver license");
            } elseif (!preg_match('/^[A-Z]\d{12}$/', $driverLicense)) {
                $issues[] = "#{$transmission->id}: Invalid DL format: {$driverLicense}";
                $this->error("  ❌ Invalid driver license format: {$driverLicense} (should be A999999999999)");
                
                if ($this->option('fix')) {
                    $fixedDL = $this->formatFloridaDriverLicense($driverLicense);
                    $user->update(['driver_license' => $fixedDL]);
                    $this->info("  ✅ Fixed to: {$fixedDL}");
                }
            } else {
                $this->info("  ✅ Driver license format OK: {$driverLicense}");
            }
            
            // Check citation number format
            $citationNumber = $enrollment->citation_number ?? '';
            if (empty($citationNumber)) {
                $issues[] = "#{$transmission->id}: Missing citation number";
                $this->error("  ❌ Missing citation number");
            } elseif (strlen($citationNumber) !== 7) {
                $issues[] = "#{$transmission->id}: Invalid citation length: {$citationNumber}";
                $this->error("  ❌ Citation number wrong length: {$citationNumber} (should be 7 chars)");
                
                if ($this->option('fix')) {
                    $fixedCitation = $this->formatCitationNumber($citationNumber);
                    $enrollment->update(['citation_number' => $fixedCitation]);
                    $this->info("  ✅ Fixed to: {$fixedCitation}");
                }
            } else {
                $this->info("  ✅ Citation number format OK: {$citationNumber}");
            }
            
            // Check required fields
            if (empty($user->first_name)) {
                $issues[] = "#{$transmission->id}: Missing first name";
                $this->error("  ❌ Missing first name");
            }
            
            if (empty($user->last_name)) {
                $issues[] = "#{$transmission->id}: Missing last name";
                $this->error("  ❌ Missing last name");
            }
            
            if (empty($user->date_of_birth)) {
                $issues[] = "#{$transmission->id}: Missing date of birth";
                $this->error("  ❌ Missing date of birth");
            }
            
            // Check current status and error codes
            $this->line("  Status: {$transmission->status}");
            if ($transmission->response_code) {
                $this->line("  Response Code: {$transmission->response_code}");
                
                if (preg_match('/^[A-Z]{2}\d{3}$/', $transmission->response_code)) {
                    $errorInfo = $soapService->mapFloridaErrorCode($transmission->response_code);
                    $this->line("  Error Meaning: {$errorInfo['message']}");
                    $this->line("  Retryable: " . ($errorInfo['retryable'] ? 'Yes' : 'No'));
                }
            }
            
            $this->newLine();
        }

        // Summary
        if (empty($issues)) {
            $this->info('🎉 All transmissions have valid data format!');
        } else {
            $this->error('❌ Found ' . count($issues) . ' data format issues:');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
            
            if (!$this->option('fix')) {
                $this->newLine();
                $this->info('💡 Run with --fix to automatically fix format issues');
            }
        }

        // Test API connection
        $this->newLine();
        $this->info('🔗 Testing Florida API connection...');
        $connectionResult = $soapService->testConnection();
        
        if ($connectionResult['success']) {
            $this->info('✅ Florida API connection successful');
        } else {
            $this->error('❌ Florida API connection failed: ' . $connectionResult['error']);
            if (isset($connectionResult['suggestion'])) {
                $this->warn('💡 ' . $connectionResult['suggestion']);
            }
        }
    }

    protected function formatFloridaDriverLicense(string $license): string
    {
        if (empty($license)) {
            return 'D123456789012'; // Default test format
        }
        
        // If already in Florida format (A999999999999), return as-is
        if (preg_match('/^[A-Z]\d{12}$/', $license)) {
            return $license;
        }
        
        // Try to convert to Florida format
        $cleaned = preg_replace('/[^A-Z0-9]/', '', strtoupper($license));
        
        if (strlen($cleaned) >= 13) {
            return substr($cleaned, 0, 13);
        }
        
        // Pad with zeros if too short
        return 'D' . str_pad(preg_replace('/[^0-9]/', '', $license), 12, '0', STR_PAD_LEFT);
    }

    protected function formatCitationNumber(string $citation): string
    {
        if (empty($citation)) {
            return '1234567'; // Default test citation
        }
        
        $cleaned = preg_replace('/[^0-9A-Z]/', '', strtoupper($citation));
        
        if (strlen($cleaned) === 7) {
            return $cleaned;
        }
        
        if (strlen($cleaned) > 7) {
            return substr($cleaned, 0, 7);
        }
        
        return str_pad($cleaned, 7, '0', STR_PAD_LEFT);
    }
}