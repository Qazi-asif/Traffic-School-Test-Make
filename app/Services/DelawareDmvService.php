<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class DelawareDmvService
{
    private $apiUrl;
    private $credentials;
    private $timeout;

    public function __construct()
    {
        $this->apiUrl = config('delaware.dmv_api_url');
        $this->credentials = [
            'username' => config('delaware.dmv_username'),
            'password' => config('delaware.dmv_password'),
            'school_id' => config('delaware.school_id'),
        ];
        $this->timeout = config('delaware.timeout', 30);
    }

    /**
     * Submit certificate to Delaware DMV
     */
    public function submitCertificate(array $certificateData): array
    {
        Log::info("Submitting certificate to Delaware DMV", [
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
            Log::error("Delaware DMV submission failed", [
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
     * Validate certificate data for Delaware requirements
     */
    private function validateCertificateData(array $data): void
    {
        $required = [
            'certificate_number',
            'student_name',
            'completion_date',
            'final_exam_score',
            'course_type',
            'course_hours'
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate exam score (Delaware requires 80%)
        if ($data['final_exam_score'] < 80) {
            throw new \Exception("Final exam score must be at least 80% for Delaware");
        }

        // Validate course type and hours
        $validTypes = ['3hr', '6hr'];
        if (!in_array($data['course_type'], $validTypes)) {
            throw new \Exception("Invalid course type. Must be '3hr' or '6hr'");
        }

        $minHours = $data['course_type'] === '3hr' ? 3 : 6;
        if ($data['course_hours'] < $minHours) {
            throw new \Exception("Course hours insufficient for {$data['course_type']} course");
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
                'driver_license_number' => $data['driver_license_number'] ?? '',
                'completion_date' => $data['completion_date'],
                'final_exam_score' => $data['final_exam_score'],
                'course_type' => $data['course_type'], // '3hr' or '6hr'
                'course_hours' => $data['course_hours'],
                'quiz_rotation_used' => $data['quiz_rotation_used'] ?? true,
            ],
            'submission_type' => $data['course_type'] === '3hr' ? 'point_reduction' : 'insurance_discount',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Send API request to Delaware DMV
     */
    private function sendApiRequest(array $requestData): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'TrafficSchool-DE-DMV/1.0'
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
        Log::info("Delaware DMV response received", $responseData);

        if (!isset($responseData['success'])) {
            throw new \Exception("Invalid response format: missing success field");
        }

        $success = $responseData['success'] === true || $responseData['success'] === 'true';
        $message = $responseData['message'] ?? 'No message provided';
        $submissionId = $responseData['submission_id'] ?? null;
        $responseCode = $responseData['response_code'] ?? ($success ? 'SUCCESS' : 'ERROR');

        // Handle Delaware-specific error codes
        if (!$success && isset($responseData['error_code'])) {
            $message = $this->getDelawareErrorMessage($responseData['error_code']) . ': ' . $message;
        }

        return [
            'success' => $success,
            'message' => $message,
            'submission_id' => $submissionId,
            'response_code' => $responseCode,
            'point_reduction_eligible' => $responseData['point_reduction_eligible'] ?? false,
            'insurance_discount_eligible' => $responseData['insurance_discount_eligible'] ?? false,
        ];
    }

    /**
     * Get Delaware-specific error messages
     */
    private function getDelawareErrorMessage(string $errorCode): string
    {
        $errorMessages = [
            'DE001' => 'Invalid student information',
            'DE002' => 'Course hours insufficient',
            'DE003' => 'Exam score below minimum',
            'DE004' => 'Invalid course type',
            'DE005' => 'Duplicate submission',
            'DE006' => 'School not authorized',
            'DE007' => 'Quiz rotation not used',
            'DE008' => 'Driver license format invalid',
        ];

        return $errorMessages[$errorCode] ?? 'Unknown error';
    }

    /**
     * Submit point reduction request
     */
    public function submitPointReduction(array $data): array
    {
        try {
            $requestData = [
                'authentication' => $this->credentials,
                'point_reduction_data' => [
                    'certificate_number' => $data['certificate_number'],
                    'driver_license_number' => $data['driver_license_number'],
                    'student_name' => $data['student_name'],
                    'completion_date' => $data['completion_date'],
                    'course_type' => '3hr',
                    'points_to_reduce' => $data['points_to_reduce'] ?? 3,
                ],
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->apiUrl . '/point-reduction', $requestData);

            if (!$response->successful()) {
                throw new \Exception("Point reduction request failed: HTTP {$response->status()}");
            }

            $responseData = $response->json();

            return [
                'success' => $responseData['success'] ?? false,
                'message' => $responseData['message'] ?? 'Point reduction processed',
                'points_reduced' => $responseData['points_reduced'] ?? 0,
                'effective_date' => $responseData['effective_date'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("Delaware point reduction failed", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Submit insurance discount certification
     */
    public function submitInsuranceDiscount(array $data): array
    {
        try {
            $requestData = [
                'authentication' => $this->credentials,
                'insurance_data' => [
                    'certificate_number' => $data['certificate_number'],
                    'student_name' => $data['student_name'],
                    'completion_date' => $data['completion_date'],
                    'course_type' => '6hr',
                    'insurance_company' => $data['insurance_company'] ?? null,
                    'policy_number' => $data['policy_number'] ?? null,
                ],
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->apiUrl . '/insurance-discount', $requestData);

            if (!$response->successful()) {
                throw new \Exception("Insurance discount request failed: HTTP {$response->status()}");
            }

            $responseData = $response->json();

            return [
                'success' => $responseData['success'] ?? false,
                'message' => $responseData['message'] ?? 'Insurance discount certified',
                'discount_percentage' => $responseData['discount_percentage'] ?? null,
                'valid_until' => $responseData['valid_until'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("Delaware insurance discount failed", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to Delaware DMV
     */
    public function testConnection(): array
    {
        try {
            $testData = [
                'certificate_number' => 'TEST-DE-' . time(),
                'student_name' => 'Test Student',
                'completion_date' => date('Y-m-d'),
                'final_exam_score' => 85,
                'course_type' => '6hr',
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
     * Get submission status from Delaware DMV
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
                'point_reduction_status' => $data['point_reduction_status'] ?? null,
                'insurance_discount_status' => $data['insurance_discount_status'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get Delaware DMV status", [
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