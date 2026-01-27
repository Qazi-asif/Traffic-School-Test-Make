<?php

namespace App\Console\Commands;

use App\Services\CourtCodeService;
use Illuminate\Console\Command;

class SyncCourtCodes extends Command
{
    protected $signature = 'court-codes:sync {state} {--system=flhsmv}';

    protected $description = 'Sync court codes with state system';

    public function handle(CourtCodeService $service): int
    {
        $state = strtoupper($this->argument('state'));
        $system = $this->option('system');

        $this->info("Syncing court codes for {$state} with {$system}...");

        $result = $service->syncWithStateSystem($state, $system);

        $this->info("Synced: {$result['synced']}");

        if (! empty($result['errors'])) {
            $this->error('Errors:');
            foreach ($result['errors'] as $error) {
                $this->line("- {$error}");
            }
        }

        return self::SUCCESS;
    }
}
