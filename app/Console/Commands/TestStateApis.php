<?php

namespace App\Console\Commands;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;

class TestStateApis extends Command
{
    protected $signature = 'state:test-apis {--retry-failed : Retry failed transmissions}';
    protected $description = 'Test state API connections and retry failed transmissions';

    public function handle()
    {
        $this->info('🎯 Testing State API Integrations...');
        
        // Step 1: Test API connectivity
        $this->info("\nStep 1: Testing API connectivity...");
        
        $this->testFloridaFlhsmv();
        $this->testCaliforniaTvcc();
        $this->testNevadaNtsa();
        $this->testCcs();
        
        // Step 2: Retry failed transmissions if requested
        if ($this->option('retry-failed')) {
            $this->info("\nStep 2: Retrying failed transmissions...");
            $this->retryFailedTransmissions();
        }
        
        // Step 3: Show transmission statistics
        $this->info("\nStep 3: Transmission statistics...");
        $this->showTransmissionStats();
        
        return 0;
    }
    
    private function testFloridaFlhsmv()
    {
        $this->line("🏖️  Testing Florida FLHSMV...");
        
        $endpoint = 'https://services.flhsmv.gov/DriverSchoolWebService/wsPrimerComponentService.svc?wsdl';
        
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (compatible; TrafficSchool/1.0)'
                ]
            ]);
            
            $response = @file_get_contents($endpoint, false, $context);
            
            if ($response !== false && strpos($response, 'wsdl:definitions') !== false) {
                $this->info("  ✅ WSDL accessible");
            } else {
                $this->warn("  ⚠️  WSDL not accessible - may need IP whitelisting");
                $this->line("  📞 Contact: Florida DHSMV IT Support");
                $this->line("  🔑 Credentials: NMNSEdits / LoveFL2025!");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Connection failed: " . $e->getMessage());
        }
    }
    
    private function testCaliforniaTvcc()
    {
        $this->line("🌴 Testing California TVCC...");
        
        $endpoint = 'https://xsg.dmv.ca.gov/tvcc/tvccservice';
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $this->info("  ✅ Service accessible");
            } else {
                $this->warn("  ⚠️  Service not accessible (HTTP {$httpCode})");
                $this->line("  📞 Contact: California DMV TVCC Support");
                $this->line("  🔑 Credentials: Support@dummiestrafficschool.com");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Connection failed: " . $e->getMessage());
        }
    }
    
    private function testNevadaNtsa()
    {
        $this->line("🎰 Testing Nevada NTSA...");
        
        $endpoint = 'https://secure.ntsa.us/cgi-bin/register.cgi';
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $this->info("  ✅ Service accessible");
            } else {
                $this->warn("  ⚠️  Domain doesn't exist or service unavailable");
                $this->line("  📞 Contact: Nevada Traffic Safety Association");
                $this->line("  🔍 Need: Correct domain/URL for API");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Connection failed: " . $e->getMessage());
        }
    }
    
    private function testCcs()
    {
        $this->line("⚖️  Testing CCS...");
        
        $endpoint = 'http://testingprovider.com/ccs/register.jsp';
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $this->info("  ✅ Service accessible");
            } else {
                $this->warn("  ⚠️  Domain doesn't exist or service unavailable");
                $this->line("  📞 Contact: CCS System Administrator");
                $this->line("  🔍 Need: Correct production URL");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Connection failed: " . $e->getMessage());
        }
    }
    
    private function retryFailedTransmissions()
    {
        $failedTransmissions = StateTransmission::where('status', 'error')
            ->with(['enrollment.user'])
            ->get();
        
        $this->info("Found {$failedTransmissions->count()} failed transmissions");
        
        $retriedCount = 0;
        
        foreach ($failedTransmissions as $transmission) {
            $enrollment = $transmission->enrollment;
            
            if (!$enrollment || !$enrollment->user) {
                continue;
            }
            
            // Check if enrollment now has required data
            if (!empty($enrollment->citation_number) && !empty($enrollment->court_selected)) {
                $transmission->update([
                    'status' => 'pending',
                    'response_message' => null,
                    'response_code' => null,
                    'retry_count' => 0,
                    'sent_at' => null
                ]);
                
                $retriedCount++;
                $this->line("  Reset transmission {$transmission->id} ({$transmission->state}/{$transmission->system})");
            }
        }
        
        $this->info("✅ Reset {$retriedCount} transmissions for retry");
    }
    
    private function showTransmissionStats()
    {
        $stats = StateTransmission::selectRaw('
            state,
            system,
            status,
            COUNT(*) as count
        ')
        ->groupBy('state', 'system', 'status')
        ->get();
        
        $this->table(
            ['State', 'System', 'Status', 'Count'],
            $stats->map(function ($stat) {
                return [
                    $stat->state,
                    $stat->system,
                    $stat->status,
                    $stat->count
                ];
            })->toArray()
        );
        
        $totalTransmissions = StateTransmission::count();
        $successfulTransmissions = StateTransmission::where('status', 'success')->count();
        $failedTransmissions = StateTransmission::where('status', 'error')->count();
        $pendingTransmissions = StateTransmission::where('status', 'pending')->count();
        
        $successRate = $totalTransmissions > 0 ? round(($successfulTransmissions / $totalTransmissions) * 100, 1) : 0;
        
        $this->info("\n📊 Overall Statistics:");
        $this->info("Total transmissions: {$totalTransmissions}");
        $this->info("Successful: {$successfulTransmissions}");
        $this->info("Failed: {$failedTransmissions}");
        $this->info("Pending: {$pendingTransmissions}");
        $this->info("Success rate: {$successRate}%");
        
        if ($successRate < 50) {
            $this->warn("\n⚠️  Low success rate - contact state vendors for API updates");
        } elseif ($successRate < 90) {
            $this->line("\n🔄 Moderate success rate - monitor and optimize");
        } else {
            $this->info("\n🎉 Good success rate - system working well!");
        }
    }
}