<?php

namespace App\Jobs;

use App\Models\StateTransmission;
use App\Notifications\RepeatedTransmissionFailure;
use App\Services\CaliforniaTvccService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendCaliforniaTransmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [300, 600, 1800, 3600]; // 5min, 10min, 30min, 1hr

    protected $transmissionId;

    public function __construct(int $transmissionId)
    {
        $this->transmissionId = $transmissionId;
    }

    public function handle(CaliforniaTvccService $tvccService): void
    {
        $transmission = StateTransmission::with(['enrollment.user', 'enrollment.course', 'enrollment.californiaCertificate'])
            ->find($this->transmissionId);

        if (! $transmission) {
            Log::error("State transmission not found: {$this->transmissionId}");

            return;
        }

        // Check if TVCC is enabled
        if (! $tvccService->isEnabled()) {
            $this->markAsError($transmission, 'DISABLED', 'California TVCC is not enabled');

            return;
        }

        // Validate configuration
        $configErrors = $tvccService->validateConfig();
        if (! empty($configErrors)) {
            $this->markAsError($transmission, 'CONFIG_ERROR', implode(', ', $configErrors));

            return;
        }

        $enrollment = $transmission->enrollment;
        $certificate = $enrollment->californiaCertificate;

        if (! $certificate) {
            $this->markAsError($transmission, 'NO_CERTIFICATE', 'California certificate not found');

            return;
        }

        try {
            // Submit to TVCC
            $result = $tvccService->submitCertificate($certificate);

            if ($result['success']) {
                $response = $result['response'];

                // Update certificate
                $certificate->update([
                    'cc_seq_nbr' => $response['ccSeqNbr'],
                    'cc_stat_cd' => $response['ccStatCd'],
                    'cc_sub_tstamp' => $response['ccSubTstamp'],
                    'certificate_number' => $response['ccSeqNbr'],
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                // Update transmission
                $transmission->update([
                    'status' => 'success',
                    'response_code' => $response['ccStatCd'],
                    'response_message' => "Certificate submitted successfully. Seq: {$response['ccSeqNbr']}",
                    'sent_at' => now(),
                ]);

                Log::info('California TVCC transmission successful', [
                    'transmission_id' => $transmission->id,
                    'certificate_id' => $certificate->id,
                    'cc_seq_nbr' => $response['ccSeqNbr'],
                ]);
            } else {
                $this->markAsError(
                    $transmission,
                    $result['code'] ?? 'UNKNOWN',
                    $result['error'] ?? 'Unknown error'
                );
            }
        } catch (Exception $e) {
            Log::error('California TVCC transmission exception', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->markAsError($transmission, 'EXCEPTION', $e->getMessage());

            throw $e; // Re-throw to trigger retry
        }
    }

    protected function markAsError(StateTransmission $transmission, string $code, string $message): void
    {
        $newRetryCount = $transmission->retry_count + 1;

        $transmission->update([
            'status' => 'error',
            'response_code' => $code,
            'response_message' => $message,
            'retry_count' => $newRetryCount,
        ]);

        // Update certificate
        if ($certificate = $transmission->enrollment->californiaCertificate) {
            $certificate->update([
                'status' => 'failed',
                'error_message' => $message,
            ]);
        }

        // Notify admins if max retries reached
        if ($newRetryCount >= config('california.retry.max_attempts', 5)) {
            $this->notifyAdminsOfFailure($transmission);
        }
    }

    protected function notifyAdminsOfFailure(StateTransmission $transmission): void
    {
        try {
            $admins = \App\Models\User::where('role', 'super_admin')->get();
            Notification::send($admins, new RepeatedTransmissionFailure($transmission));
        } catch (Exception $e) {
            Log::error('Failed to send transmission failure notification', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error('California TVCC job failed permanently', [
            'transmission_id' => $this->transmissionId,
            'error' => $exception->getMessage(),
        ]);

        $transmission = StateTransmission::find($this->transmissionId);

        if ($transmission) {
            $this->markAsError($transmission, 'JOB_FAILED', 'Job failed after all retries: '.$exception->getMessage());
        }
    }
}
