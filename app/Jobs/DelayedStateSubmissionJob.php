<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Services\StateSubmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DelayedStateSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $certificate;
    
    public $tries = 1; // This job only triggers the submission, actual retries are handled by state-specific jobs

    /**
     * Create a new job instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
        
        // Use state integration queue if configured
        $this->onQueue(config('state-integrations.queue.queue', 'state-submissions'));
    }

    /**
     * Execute the job.
     */
    public function handle(StateSubmissionService $submissionService): void
    {
        Log::info("Processing delayed state submission", [
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'state_code' => $this->certificate->state_code
        ]);

        try {
            // Check if certificate still exists and hasn't been submitted yet
            $certificate = Certificate::find($this->certificate->id);
            
            if (!$certificate) {
                Log::warning("Certificate not found for delayed submission", [
                    'certificate_id' => $this->certificate->id
                ]);
                return;
            }

            if ($certificate->is_sent_to_state) {
                Log::info("Certificate already submitted to state", [
                    'certificate_id' => $certificate->id
                ]);
                return;
            }

            // Submit to state
            $result = $submissionService->submitCertificate($certificate);

            Log::info("Delayed state submission completed", [
                'certificate_id' => $certificate->id,
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Delayed state submission failed", [
                'certificate_id' => $this->certificate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't retry this job - let the state-specific jobs handle retries
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Delayed state submission job failed permanently", [
            'certificate_id' => $this->certificate->id,
            'error' => $exception->getMessage()
        ]);
    }
}