<?php

namespace App\Jobs;

use App\Services\BookletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBookletQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 600;

    public function handle(BookletService $bookletService): void
    {
        $processed = $bookletService->processPendingOrders();

        \Log::info("Processed {$processed} booklet orders");
    }
}
