<?php

namespace App\Console\Commands;

use App\Services\CourtCodeService;
use Illuminate\Console\Command;

class ExportCourtCodes extends Command
{
    protected $signature = 'court-codes:export {--state=} {--type=}';

    protected $description = 'Export court codes to CSV';

    public function handle(CourtCodeService $service): int
    {
        $filters = [];

        if ($state = $this->option('state')) {
            $filters['state'] = strtoupper($state);
        }

        if ($type = $this->option('type')) {
            $filters['type'] = $type;
        }

        $this->info('Exporting court codes...');

        $filename = $service->exportToCsv($filters);

        $this->info("Exported to: {$filename}");

        return self::SUCCESS;
    }
}
