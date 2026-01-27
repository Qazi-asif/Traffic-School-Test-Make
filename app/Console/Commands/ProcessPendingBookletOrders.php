<?php

namespace App\Console\Commands;

use App\Services\BookletService;
use Illuminate\Console\Command;

class ProcessPendingBookletOrders extends Command
{
    protected $signature = 'booklets:process-pending';

    protected $description = 'Process all pending booklet orders';

    public function handle(BookletService $bookletService): int
    {
        $this->info('Processing pending booklet orders...');

        $processed = $bookletService->processPendingOrders();

        $this->info("Processed {$processed} booklet orders successfully.");

        return Command::SUCCESS;
    }
}
