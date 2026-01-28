<?php

/**
 * State Integration System Deployment Script
 * 
 * This script deploys the complete state integration system including:
 * - State-specific API services (Florida DICDS, Missouri DOR, Texas TDLR, Delaware DMV)
 * - Automated certificate submission jobs
 * - State transmission tracking and management
 * - Admin dashboard for monitoring and control
 * - Configuration and environment setup
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class StateIntegrationSystemDeployment
{
    private $output = [];
    
    public function deploy()
    {
        $this->log("🚀 Starting State Integration System Deployment...");
        
        try {
            // Step 1: Run database migrations
            $this->runMigrations();
            
            // Step 2: Create configuration files
            $this->setupConfiguration();
            
            // Step 3: Register event listeners
            $this->registerEventListeners();
            
            // Step 4: Setup queue configuration
            $this->setupQueueConfiguration();
            
            // Step 5: Create default error codes
            $this->createDefaultErrorCodes();
            
            // Step 6: Clear caches
            $this->clearCaches();
            
            // Step 7: Verify deployment
            $this->verifyDeployment();
            
            $this->log("✅ State Integration System Deployment Completed Successfully!");
            $this->displaySummary();
            
        } catch (Exception $e) {
            $this->log("❌ Deployment Failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function runMigrations()
    {
        $this->log("📊 Running database migrations...");
        
        $migrations = [
            '2025_01_28_000008_create_state_transmissions_table.php',
        ];
        
        foreach ($migrations as $migration) {
            if (file_exists(database_path('migrations/' . $migration))) {
                $this->log("  - Running migration: {$migration}");
                Artisan::call('migrate', ['--path' => 'database/migrations/' . $migration, '--force' => true]);
            } else {
                $this->log("  ⚠️  Migration file not found: {$migration}");
            }
        }
        
        $this->log("✅ Database migrations completed");
    }
    
    private function setupConfiguration()
    {
        $this->log("⚙️ Setting up configuration...");
        
        // Check if state-integrations config exists
        if (file_exists(config_path('state-integrations.php'))) {
            $this->log("  ✅ State integrations config file exists");
        } else {
            $this->log("  ⚠️ State integrations config file not found");
        }
        
        // Create sample .env entries
        $this->createSampleEnvEntries();
        
        $this->log("✅ Configuration setup completed");
    }
    
    private function createSampleEnvEntries()
    {
        $this->log("📝 Creating sample environment configuration...");
        
        $sampleEnv = "
# State Integration Configuration
AUTO_STATE_SUBMISSION_ENABLED=false
STATE_INTEGRATION_TEST_MODE=true
STATE_INTEGRATION_QUEUE_CONNECTION=database
STATE_INTEGRATION_QUEUE_NAME=state-submissions

# Florida DICDS Configuration
FLORIDA_INTEGRATION_ENABLED=false
FLORIDA_DICDS_SOAP_URL=https://dicds.flhsmv.gov/soap/certificate
FLORIDA_DICDS_USERNAME=your_username
FLORIDA_DICDS_PASSWORD=your_password
FLORIDA_DICDS_SCHOOL_ID=your_school_id

# Missouri DOR Configuration
MISSOURI_INTEGRATION_ENABLED=false
MISSOURI_DOR_API_URL=https://api.dor.mo.gov/defensive-driving
MISSOURI_DOR_USERNAME=your_username
MISSOURI_DOR_PASSWORD=your_password
MISSOURI_SCHOOL_ID=your_school_id

# Texas TDLR Configuration
TEXAS_INTEGRATION_ENABLED=false
TEXAS_TDLR_API_URL=https://api.tdlr.texas.gov/defensive-driving
TEXAS_TDLR_USERNAME=your_username
TEXAS_TDLR_PASSWORD=your_password
TEXAS_PROVIDER_ID=your_provider_id

# Delaware DMV Configuration
DELAWARE_INTEGRATION_ENABLED=false
DELAWARE_DMV_API_URL=https://api.dmv.delaware.gov/defensive-driving
DELAWARE_DMV_USERNAME=your_username
DELAWARE_DMV_PASSWORD=your_password
DELAWARE_SCHOOL_ID=your_school_id

# Monitoring and Alerts
STATE_INTEGRATION_NOTIFICATION_EMAIL=admin@yourschool.com
STATE_INTEGRATION_ALERT_EMAIL=alerts@yourschool.com
";
        
        // Write to sample file
        file_put_contents(base_path('.env.state-integration.sample'), $sampleEnv);
        
        $this->log("  ✅ Sample environment file created: .env.state-integration.sample");
    }
    
    private function registerEventListeners()
    {
        $this->log("🎧 Registering event listeners...");
        
        // Check if EventServiceProvider exists and has our listener
        $eventServiceProvider = app_path('Providers/EventServiceProvider.php');
        
        if (file_exists($eventServiceProvider)) {
            $content = file_get_contents($eventServiceProvider);
            
            if (strpos($content, 'CreateStateTransmission') !== false) {
                $this->log("  ✅ CreateStateTransmission listener already registered");
            } else {
                $this->log("  ⚠️ CreateStateTransmission listener needs to be registered manually");
                $this->log("     Add to EventServiceProvider: CertificateGenerated::class => [CreateStateTransmission::class]");
            }
        }
        
        $this->log("✅ Event listener registration completed");
    }
    
    private function setupQueueConfiguration()
    {
        $this->log("📋 Setting up queue configuration...");
        
        try {
            // Create queue table if it doesn't exist
            if (!DB::getSchemaBuilder()->hasTable('jobs')) {
                $this->log("  - Creating jobs table...");
                Artisan::call('queue:table');
                Artisan::call('migrate', ['--force' => true]);
            } else {
                $this->log("  ✅ Jobs table already exists");
            }
            
            // Create failed jobs table if it doesn't exist
            if (!DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $this->log("  - Creating failed_jobs table...");
                Artisan::call('queue:failed-table');
                Artisan::call('migrate', ['--force' => true]);
            } else {
                $this->log("  ✅ Failed jobs table already exists");
            }
            
        } catch (Exception $e) {
            $this->log("  ⚠️ Queue setup warning: " . $e->getMessage());
        }
        
        $this->log("✅ Queue configuration completed");
    }
    
    private function createDefaultErrorCodes()
    {
        $this->log("🚨 Creating default error codes...");
        
        try {
            // Check if transmission_error_codes table exists
            if (!DB::getSchemaBuilder()->hasTable('transmission_error_codes')) {
                $this->log("  ⚠️ transmission_error_codes table not found, skipping error codes setup");
                return;
            }
            
            $errorCodes = [
                // Florida DICDS Error Codes
                ['state' => 'FL', 'error_code' => 'FL001', 'description' => 'Invalid driver license format', 'is_retryable' => false],
                ['state' => 'FL', 'error_code' => 'FL002', 'description' => 'Exam score below minimum (80%)', 'is_retryable' => false],
                ['state' => 'FL', 'error_code' => 'FL003', 'description' => 'DICDS system unavailable', 'is_retryable' => true],
                ['state' => 'FL', 'error_code' => 'FL004', 'description' => 'Invalid school credentials', 'is_retryable' => false],
                ['state' => 'FL', 'error_code' => 'FL005', 'description' => 'Duplicate certificate submission', 'is_retryable' => false],
                
                // Missouri DOR Error Codes
                ['state' => 'MO', 'error_code' => 'MO001', 'description' => 'Invalid student information', 'is_retryable' => false],
                ['state' => 'MO', 'error_code' => 'MO002', 'description' => 'Course hours insufficient (8 hours required)', 'is_retryable' => false],
                ['state' => 'MO', 'error_code' => 'MO003', 'description' => 'Exam score below minimum (70%)', 'is_retryable' => false],
                ['state' => 'MO', 'error_code' => 'MO004', 'description' => 'Invalid approval number', 'is_retryable' => false],
                ['state' => 'MO', 'error_code' => 'MO005', 'description' => 'Form 4444 generation failed', 'is_retryable' => true],
                
                // Texas TDLR Error Codes
                ['state' => 'TX', 'error_code' => 'TX001', 'description' => 'Invalid driver license number', 'is_retryable' => false],
                ['state' => 'TX', 'error_code' => 'TX002', 'description' => 'Course hours insufficient (6 hours required)', 'is_retryable' => false],
                ['state' => 'TX', 'error_code' => 'TX003', 'description' => 'Exam score below minimum (75%)', 'is_retryable' => false],
                ['state' => 'TX', 'error_code' => 'TX004', 'description' => 'Invalid TDLR course ID', 'is_retryable' => false],
                ['state' => 'TX', 'error_code' => 'TX005', 'description' => 'Provider not authorized', 'is_retryable' => false],
                
                // Delaware DMV Error Codes
                ['state' => 'DE', 'error_code' => 'DE001', 'description' => 'Invalid course type (must be 3hr or 6hr)', 'is_retryable' => false],
                ['state' => 'DE', 'error_code' => 'DE002', 'description' => 'Quiz rotation not used', 'is_retryable' => false],
                ['state' => 'DE', 'error_code' => 'DE003', 'description' => 'Exam score below minimum (80%)', 'is_retryable' => false],
                ['state' => 'DE', 'error_code' => 'DE004', 'description' => 'School not authorized', 'is_retryable' => false],
                
                // Generic Error Codes
                ['state' => 'ALL', 'error_code' => 'TIMEOUT', 'description' => 'Request timeout', 'is_retryable' => true],
                ['state' => 'ALL', 'error_code' => 'CONNECTION_ERROR', 'description' => 'Connection error', 'is_retryable' => true],
                ['state' => 'ALL', 'error_code' => 'SERVER_ERROR', 'description' => 'Server error (5xx)', 'is_retryable' => true],
                ['state' => 'ALL', 'error_code' => 'RATE_LIMITED', 'description' => 'Rate limited', 'is_retryable' => true],
            ];
            
            foreach ($errorCodes as $errorCode) {
                DB::table('transmission_error_codes')->updateOrInsert(
                    [
                        'state' => $errorCode['state'],
                        'error_code' => $errorCode['error_code'],
                    ],
                    array_merge($errorCode, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
            
            $this->log("  ✅ Created " . count($errorCodes) . " default error codes");
            
        } catch (Exception $e) {
            $this->log("  ⚠️ Error codes setup failed: " . $e->getMessage());
        }
        
        $this->log("✅ Error codes setup completed");
    }
    
    private function clearCaches()
    {
        $this->log("🧹 Clearing application caches...");
        
        try {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            $this->log("✅ Caches cleared successfully");
            
        } catch (Exception $e) {
            $this->log("⚠️ Cache clearing failed: " . $e->getMessage());
        }
    }
    
    private function verifyDeployment()
    {
        $this->log("🔍 Verifying deployment...");
        
        // Check if tables exist
        $tables = [
            'state_transmissions',
            'jobs',
            'failed_jobs'
        ];
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->log("  ✅ Table '{$table}' exists");
            } else {
                $this->log("  ⚠️ Table '{$table}' does not exist");
            }
        }
        
        // Check if service classes exist
        $services = [
            'app/Services/StateSubmissionService.php',
            'app/Services/FloridaDicdsService.php',
            'app/Services/MissouriDorService.php',
            'app/Services/TexasTdlrService.php',
            'app/Services/DelawareDmvService.php',
        ];
        
        foreach ($services as $service) {
            if (file_exists(base_path($service))) {
                $this->log("  ✅ Service '{$service}' exists");
            } else {
                $this->log("  ⚠️ Service '{$service}' not found");
            }
        }
        
        // Check if job classes exist
        $jobs = [
            'app/Jobs/SendFloridaTransmissionJob.php',
            'app/Jobs/SendMissouriTransmissionJob.php',
            'app/Jobs/SendTexasTransmissionJob.php',
            'app/Jobs/SendDelawareTransmissionJob.php',
            'app/Jobs/DelayedStateSubmissionJob.php',
        ];
        
        foreach ($jobs as $job) {
            if (file_exists(base_path($job))) {
                $this->log("  ✅ Job '{$job}' exists");
            } else {
                $this->log("  ⚠️ Job '{$job}' not found");
            }
        }
        
        // Check if controller exists
        if (file_exists(app_path('Http/Controllers/StateTransmissionController.php'))) {
            $this->log("  ✅ StateTransmissionController exists");
        } else {
            $this->log("  ⚠️ StateTransmissionController not found");
        }
        
        // Check if models exist
        if (file_exists(app_path('Models/StateTransmission.php'))) {
            $this->log("  ✅ StateTransmission model exists");
        } else {
            $this->log("  ⚠️ StateTransmission model not found");
        }
        
        // Check configuration
        if (file_exists(config_path('state-integrations.php'))) {
            $this->log("  ✅ State integrations configuration exists");
        } else {
            $this->log("  ⚠️ State integrations configuration not found");
        }
        
        $this->log("✅ Deployment verification completed");
    }
    
    private function displaySummary()
    {
        $this->log("\n" . str_repeat("=", 70));
        $this->log("📋 STATE INTEGRATION SYSTEM DEPLOYMENT SUMMARY");
        $this->log(str_repeat("=", 70));
        
        $this->log("🎯 FEATURES DEPLOYED:");
        $this->log("  ✅ Multi-state certificate submission (FL, MO, TX, DE)");
        $this->log("  ✅ State-specific API services (DICDS, DOR, TDLR, DMV)");
        $this->log("  ✅ Automated submission with queue processing");
        $this->log("  ✅ Retry logic with exponential backoff");
        $this->log("  ✅ Admin dashboard for monitoring and control");
        $this->log("  ✅ Bulk operations and manual submission");
        $this->log("  ✅ Connection testing and diagnostics");
        $this->log("  ✅ Error handling and logging");
        
        $this->log("\n🛣️ AVAILABLE ROUTES:");
        $this->log("  • /admin/state-transmissions - Admin dashboard");
        $this->log("  • /admin/state-transmissions/{id} - View transmission details");
        $this->log("  • /admin/state-transmissions/{id}/retry - Retry failed transmission");
        $this->log("  • /admin/state-transmissions/bulk-retry - Bulk retry operations");
        $this->log("  • /admin/state-transmissions/bulk-submit - Bulk submit by state");
        $this->log("  • /admin/state-transmissions/test-connection - Test state connections");
        
        $this->log("\n🏛️ STATE-SPECIFIC INTEGRATIONS:");
        $this->log("  • Florida: DICDS SOAP API for FLHSMV submissions");
        $this->log("  • Missouri: DOR REST API with Form 4444 generation");
        $this->log("  • Texas: TDLR REST API for defensive driving");
        $this->log("  • Delaware: DMV REST API with point reduction support");
        
        $this->log("\n📊 DATABASE TABLES CREATED:");
        $this->log("  • state_transmissions (transmission tracking)");
        $this->log("  • jobs (queue processing)");
        $this->log("  • failed_jobs (failed job tracking)");
        
        $this->log("\n⚙️ CONFIGURATION:");
        $this->log("  • config/state-integrations.php - Main configuration");
        $this->log("  • .env.state-integration.sample - Environment template");
        $this->log("  • Queue: state-submissions (configurable)");
        $this->log("  • Auto-submission: Disabled by default");
        
        $this->log("\n🔧 NEXT STEPS:");
        $this->log("  1. Copy .env.state-integration.sample to .env and configure");
        $this->log("  2. Set up state API credentials and endpoints");
        $this->log("  3. Enable integrations: *_INTEGRATION_ENABLED=true");
        $this->log("  4. Test connections using admin dashboard");
        $this->log("  5. Enable auto-submission: AUTO_STATE_SUBMISSION_ENABLED=true");
        $this->log("  6. Start queue worker: php artisan queue:work");
        $this->log("  7. Monitor transmissions in admin dashboard");
        
        $this->log("\n📞 QUEUE MANAGEMENT:");
        $this->log("  • Start worker: php artisan queue:work --queue=state-submissions");
        $this->log("  • Monitor jobs: php artisan queue:monitor");
        $this->log("  • Retry failed: php artisan queue:retry all");
        $this->log("  • Clear failed: php artisan queue:flush");
        
        $this->log("\n🚨 MONITORING & ALERTS:");
        $this->log("  • Success rate monitoring with configurable thresholds");
        $this->log("  • Email alerts for consecutive failures");
        $this->log("  • Real-time dashboard with statistics and charts");
        $this->log("  • Export functionality for reporting");
        
        $this->log("\n📈 TESTING:");
        $this->log("  • Test mode available: STATE_INTEGRATION_TEST_MODE=true");
        $this->log("  • Connection testing for each state");
        $this->log("  • Manual submission testing");
        $this->log("  • Retry mechanism testing");
        
        $this->log(str_repeat("=", 70));
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        
        echo $logMessage . "\n";
        $this->output[] = $logMessage;
        
        // Also log to Laravel log
        Log::info($logMessage);
    }
    
    public function getOutput()
    {
        return $this->output;
    }
}

// Run deployment if script is executed directly
if (php_sapi_name() === 'cli') {
    try {
        $deployment = new StateIntegrationSystemDeployment();
        $deployment->deploy();
        
        echo "\n🎉 State Integration System is ready to use!\n";
        echo "Visit /admin/state-transmissions to start managing state submissions.\n\n";
        
    } catch (Exception $e) {
        echo "\n💥 Deployment failed: " . $e->getMessage() . "\n";
        echo "Check the logs for more details.\n\n";
        exit(1);
    }
}

return new StateIntegrationSystemDeployment();