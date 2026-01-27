<?php

namespace App\Console\Commands;

use App\Services\CourtCodeService;
use Illuminate\Console\Command;

class ImportCourtCodes extends Command
{
    protected $signature = 'court-codes:import {file} {--state=}';

    protected $description = 'Import court codes from CSV file';

    public function handle(CourtCodeService $service): int
    {
        $file = $this->argument('file');
        $state = strtoupper($this->option('state'));

        if (! $state) {
            $this->error('State code is required. Use --state=FL');

            return self::FAILURE;
        }

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Importing court codes from {$file} for state {$state}...");

        $stats = $service->importFromCsv($file, $state);

        $this->info("Imported: {$stats['imported']}");
        $this->info("Skipped: {$stats['skipped']}");

        if (! empty($stats['errors'])) {
            $this->error('Errors:');
            foreach ($stats['errors'] as $error) {
                $this->line("- {$error}");
            }
        }

        return self::SUCCESS;
    }
}
