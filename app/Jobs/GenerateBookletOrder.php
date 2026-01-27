<?php

namespace App\Jobs;

use App\Models\BookletOrder;
use App\Services\BookletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBookletOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300;

    public function __construct(
        public BookletOrder $order
    ) {}

    public function handle(BookletService $bookletService): void
    {
        $bookletService->processOrder($this->order);
    }

    public function failed(\Throwable $exception): void
    {
        $this->order->markFailed($exception->getMessage());
    }
}
