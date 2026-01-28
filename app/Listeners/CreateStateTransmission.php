<?php

namespace App\Listeners;

use App\Events\CertificateGenerated;
use App\Services\StateSubmissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class CreateStateTransmission implements ShouldQueue
{
    use InteractsWithQueue;

    protected $submissionService;

    /**
     * Create the event listener.
     */
    public function __construct(StateSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Handle the event.
     */
    public function handle(CertificateGenerated $event): void
    {
        $certificate = $event->certificate;
        
        Log::info("Processing certificate for state submission", [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'state_code' => $certificate->state_code
        ]);

        // Check if auto-submission is enabled globally
        if (!Config::get('state-integrations.global.auto_submit_enabled', false)) {
            Log::info("Auto state submission is disabled globally");
            return;
        }

        // Check if state integration is enabled
        $stateConfig = $this->getStateConfig($certificate->state_code);
        if (!$stateConfig || !$stateConfig['enabled']) {
            Log::info("State integration disabled for: " . $certificate->state_code);
            return;
        }

        // Check if certificate meets state requirements
        if (!$certificate->meetsStateRequirements()) {
            Log::warning("Certificate does not meet state requirements", [
                'certificate_id' => $certificate->id,
                'state_code' => $certificate->state_code,
                'requirements' => $certificate->state_requirements
            ]);
            return;
        }

        try {
            // Add delay if configured
            $delay = Config::get('state-integrations.global.auto_submit_delay', 0);
            
            if ($delay > 0) {
                // Dispatch with delay
                \App\Jobs\DelayedStateSubmissionJob::dispatch($certificate)
                    ->delay(now()->addSeconds($delay));
                
                Log::info("State submission scheduled with delay", [
                    'certificate_id' => $certificate->id,
                    'delay_seconds' => $delay
                ]);
            } else {
                // Submit immediately
                $result = $this->submissionService->submitCertificate($certificate);
                
                Log::info("Immediate state submission result", [
                    'certificate_id' => $certificate->id,
                    'result' => $result
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to process certificate for state submission", [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't fail the entire certificate generation process
            // The submission can be retried manually later
        }
    }

    /**
     * Get state configuration
     */
    private function getStateConfig(string $stateCode): ?array
    {
        $stateKey = strtolower($this->getStateKey($stateCode));
        return Config::get("state-integrations.{$stateKey}");
    }

    /**
     * Get state configuration key
     */
    private function getStateKey(string $stateCode): string
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
                return 'florida';
            case 'MO':
                return 'missouri';
            case 'TX':
                return 'texas';
            case 'DE':
                return 'delaware';
            default:
                return strtolower($stateCode);
        }
    }
}