<?php

namespace App\Services;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NevadaNtsaService
{
    protected string $apiUrl;
    protected string $schoolName;
    protected string $testName;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('state-integrations.nevada.ntsa.url');
        $this->schoolName = config('state-integrations.nevada.ntsa.school_name');
        $this->testName = config('state-integrations.nevada.ntsa.test_name');
        $this->timeout = config('state-integrations.nevada.ntsa.timeout', 30);
    }

    /**
     * Send transmission to Nevada NTSA (Nevada Traffic Safety Association).
     */
    public function sendTransmission(StateTransmission $transmission): bool
    {
        try {
            $enrollment = $transmission->enrollment()->with(['user', 'course'])->first();

            if (!$enrollment) {
                $this->markAsError($transmission, 'ENROLLMENT_NOT_FOUND', 'Enrollment record not found');
                return false;
            }

            $validationErrors = $this->validateRequiredFields($enrollment);
            if (!empty($validationErrors)) {
                $this->markAsError($transmission, 'VALIDATION_ERROR', implode(', ', $validationErrors));
                return false;
            }

            $payload = $this->buildPayload($enrollment);
            $transmission->update(['payload_json' => $payload]);

            $response = $this->callNtsaApi($payload);

            return $this->handleResponse($transmission, $response);

        } catch (Exception $e) {
            Log::error('NTSA transmission failed', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            $this->markAsError($transmission, 'EXCEPTION', $e->getMessage());
            return false;
        }
    }

    protected function validateRequiredFields(UserCourseEnrollment $enrollment): array
    {
        $errors = [];
        $user = $enrollment->user;

        if (empty($user->first_name) || empty($user->last_name)) {
            $errors[] = 'Student name is required';
        }

        if (empty($user->email)) {
            $errors[] = 'Email is required';
        }

        if (empty($user->driver_license)) {
            $errors[] = 'Driver license is required';
        }

        if (empty($user->date_of_birth)) {
            $errors[] = 'Date of birth is required';
        }

        if (empty($enrollment->citation_number)) {
            $errors[] = 'Citation number is required';
        }

        if (empty($user->court_selected)) {
            $errors[] = 'Court selection is required';
        }

        if (empty($user->phone)) {
            $errors[] = 'Phone number is required';
        }

        return $errors;
    }

    protected function buildPayload(UserCourseEnrollment $enrollment): array
    {
        $user = $enrollment->user;

        return [
            'Name' => $user->first_name . ' ' . $user->last_name,
            'Email' => $user->email,
            'License' => $user->driver_license,
            'DOB' => $user->date_of_birth->format('Y-m-d'),
            'Court' => $this->getNtsaCourtName($user->court_selected),
            'CaseNum' => $enrollment->citation_number,
            'DueDate' => $enrollment->due_date ? $enrollment->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
            'School' => $this->schoolName,
            'TestName' => $this->testName,
            'Telephone' => $user->phone,
            'UniqueID' => $user->id,
            'Encrypt' => '0',
            'Demo' => '0',
            'Language' => ($enrollment->language ?? 'english') === 'spanish' ? 'Spanish' : 'English',
        ];
    }

    protected function callNtsaApi(array $payload): array
    {
        Log::info('Sending NTSA transmission', [
            'url' => $this->apiUrl,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->apiUrl, $payload);

            Log::info('NTSA API response received', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

        } catch (Exception $e) {
            Log::error('NTSA API call failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'API_ERROR',
            ];
        }
    }

    protected function handleResponse(StateTransmission $transmission, array $response): bool
    {
        Log::info('NTSA API response', [
            'transmission_id' => $transmission->id,
            'response' => $response,
        ]);

        if ($response['success']) {
            // NTSA typically returns HTML or a redirect on success
            $responseBody = $response['body'] ?? '';
            
            // Check for success indicators in the response
            if (strpos($responseBody, 'success') !== false || 
                strpos($responseBody, 'submitted') !== false ||
                $response['status'] === 200) {
                
                $transmission->update([
                    'status' => 'success',
                    'response_code' => (string) $response['status'],
                    'response_message' => 'Successfully submitted to Nevada NTSA',
                    'sent_at' => now(),
                ]);

                return true;
            } else {
                $this->markAsError(
                    $transmission,
                    'NTSA_ERROR',
                    'NTSA submission may have failed: ' . substr($responseBody, 0, 200)
                );

                return false;
            }
        } else {
            $this->markAsError(
                $transmission,
                $response['code'] ?? 'API_ERROR',
                $response['error'] ?? 'Unknown NTSA API error'
            );

            return false;
        }
    }

    protected function markAsError(StateTransmission $transmission, string $code, string $message): void
    {
        $transmission->update([
            'status' => 'error',
            'response_code' => $code,
            'response_message' => $message,
            'retry_count' => $transmission->retry_count + 1,
        ]);
    }

    protected function getNtsaCourtName(string $courtSelected): string
    {
        // Map court selections to NTSA court names
        // This should be populated from your courts table with ntsa_court_name field
        $courtMappings = [
            'Las Vegas Justice Court' => 'Las Vegas Justice Court',
            'Henderson Municipal Court' => 'Henderson Municipal Court',
            'North Las Vegas Municipal Court' => 'North Las Vegas Municipal Court',
            // Add more mappings as needed
        ];

        return $courtMappings[$courtSelected] ?? $courtSelected;
    }
}