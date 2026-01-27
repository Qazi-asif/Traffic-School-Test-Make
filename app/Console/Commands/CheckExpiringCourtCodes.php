<?php

namespace App\Console\Commands;

use App\Services\CourtCodeService;
use Illuminate\Console\Command;

class CheckExpiringCourtCodes extends Command
{
    protected $signature = 'court-codes:check-expiring {--days=30}';

    protected $description = 'Check for expiring court codes and send alerts';

    public function handle(CourtCodeService $service): int
    {
        $days = (int) $this->option('days');
        $codes = $service->getExpiringCodes($days);

        if ($codes->isEmpty()) {
            $this->info('No expiring codes found.');

            return self::SUCCESS;
        }

        $this->warn("Found {$codes->count()} codes expiring in the next {$days} days:");

        foreach ($codes as $code) {
            $daysUntil = now()->diffInDays($code->expiration_date);
            $this->line("- {$code->code_value} ({$code->code_type}) - {$code->court->court} - Expires in {$daysUntil} days");
        }

        return self::SUCCESS;
    }
}
