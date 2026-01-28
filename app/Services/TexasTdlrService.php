<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TexasTdlrService
{
    private $apiUrl;
    private $credentials;
    private $timeout;

    public function __construct()
    {
        $this->apiUrl = config('texas.tdlr_api_url');
        $this->credentials = [
            'username' => config('texas.tdlr_username'),
            'password' => config('texas.tdlr_password'),
            'provider_id' => config('texas.provider_id'),
        ];
        $this->timeout = config('texas.timeout', 30);
    }

    /**
     * Submit certificate to Texas TDLR
     */
    public function submitCertificate(array $certificateData): array
    {
        Log::info("Submitting certificate to Texas TDLR", [
            'certificate_number' => $certificateData['certificate_number'] ?? 'N/A'
        ]);

        try {
            // Validate required fields
            $this->validateCertificateData($certificateData);

            // Build API request
            $requestData = $this->buildApiRequest($certificateData);

            // Send API request
            $response = $this->sendApiRequest($requestData);

            // Parse response
            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error("Texas TDLR submission failed", [
                'error' => $e->getMessage(),
                'certificate_data' => $certificateData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response_code' => 'EXCEPTION'
            ];
        }
    }

    /**
     * Validate certificate data for Texas requirements
     */
    private function validateCertificateData(array $data): void
    {
        $required = [
            'certificate_number',
            'student_name',
            'driver_license_number',
            'completion_date',
            'final_exam_score',
            'course_hours'
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate Texas driver license format
        if (!preg_match('/^\d{8}$/', $data['driver_license_number'])) {
            throw new \Exception("Invalid Texas driver license format (must be 8 digits)");
        }

        // Validate exam score (Texas requires 75%)
        if ($data['final_exam_score'] < 75) {
            throw new \Exception("Final exam score must be at least 75% for Texas");
        }

        // Validate course hours (Texas requires 6 hours)
        if ($data['course_hours'] < 6) {
            throw new \Exception("Course must be at least 6 hours for Texas");
        }
    }

    /**
     * Build API request data
     */
    private function buildApiRequest(array $data): array
    {
        return [
            'authentication' => [
                'username' => $this->credentials['username'],
                'password' => $this->credentials['password'],
                'provider_id' => $this->credentials['provider_id'],
            ],
            'certificate_data' => [
                'certificate_number' => $data['certificate_number'],
                'student_name' => $data['student_name'],
                'driver_license_number' => $data['driver_license_number'],
                'citation_number' => $data['citation_number'] ?? '',
                'completion_date' => $data['completion_date'],
                'final_exam_score' => $data['final_exam_score'],
                'course_hours' => $data['course_hours'],
                'tdlr_course_id' => $data['tdlr_course_id'] ?? null,
                'court_name' => $data['court_name'] ?? '',
                'county' => $data['county'] ?? '',
            ],
            'submission_type' => 'defensive_driving',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Send API request to Texas TDLR
     */
    private function sendApiRequest(array $requestData): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'TrafficSchool-TDLR/1.0'
            ])
            ->post($this->apiUrl . '/submit-certificate', $requestData);

        if (!$response->successful()) {
            throw new \Exception("API request failed: HTTP {$response->status()} - {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Parse API response
     */
    private function parseResponse(array $responseData): array
    {
        Log::info("Texas TDLR response received", $responseData);

        if (!isset($responseData['success'])) {
            throw new \Exception("Invalid response format: missing success field");
        }

        $success = $responseData['success'] === true || $responseData['success'] === 'true';
        $message = $responseData['message'] ?? 'No message provided';
        $submissionId = $responseData['submission_id'] ?? null;
        $responseCode = $responseData['response_code'] ?? ($success ? 'SUCCESS' : 'ERROR');

        // Handle Texas-specific error codes
        if (!$success && isset($responseData['error_code'])) {
            $message = $this->getTexasErrorMessage($responseData['error_code']) . ': ' . $message;
        }

        return [
            'success' => $success,
            'message' => $message,
            'submission_id' => $submissionId,
            'response_code' => $responseCode,
            'tdlr_confirmation' => $responseData['tdlr_confirmation'] ?? null,
        ];
    }

    /**
     * Get Texas-specific error messages
     */
    private function getTexasErrorMessage(string $errorCode): string
    {
        $errorMessages = [
            'TX001' => 'Invalid driver license number',
            'TX002' => 'Course hours insufficient',
            'TX003' => 'Exam score below minimum',
            'TX004' => 'Invalid TDLR course ID',
            'TX005' => 'Duplicate submission',
            'TX006' => 'Provider not authorized',
            'TX007' => 'Citation number invalid',
            'TX008' => 'Court information missing',
        ];

        return $errorMessages[$errorCode] ?? 'Unknown error';
    }

    /**
     * Validate TDLR course approval
     */
    public function validateCourseApproval(string $tdlrCourseId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->apiUrl . '/validate-course/' . $tdlrCourseId, [
                    'username' => $this->credentials['username'],
                    'password' => $this->credentials['password'],
                ]);

            if (!$response->successful()) {
                throw new \Exception("Course validation failed: HTTP {$response->status()}");
            }

            $data = $response->json();

            return [
                'success' => true,
                'valid' => $data['valid'] ?? false,
                'course_name' => $data['course_name'] ?? null,
                'approval_date' => $data['approval_date'] ?? null,
                'expiration_date' => $data['expiration_date'] ?? null,
                'status' => $data['status'] ?? 'unknown',
            ];

        } catch (\Exception $e) {
            Log::error("Texas course validation failed", [
                'tdlr_course_id' => $tdlrCourseId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to Texas TDLR
     */
    public function testConnection(): array
    {
        try {
            $testData = [
                'certificate_number' => 'TEST-TX-' . time(),
                'student_name' => 'Test Student',
                'driver_license_number' => '12345678',
                'completion_date' => date('Y-m-d'),
                'final_exam_score' => 85,
                'course_hours' => 6
            ];

            // Use test endpoint if available
            $testUrl = $this->apiUrl . '/test-connection';
            
            $response = Http::timeout($this->timeout)
                ->post($testUrl, [
                    'authentication' => $this->credentials,
                    'test_data' => $testData
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connection test successful',
                    'test_result' => $data
                ];
            } else {
                throw new \Exception("Test connection failed: HTTP {$response->status()}");
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get submission status from Texas TDLR
     */
    public function getSubmissionStatus(string $submissionId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->apiUrl . '/submission-status/' . $submissionId, [
                    'username' => $this->credentials['username'],
                    'password' => $this->credentials['password'],
                ]);

            if (!$response->successful()) {
                throw new \Exception("Status request failed: HTTP {$response->status()}");
            }

            $data = $response->json();

            return [
                'success' => true,
                'status' => $data['status'] ?? 'unknown',
                'message' => $data['message'] ?? 'No status message',
                'processed_date' => $data['processed_date'] ?? null,
                'tdlr_confirmation' => $data['tdlr_confirmation'] ?? null,
                'court_notified' => $data['court_notified'] ?? false,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get Texas TDLR status", [
                'submission_id' => $submissionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Submit court notification for ticket dismissal
     */
    public function submitCourtNotification(array $data): array
    {
        try {
            $requestData = [
                'authentication' => $this->credentials,
                'notification_data' => [
                    'certificate_number' => $data['certificate_number'],
                    'citation_number' => $data['citation_number'],
                    'court_name' => $data['court_name'],
                    'county' => $data['county'],
                    'completion_date' => $data['completion_date'],
                    'student_name' => $data['student_name'],
                    'driver_license_number' => $data['driver_license_number'],
                ],
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->apiUrl . '/notify-court', $requestData);

            if (!$response->successful()) {
                throw new \Exception("Court notification failed: HTTP {$response->status()}");
            }

            $responseData = $response->json();

            return [
                'success' => $responseData['success'] ?? false,
                'message' => $responseData['message'] ?? 'Court notification sent',
                'notification_id' => $responseData['notification_id'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("Texas court notification failed", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}