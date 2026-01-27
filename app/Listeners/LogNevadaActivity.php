<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Services\NevadaComplianceService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogNevadaActivity implements ShouldQueue
{
    protected $complianceService;

    public function __construct(NevadaComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Handle the event
     */
    public function handle($event): void
    {
        // Handle login events
        if ($event instanceof Login) {
            $this->complianceService->logLogin($event->user);
        }

        // Handle course completion
        if ($event instanceof CourseCompleted) {
            $this->complianceService->logCompletion($event->enrollment);
        }
    }
}
