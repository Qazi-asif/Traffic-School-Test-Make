<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Court;
use App\Models\StateTransmission;
use App\Models\TvccPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckStateIntegrations extends Command
{
    protected $signature = 'state:check';
    protected $description = 'Check state integrations configuration status';

    public function handle(): int
    {
        $this->info('🔍 Checking State Integrations Configuration...');
        $this->newLine();

        $allGood = true;

        // Check migrations
        $allGood = $this->checkMigrations() && $allGood;
        $this->newLine();

        // Check configuration
        $allGood = $this->checkConfiguration() && $allGood;
        $this->newLine();

        // Check TVCC password
        $allGood = $this->checkTvccPassword() && $allGood;
        $this->newLine();

        // Check course flags
        $this->checkCourseFlags();
        $this->newLine();

        // Check court mappings
        $this->checkCourtMappings();
        $this->newLine();

        // Check transmissions
        $this->checkTransmissions();
        $this->newLine();

        if ($allGood) {
            $this->info('✅ All critical checks passed!');
            return 0;
        } else {
            $this->warn('⚠️  Some issues found. Please review above.');
            return 1;
        }
    }

    protected function checkMigrations(): bool
    {
        $this->info('📋 Checking Migrations...');

        $checks = [
            'state_transmissions.system' => Schema::hasColumn('state_transmissions', 'system'),
            'tvcc_passwords table' => Schema::hasTable('tvcc_passwords'),
            'courses.tvcc_enabled' => Schema::hasColumn('courses', 'tvcc_enabled'),
            'courts.tvcc_court_code' => Schema::hasColumn('courts', 'tvcc_court_code'),
        ];

        $allPassed = true;
        foreach ($checks as $name => $passed) {
            if ($passed) {
                $this->line("  ✓ {$name}");
            } else {
                $this->error("  ✗ {$name} - MISSING");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    protected function checkConfiguration(): bool
    {
        $this->info('⚙️  Checking Configuration...');

        $configs = [
            'STATE_TRANSMISSION_SYNC' => config('state-integrations.sync_execution'),
            'CALIFORNIA_TVCC_ENABLED' => config('state-integrations.california.tvcc.enabled'),
            'CALIFORNIA_TVCC_URL' => config('state-integrations.california.tvcc.url'),
            'NEVADA_NTSA_ENABLED' => config('state-integrations.nevada.ntsa.enabled'),
            'CCS_ENABLED' => config('state-integrations.ccs.enabled'),
        ];

        $allSet = true;
        foreach ($configs as $name => $value) {
            if ($value) {
                $display = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                $this->line("  ✓ {$name}: {$display}");
            } else {
                $this->warn("  ⚠ {$name}: not set");
                $allSet = false;
            }
        }

        return $allSet;
    }

    protected function checkTvccPassword(): bool
    {
        $this->info('🔑 Checking TVCC Password...');

        try {
            $password = TvccPassword::current();
            if ($password && $password !== 'change_me_in_production') {
                $this->line("  ✓ TVCC password is set");
                return true;
            } else {
                $this->warn("  ⚠ TVCC password needs to be updated");
                $this->line("  Run: php artisan tvcc:password");
                return false;
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Error checking password: {$e->getMessage()}");
            return false;
        }
    }

    protected function checkCourseFlags(): void
    {
        $this->info('📚 Checking Course Flags...');

        $tvccEnabled = Course::where('tvcc_enabled', true)->count();
        $ntsaEnabled = Course::where('ntsa_enabled', true)->count();
        $ccsEnabled = Course::where('ccs_enabled', true)->count();

        $this->line("  TVCC enabled: {$tvccEnabled} courses");
        $this->line("  NTSA enabled: {$ntsaEnabled} courses");
        $this->line("  CCS enabled: {$ccsEnabled} courses");

        if ($tvccEnabled === 0 && $ntsaEnabled === 0 && $ccsEnabled === 0) {
            $this->warn("  ⚠ No courses have integrations enabled");
        }
    }

    protected function checkCourtMappings(): void
    {
        $this->info('🏛️  Checking Court Mappings...');

        $tvccMapped = Court::whereNotNull('tvcc_court_code')->count();
        $ctsiMapped = Court::whereNotNull('ctsi_court_id')->count();
        $ntsaMapped = Court::whereNotNull('ntsa_court_name')->count();
        $totalCourts = Court::count();

        $this->line("  TVCC mapped: {$tvccMapped}/{$totalCourts} courts");
        $this->line("  CTSI mapped: {$ctsiMapped}/{$totalCourts} courts");
        $this->line("  NTSA mapped: {$ntsaMapped}/{$totalCourts} courts");

        if ($tvccMapped === 0 && $ctsiMapped === 0 && $ntsaMapped === 0) {
            $this->warn("  ⚠ No courts have mappings configured");
        }
    }

    protected function checkTransmissions(): void
    {
        $this->info('📡 Checking Transmissions...');

        $total = StateTransmission::count();
        $pending = StateTransmission::where('status', 'pending')->count();
        $success = StateTransmission::where('status', 'success')->count();
        $error = StateTransmission::where('status', 'error')->count();

        $this->line("  Total: {$total}");
        $this->line("  Pending: {$pending}");
        $this->line("  Success: {$success}");
        $this->line("  Error: {$error}");

        if ($total === 0) {
            $this->line("  ℹ️  No transmissions yet");
        }

        // Show by system
        $bySystems = StateTransmission::selectRaw('system, count(*) as count')
            ->groupBy('system')
            ->get();

        if ($bySystems->isNotEmpty()) {
            $this->line("  By System:");
            foreach ($bySystems as $stat) {
                $this->line("    - {$stat->system}: {$stat->count}");
            }
        }
    }
}
