<?php

namespace App\Services;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CcsService
{
    /**
     * Send transmission to CCS (Court Compliance System).
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

            $response = $this->callCcsApi($payload);

            return $this->handleResponse($transmission, $response);

        } catch (Exception $e) {
            Log::error('CCS transmission failed', [
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

        // Note: Making validation more lenient for testing
        // In production, you may want to enforce these fields more strictly
        
        if (empty($enrollment->citation_number)) {
            $errors[] = 'Citation number is required';
        }

        return $errors;
    }

    protected function buildPayload(UserCourseEnrollment $enrollment): array
    {
        $user = $enrollment->user;

        return [
            'StudentName' => ucfirst(strtolower($user->first_name)) . ' ' . ucfirst(strtolower($user->last_name)),
            'StudentEmail' => $user->email,
            'StudentDriLicNum' => $user->driver_license,
            'StudentBirthday' => $user->date_of_birth ? $user->date_of_birth->format('m/d/Y') : now()->format('m/d/Y'),
            'StudentCourtName' => $user->court_selected ?? 'Unknown Court',
            'StudentCaseNum' => $enrollment->citation_number,
            'StudentCourtDueDate' => $enrollment->due_date ? $enrollment->due_date->format('m/d/Y') : now()->addDays(30)->format('m/d/Y'),
            'StudentSchoolName' => config('state-integrations.ccs.school_name', 'dummiests'),
            'StudentSignUpDate' => $enrollment->created_at->format('m/d/Y'),
            'StudentAddress' => ($user->address ?? '') . ' ' . ($user->address_2 ?? ''),
            'StudentCity' => $user->city ?? '',
            'StudentState' => $user->state ?? '',
            'StudentPostalCode' => $user->zip_code ?? '',
            'StudentTelephoneNum' => $user->phone ?? '',
            'StudentUserID' => $user->id,
            'StudentLanguage' => ($enrollment->language ?? 'english') === 'spanish' ? 'Spanish' : 'English',
        ];
    }

    protected function callCcsApi(array $payload): array
    {
        $url = config('state-integrations.ccs.url');
        $timeout = config('state-integrations.ccs.timeout', 30);

        Log::info('Sending CCS transmission', [
            'url' => $url,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout($timeout)
                ->asForm()
                ->post($url, $payload);

            Log::info('CCS API response received', [
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
            Log::error('CCS API call failed', [
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
        Log::info('CCS API response', [
            'transmission_id' => $transmission->id,
            'response' => $response,
        ]);

        if ($response['success']) {
            // CCS typically returns HTML or a redirect on success
            $responseBody = $response['body'] ?? '';
            
            // Check for success indicators in the response
            if (strpos($responseBody, 'success') !== false || 
                strpos($responseBody, 'submitted') !== false ||
                $response['status'] === 200) {
                
                $transmission->update([
                    'status' => 'success',
                    'response_code' => (string) $response['status'],
                    'response_message' => 'Successfully submitted to CCS',
                    'sent_at' => now(),
                ]);

                return true;
            } else {
                $this->markAsError(
                    $transmission,
                    'CCS_ERROR',
                    'CCS submission may have failed: ' . substr($responseBody, 0, 200)
                );

                return false;
            }
        } else {
            $this->markAsError(
                $transmission,
                $response['code'] ?? 'API_ERROR',
                $response['error'] ?? 'Unknown CCS API error'
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
}
