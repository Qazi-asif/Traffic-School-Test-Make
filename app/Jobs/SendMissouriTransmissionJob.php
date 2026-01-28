<?php

namespace App\Jobs;

use App\Models\StateTransmission;
use App\Services\MissouriDorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMissouriTransmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transmission;
    
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(StateTransmission $transmission)
    {
        $this->transmission = $transmission;
    }

    /**
     * Execute the job.
     */
    public function handle(MissouriDorService $dorService): void
    {
        Log::info("Processing Missouri DOR transmission", [
            'transmission_id' => $this->transmission->id,
            'certificate_id' => $this->transmission->certificate_id
        ]);

        try {
            // Update status to processing
            $this->transmission->update(['status' => 'processing']);

            // Submit to Missouri DOR
            $result = $dorService->submitCertificate($this->transmission->payload_json);

            if ($result['success']) {
                // Update transmission as successful
                $this->transmission->update([
                    'status' => 'success',
                    'response_code' => $result['response_code'] ?? 'SUCCESS',
                    'response_message' => $result['message'] ?? 'Successfully submitted to Missouri DOR',
                    'sent_at' => now(),
                ]);

                // Update certificate as sent to state
                if ($this->transmission->certificate) {
                    $this->transmission->certificate->update([
                        'is_sent_to_state' => true,
                        'state_submission_id' => $result['submission_id'] ?? null,
                        'state_submission_status' => 'success',
                        'state_submission_date' => now(),
                    ]);
                }

                Log::info("Missouri DOR transmission successful", [
                    'transmission_id' => $this->transmission->id,
                    'submission_id' => $result['submission_id'] ?? null
                ]);

            } else {
                // Handle submission failure
                $this->handleFailure($result['error'] ?? 'Unknown error', $result['response_code'] ?? 'ERROR');
            }

        } catch (\Exception $e) {
            Log::error("Missouri DOR transmission exception", [
                'transmission_id' => $this->transmission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->handleFailure($e->getMessage(), 'EXCEPTION');
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Missouri DOR transmission job failed permanently", [
            'transmission_id' => $this->transmission->id,
            'error' => $exception->getMessage()
        ]);

        $this->transmission->update([
            'status' => 'failed',
            'response_code' => 'JOB_FAILED',
            'response_message' => 'Job failed after maximum retries: ' . $exception->getMessage(),
        ]);
    }

    /**
     * Handle transmission failure
     */
    private function handleFailure(string $error, string $responseCode): void
    {
        $this->transmission->update([
            'status' => 'error',
            'response_code' => $responseCode,
            'response_message' => $error,
        ]);

        // Check if this is a retryable error
        $retryableErrors = [
            'TIMEOUT',
            'CONNECTION_ERROR',
            'SERVER_ERROR',
            'TEMPORARY_UNAVAILABLE'
        ];

        if (in_array($responseCode, $retryableErrors) && $this->attempts() < $this->tries) {
            Log::info("Missouri DOR transmission will be retried", [
                'transmission_id' => $this->transmission->id,
                'attempt' => $this->attempts(),
                'error' => $error
            ]);
            
            throw new \Exception("Retryable error: {$error}");
        } else {
            Log::error("Missouri DOR transmission failed permanently", [
                'transmission_id' => $this->transmission->id,
                'error' => $error,
                'response_code' => $responseCode
            ]);
        }
    }
}