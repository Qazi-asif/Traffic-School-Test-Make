<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckFloridaSecurityTables extends Command
{
    protected $signature = 'florida:check-tables';

    protected $description = 'Check if Florida Security tables exist';

    public function handle()
    {
        $tables = [
            'florida_security_logs',
            'florida_login_attempts',
            'florida_password_history',
            'florida_audit_trails',
            'florida_compliance_checks',
            'florida_data_exports',
        ];

        $this->info('Checking Florida Security tables...');

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("✓ {$table} exists");
            } else {
                $this->error("✗ {$table} missing");
            }
        }
    }
}
