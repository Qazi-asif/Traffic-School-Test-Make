<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MissouriDorService
{
    private $apiUrl;
    private $credentials;
    private $timeout;

    public function __construct()
    {
        $this->apiUrl = config('missouri.dor_api_url');
        $this->credentials = [
            'username' => config('missouri.dor_username'),
            'password' => config('missouri.dor_password'),
            'school_id' => config('missouri.school_id'),
        ];
        $this->timeout = config('missouri.timeout', 30);
    }

    /**
     * Submit certificate to Missouri Department of Revenue
     */
    public function submitCertificate(array $certificateData): array
    {
        Log::info("Submitting certificate to Missouri DOR", [
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
            Log::error("Missouri DOR submission failed", [
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
     * Validate certificate data for Missouri requirements
     */
    private function validateCertificateData(array $data): void
    {
        $required = [
            'certificate_number',
            'student_name',
            'student_address',
            'completion_date',
            'final_exam_score',
            'course_hours'
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate exam score (Missouri requires 70%)
        if ($data['final_exam_score'] < 70) {
            throw new \Exception("Final exam score must be at least 70% for Missouri");
        }

        // Validate course hours (Missouri requires 8 hours)
        if ($data['course_hours'] < 8) {
            throw new \Exception("Course must be at least 8 hours for Missouri");
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
                'school_id' => $this->credentials['school_id'],
            ],
            'certificate_data' => [
                'certificate_number' => $data['certificate_number'],
                'student_name' => $data['student_name'],
                'student_address' => $data['student_address'],
                'driver_license_number' => $data['driver_license_number'] ?? '',
                'completion_date' => $data['completion_date'],
                'final_exam_score' => $data['final_exam_score'],
                'course_hours' => $data['course_hours'],
                'form_4444_number' => $data['form_4444_number'] ?? null,
                'approval_number' => $data['approval_number'] ?? null,
            ],
            'submission_type' => 'defensive_driving',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Send API request to Missouri DOR
     */
    private function sendApiRequest(array $requestData): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'TrafficSchool/1.0'
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
        Log::info("Missouri DOR response received", $responseData);

        if (!isset($responseData['success'])) {
            throw new \Exception("Invalid response format: missing success field");
        }

        $success = $responseData['success'] === true || $responseData['success'] === 'true';
        $message = $responseData['message'] ?? 'No message provided';
        $submissionId = $responseData['submission_id'] ?? null;
        $responseCode = $responseData['response_code'] ?? ($success ? 'SUCCESS' : 'ERROR');

        // Handle Missouri-specific error codes
        if (!$success && isset($responseData['error_code'])) {
            $message = $this->getMissouriErrorMessage($responseData['error_code']) . ': ' . $message;
        }

        return [
            'success' => $success,
            'message' => $message,
            'submission_id' => $submissionId,
            'response_code' => $responseCode,
            'form_4444_generated' => $responseData['form_4444_generated'] ?? false,
        ];
    }

    /**
     * Get Missouri-specific error messages
     */
    private function getMissouriErrorMessage(string $errorCode): string
    {
        $errorMessages = [
            'MO001' => 'Invalid student information',
            'MO002' => 'Course hours insufficient',
            'MO003' => 'Exam score below minimum',
            'MO004' => 'Invalid approval number',
            'MO005' => 'Duplicate submission',
            'MO006' => 'School not authorized',
            'MO007' => 'Form 4444 generation failed',
        ];

        return $errorMessages[$errorCode] ?? 'Unknown error';
    }

    /**
     * Generate Missouri Form 4444
     */
    public function generateForm4444(array $certificateData): array
    {
        try {
            $requestData = [
                'authentication' => [
                    'username' => $this->credentials['username'],
                    'password' => $this->credentials['password'],
                    'school_id' => $this->credentials['school_id'],
                ],
                'form_data' => [
                    'student_name' => $certificateData['student_name'],
                    'student_address' => $certificateData['student_address'],
                    'driver_license_number' => $certificateData['driver_license_number'] ?? '',
                    'completion_date' => $certificateData['completion_date'],
                    'course_hours' => $certificateData['course_hours'],
                    'approval_number' => $certificateData['approval_number'] ?? null,
                ],
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->apiUrl . '/generate-form-4444', $requestData);

            if (!$response->successful()) {
                throw new \Exception("Form 4444 generation failed: HTTP {$response->status()}");
            }

            $responseData = $response->json();

            return [
                'success' => $responseData['success'] ?? false,
                'form_4444_number' => $responseData['form_4444_number'] ?? null,
                'pdf_url' => $responseData['pdf_url'] ?? null,
                'message' => $responseData['message'] ?? 'Form 4444 generated',
            ];

        } catch (\Exception $e) {
            Log::error("Missouri Form 4444 generation failed", [
                'error' => $e->getMessage(),
                'certificate_data' => $certificateData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to Missouri DOR
     */
    public function testConnection(): array
    {
        try {
            $testData = [
                'certificate_number' => 'TEST-MO-' . time(),
                'student_name' => 'Test Student',
                'student_address' => '123 Test St, Test City, MO 12345',
                'completion_date' => date('Y-m-d'),
                'final_exam_score' => 85,
                'course_hours' => 8
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
     * Get submission status from Missouri DOR
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
                'form_4444_status' => $data['form_4444_status'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get Missouri DOR status", [
                'submission_id' => $submissionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}