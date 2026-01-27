<?php

namespace App\Jobs;

use App\Models\StateTransmission;
use App\Services\FlhsmvSoapService;
use App\Services\FlhsmvHttpService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFloridaTransmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    protected int $transmissionId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $transmissionId)
    {
        $this->transmissionId = $transmissionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transmission = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->find($this->transmissionId);

        if (! $transmission) {
            Log::error("State transmission not found: {$this->transmissionId}");

            return;
        }

        try {
            $enrollment = $transmission->enrollment;
            $user = $enrollment->user;

            // Validate required fields
            $validationErrors = $this->validateRequiredFields($user, $enrollment);
            if (! empty($validationErrors)) {
                $this->markAsError($transmission, 'VALIDATION_ERROR', implode(', ', $validationErrors));

                return;
            }

            // Build the payload
            $payload = $this->buildPayload($user, $enrollment);

            // Store payload in transmission record
            $transmission->update(['payload_json' => $payload]);

            // Send to Florida API
            $response = $this->sendToFloridaApi($payload, $enrollment);

            // Handle response
            $this->handleResponse($transmission, $response);

        } catch (Exception $e) {
            Log::error('Florida transmission failed', [
                'transmission_id' => $this->transmissionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->markAsError(
                $transmission,
                'EXCEPTION',
                $e->getMessage()
            );

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Validate required fields for Florida transmission.
     */
    protected function validateRequiredFields($user, $enrollment): array
    {
        $errors = [];

        if (empty($user->driver_license)) {
            $errors[] = 'Driver license number is required';
        }

        if (empty($enrollment->citation_number)) {
            $errors[] = 'Citation number is required';
        }

        if (empty($user->citation_number)) {
            $errors[] = 'Court case number is required';
        }

        if (empty($user->first_name)) {
            $errors[] = 'First name is required';
        }

        if (empty($user->last_name)) {
            $errors[] = 'Last name is required';
        }

        if (empty($enrollment->completed_at)) {
            $errors[] = 'Completion date is required';
        }

        return $errors;
    }

    /**
     * Build the payload for Florida API with user and enrollment objects.
     */
    protected function buildPayload($user, $enrollment): array
    {
        return [
            'user' => $user,
            'enrollment' => $enrollment,
            // Legacy format for backward compatibility
            'driver_license_number' => $user->driver_license,
            'citation_number' => $enrollment->citation_number,
            'court_case_number' => $user->citation_number,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name ?? '',
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'completion_date' => $enrollment->completed_at->format('Y-m-d'),
            'course_name' => $enrollment->course->name ?? 'Traffic School',
            'course_type' => $enrollment->course->course_type ?? 'BDI',
            'certificate_number' => $enrollment->floridaCertificate?->dicds_certificate_number ?? '',
            'school_id' => config('services.florida.school_id'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Send payload to Florida DICDS via HTTP service (SOAP-compatible).
     */
    protected function sendToFloridaApi(array $payload, $enrollment)
    {
        Log::info('Sending Florida transmission via HTTP SOAP', [
            'transmission_id' => $this->transmissionId,
            'enrollment_id' => $enrollment->id,
            'payload' => $payload,
        ]);

        try {
            // Try HTTP service first (works without SOAP extension)
            $httpService = new FlhsmvHttpService();
            $response = $httpService->submitCertificate($payload);
            
            // If HTTP service fails and SOAP extension is available, try SOAP as fallback
            if (!$response['success'] && extension_loaded('soap')) {
                Log::info('HTTP service failed, trying SOAP fallback', [
                    'transmission_id' => $this->transmissionId,
                ]);
                
                $soapService = new FlhsmvSoapService();
                $response = $soapService->submitCertificate($payload);
            }
            
            if ($response['success']) {
                $certificateNumber = $response['certificate_number'] ?? 'FL' . date('Y') . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT);
                
                // Create or update Florida certificate record
                $certificate = $enrollment->floridaCertificate;
                if (!$certificate) {
                    $certificate = $enrollment->floridaCertificate()->create([
                        'enrollment_id' => $enrollment->id,
                        'dicds_certificate_number' => $certificateNumber,
                        'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                        'completion_date' => $enrollment->completed_at,
                        'citation_number' => $enrollment->citation_number,
                        'driver_license_number' => $enrollment->user->driver_license,
                        'state' => 'FL',
                        'is_sent_to_student' => false,
                        'generated_at' => now(),
                    ]);
                }

                Log::info('Florida DICDS submission successful', [
                    'transmission_id' => $this->transmissionId,
                    'certificate_number' => $certificateNumber,
                ]);

                return (object) [
                    'successful' => true,
                    'status' => 200,
                    'body' => [
                        'certificate_number' => $certificateNumber,
                        'response_code' => $response['response_code'] ?? 'SUCCESS',
                        'message' => $response['message'] ?? 'Successfully submitted to Florida DICDS',
                    ],
                ];
            } else {
                return (object) [
                    'successful' => false,
                    'status' => $response['status'] ?? 500,
                    'body' => [
                        'error' => $response['error'] ?? 'Unknown DICDS error',
                        'code' => $response['code'] ?? 'DICDS_ERROR',
                    ],
                ];
            }

        } catch (\Exception $e) {
            Log::error('Florida DICDS submission failed', [
                'transmission_id' => $this->transmissionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return (object) [
                'successful' => false,
                'status' => 500,
                'body' => [
                    'error' => $e->getMessage(),
                    'code' => 'DICDS_EXCEPTION',
                ],
            ];
        }
    }

    /**
     * Handle SOAP response.
     */
    protected function handleResponse(StateTransmission $transmission, $response): void
    {
        $statusCode = $response->status ?? 500;
        $body = is_array($response->body) ? $response->body : (array) $response->body;

        Log::info('Florida SOAP response', [
            'transmission_id' => $this->transmissionId,
            'status_code' => $statusCode,
            'response' => $body,
        ]);

        if ($response->successful) {
            $transmission->update([
                'status' => 'success',
                'response_code' => $body['response_code'] ?? 'SUCCESS',
                'response_message' => $body['message'] ?? 'Successfully transmitted to Florida DICDS',
                'sent_at' => now(),
            ]);
        } else {
            // Get human-readable error message from SOAP service
            $errorCode = $body['code'] ?? (string) $statusCode;
            $errorMessage = $body['error'] ?? $body['message'] ?? 'Unknown SOAP error';
            
            // If we have a Florida error code, get the detailed message
            if (preg_match('/^[A-Z]{2}\d{3}$/', $errorCode)) {
                $soapService = new FlhsmvSoapService();
                $errorInfo = $soapService->mapFloridaErrorCode($errorCode);
                $errorMessage = $errorInfo['message'] . ' (Code: ' . $errorCode . ')';
            }
            
            $this->markAsError($transmission, $errorCode, $errorMessage);
        }
    }

    /**
     * Mark transmission as error.
     */
    protected function markAsError(StateTransmission $transmission, string $code, string $message): void
    {
        $newRetryCount = $transmission->retry_count + 1;

        $transmission->update([
            'status' => 'error',
            'response_code' => $code,
            'response_message' => $message,
            'retry_count' => $newRetryCount,
        ]);

        // Notify admins on repeated failures (3+ attempts)
        if ($newRetryCount >= 3) {
            $this->notifyAdminsOfFailure($transmission);
        }
    }

    /**
     * Notify administrators of repeated transmission failures.
     */
    protected function notifyAdminsOfFailure(StateTransmission $transmission): void
    {
        // Disabled - notifications table missing 'data' column
        // TODO: Fix notifications table schema
        return;
    }

    /**
     * Handle job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Florida transmission job failed permanently', [
            'transmission_id' => $this->transmissionId,
            'error' => $exception->getMessage(),
        ]);

        $transmission = StateTransmission::find($this->transmissionId);
        if ($transmission) {
            $this->markAsError($transmission, 'JOB_FAILED', 'Job failed after all retries: '.$exception->getMessage());
        }
    }
}
