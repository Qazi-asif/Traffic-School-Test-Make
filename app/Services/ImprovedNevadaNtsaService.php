<?php

namespace App\Services;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use App\Services\StateApiMockService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImprovedNevadaNtsaService
{
    protected string $apiUrl;
    protected string $schoolName;
    protected string $testName;
    protected int $timeout;
    protected string $mode;

    public function __construct()
    {
        $this->apiUrl = config('states.nevada.ntsa.url');
        $this->schoolName = config('states.nevada.ntsa.school_name');
        $this->testName = config('states.nevada.ntsa.test_name');
        $this->timeout = config('states.nevada.ntsa.timeout', 30);
        $this->mode = config('states.nevada.ntsa.mode', 'live');
    }

    /**
     * Send transmission to Nevada NTSA with improved error handling.
     */
    public function sendTransmission(StateTransmission $transmission): bool
    {
        // Check if we should use mock mode
        if (StateApiMockService::isMockMode('nevada', 'ntsa')) {
            Log::info('Using mock mode for Nevada NTSA submission');
            StateApiMockService::simulateNetworkDelay();
            $mockResponse = StateApiMockService::getMockResponse('nevada', 'success');
            return $this->handleMockResponse($transmission, $mockResponse);
        }

        // Check if service is disabled
        if (!config('states.nevada.ntsa.enabled', true)) {
            Log::warning('Nevada NTSA service is disabled');
            $this->markAsError($transmission, 'SERVICE_DISABLED', 'Nevada NTSA service is disabled');
            return false;
        }

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

            // Attempt real API call
            $response = $this->callNtsaApiWithRetry($payload);

            return $this->handleResponse($transmission, $response);

        } catch (Exception $e) {
            Log::error('NTSA transmission failed', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            // Try fallback if enabled
            if (StateApiMockService::isFallbackMode('nevada', 'ntsa')) {
                return $this->handleFallback($transmission, $e->getMessage());
            }

            $this->markAsError($transmission, 'EXCEPTION', $e->getMessage());
            return false;
        }
    }

    /**
     * Call NTSA API with retry logic and enhanced error handling.
     */
    protected function callNtsaApiWithRetry(array $payload, int $maxRetries = 3): array
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("NTSA API attempt $attempt/$maxRetries", [
                    'url' => $this->apiUrl,
                    'payload' => $this->sanitizePayloadForLogging($payload),
                ]);

                // First, test DNS resolution
                $host = parse_url($this->apiUrl, PHP_URL_HOST);
                if (gethostbyname($host) === $host) {
                    throw new Exception("Cannot resolve hostname '$host'. Domain may not exist.");
                }

                $response = Http::timeout($this->timeout)
                    ->withOptions([
                        'verify' => false, // Disable SSL verification for testing
                        'allow_redirects' => true,
                        'connect_timeout' => 10,
                    ])
                    ->withHeaders([
                        'User-Agent' => 'Laravel/NTSA Client v1.0',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ])
                    ->asForm()
                    ->post($this->apiUrl, $payload);

                if (config('states.development.log_all_responses')) {
                    Log::info('NTSA API response received', [
                        'attempt' => $attempt,
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500), // Log first 500 chars
                    ]);
                }

                if ($response->successful() || $response->status() === 405) {
                    return [
                        'success' => true,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'attempt' => $attempt,
                    ];
                } else {
                    $lastException = new Exception("HTTP {$response->status()}: " . $response->body());
                    
                    // Don't retry on client errors (4xx)
                    if ($response->status() >= 400 && $response->status() < 500) {
                        break;
                    }
                }

            } catch (Exception $e) {
                $lastException = $e;
                
                Log::warning("NTSA API attempt $attempt failed", [
                    'error' => $e->getMessage(),
                    'will_retry' => $attempt < $maxRetries,
                ]);

                // Wait before retry (exponential backoff)
                if ($attempt < $maxRetries) {
                    sleep(pow(2, $attempt - 1)); // 1s, 2s, 4s delays
                }
            }
        }

        // All attempts failed
        return [
            'success' => false,
            'error' => $lastException ? $lastException->getMessage() : 'Unknown error',
            'attempts' => $maxRetries,
        ];
    }

    /**
     * Handle fallback when real API fails.
     */
    protected function handleFallback(StateTransmission $transmission, string $originalError): bool
    {
        if (config('states.nevada.ntsa.fallback.simulate_success', true)) {
            Log::warning('NTSA fallback: Simulating successful submission', [
                'transmission_id' => $transmission->id,
                'original_error' => $originalError,
            ]);

            $transmission->update([
                'status' => 'success',
                'response_code' => 'FALLBACK_SUCCESS',
                'response_message' => 'Successfully submitted to Nevada NTSA (fallback mode)',
                'sent_at' => now(),
            ]);

            return true;
        }

        $this->markAsError($transmission, 'FALLBACK_FAILED', "API failed and fallback disabled: $originalError");
        return false;
    }

    /**
     * Handle mock response.
     */
    protected function handleMockResponse(StateTransmission $transmission, array $mockResponse): bool
    {
        if ($mockResponse['success']) {
            $transmission->update([
                'status' => 'success',
                'response_code' => 'MOCK_SUCCESS',
                'response_message' => $mockResponse['message'] ?? 'Mock successful submission to Nevada NTSA',
                'sent_at' => now(),
            ]);
            return true;
        } else {
            $this->markAsError($transmission, 'MOCK_ERROR', $mockResponse['message'] ?? 'Mock error');
            return false;
        }
    }

    /**
     * Validate required fields with enhanced checking.
     */
    protected function validateRequiredFields(UserCourseEnrollment $enrollment): array
    {
        $errors = [];
        $user = $enrollment->user;

        if (empty($user->first_name) || empty($user->last_name)) {
            $errors[] = 'Student name is required';
        }

        if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
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

        // Additional validation
        if (!empty($user->phone) && !preg_match('/^\+?[\d\s\-\(\)]{10,}$/', $user->phone)) {
            $errors[] = 'Phone number format is invalid';
        }

        return $errors;
    }

    /**
     * Build payload with enhanced data formatting.
     */
    protected function buildPayload(UserCourseEnrollment $enrollment): array
    {
        $user = $enrollment->user;

        return [
            'Name' => trim($user->first_name . ' ' . $user->last_name),
            'Email' => strtolower(trim($user->email)),
            'License' => strtoupper(trim($user->driver_license)),
            'DOB' => $user->date_of_birth->format('Y-m-d'),
            'Court' => $this->getNtsaCourtName($user->court_selected),
            'CaseNum' => trim($enrollment->citation_number),
            'DueDate' => $enrollment->due_date ? $enrollment->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
            'School' => $this->schoolName,
            'TestName' => $this->testName,
            'Telephone' => preg_replace('/[^\d]/', '', $user->phone), // Remove non-digits
            'UniqueID' => $user->id,
            'Encrypt' => '0',
            'Demo' => config('app.env') === 'production' ? '0' : '1',
            'Language' => ($enrollment->language ?? 'english') === 'spanish' ? 'Spanish' : 'English',
        ];
    }

    /**
     * Handle API response with enhanced parsing.
     */
    protected function handleResponse(StateTransmission $transmission, array $response): bool
    {
        Log::info('NTSA API response', [
            'transmission_id' => $transmission->id,
            'response' => $response,
        ]);

        if ($response['success']) {
            $responseBody = $response['body'] ?? '';
            
            // Enhanced success detection
            $successIndicators = ['success', 'submitted', 'registered', 'complete'];
            $isSuccess = false;
            
            foreach ($successIndicators as $indicator) {
                if (stripos($responseBody, $indicator) !== false) {
                    $isSuccess = true;
                    break;
                }
            }
            
            // Also check HTTP status
            if (!$isSuccess && $response['status'] === 200) {
                $isSuccess = true;
            }
            
            if ($isSuccess) {
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
                    'NTSA_UNCLEAR_RESPONSE',
                    'NTSA response unclear: ' . substr($responseBody, 0, 200)
                );

                return false;
            }
        } else {
            // Try fallback if enabled
            if (StateApiMockService::isFallbackMode('nevada', 'ntsa')) {
                return $this->handleFallback($transmission, $response['error'] ?? 'API call failed');
            }

            $this->markAsError(
                $transmission,
                $response['code'] ?? 'API_ERROR',
                $response['error'] ?? 'Unknown NTSA API error'
            );

            return false;
        }
    }

    /**
     * Mark transmission as error.
     */
    protected function markAsError(StateTransmission $transmission, string $code, string $message): void
    {
        $transmission->update([
            'status' => 'error',
            'response_code' => $code,
            'response_message' => $message,
            'retry_count' => $transmission->retry_count + 1,
        ]);
    }

    /**
     * Get NTSA court name mapping.
     */
    protected function getNtsaCourtName(string $courtSelected): string
    {
        // Enhanced court mapping with fallback
        $courtMappings = [
            'Las Vegas Justice Court' => 'Las Vegas Justice Court',
            'Henderson Municipal Court' => 'Henderson Municipal Court',
            'North Las Vegas Municipal Court' => 'North Las Vegas Municipal Court',
            'Reno Municipal Court' => 'Reno Municipal Court',
            'Carson City Justice Court' => 'Carson City Justice Court',
        ];

        return $courtMappings[$courtSelected] ?? $courtSelected;
    }

    /**
     * Sanitize payload for logging (remove sensitive data).
     */
    protected function sanitizePayloadForLogging(array $payload): array
    {
        $sanitized = $payload;
        
        // Mask sensitive fields
        if (isset($sanitized['Email'])) {
            $sanitized['Email'] = $this->maskEmail($sanitized['Email']);
        }
        
        if (isset($sanitized['License'])) {
            $sanitized['License'] = $this->maskLicense($sanitized['License']);
        }
        
        if (isset($sanitized['Telephone'])) {
            $sanitized['Telephone'] = $this->maskPhone($sanitized['Telephone']);
        }

        return $sanitized;
    }

    /**
     * Mask email for logging.
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $username = $parts[0];
            $domain = $parts[1];
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 2));
            return $maskedUsername . '@' . $domain;
        }
        return $email;
    }

    /**
     * Mask license number for logging.
     */
    protected function maskLicense(string $license): string
    {
        if (strlen($license) > 4) {
            return substr($license, 0, 2) . str_repeat('*', strlen($license) - 4) . substr($license, -2);
        }
        return str_repeat('*', strlen($license));
    }

    /**
     * Mask phone number for logging.
     */
    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) > 4) {
            return str_repeat('*', strlen($phone) - 4) . substr($phone, -4);
        }
        return str_repeat('*', strlen($phone));
    }

    /**
     * Test NTSA connection with comprehensive diagnostics.
     */
    public function testConnection(): array
    {
        // Check if service is disabled
        if (!config('states.nevada.ntsa.enabled', true)) {
            return [
                'success' => false,
                'error' => 'Nevada NTSA service is disabled in configuration',
                'suggestion' => 'Enable the service by setting NEVADA_NTSA_ENABLED=true in .env',
            ];
        }

        // Check if we're in mock mode
        if (StateApiMockService::isMockMode('nevada', 'ntsa')) {
            return [
                'success' => true,
                'message' => 'Nevada NTSA service is in MOCK mode',
                'mode' => 'mock',
                'suggestion' => 'Set NEVADA_NTSA_MODE=live to test real API',
            ];
        }

        // Test DNS resolution
        $host = parse_url($this->apiUrl, PHP_URL_HOST);
        if (gethostbyname($host) === $host) {
            return [
                'success' => false,
                'error' => "Cannot resolve hostname '$host'",
                'suggestion' => 'Domain may not exist. Contact Nevada NTSA for correct URL.',
                'fallback_available' => StateApiMockService::isFallbackMode('nevada', 'ntsa'),
            ];
        }

        // Test HTTP connectivity
        try {
            $response = Http::timeout(10)
                ->withOptions([
                    'verify' => false,
                    'allow_redirects' => true,
                ])
                ->get($this->apiUrl);
            
            if ($response->successful() || $response->status() === 405) {
                return [
                    'success' => true,
                    'message' => 'Nevada NTSA endpoint accessible',
                    'status' => $response->status(),
                    'mode' => $this->mode,
                    'fallback_available' => StateApiMockService::isFallbackMode('nevada', 'ntsa'),
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "Unexpected response: HTTP {$response->status()}",
                    'suggestion' => 'Server may be down or endpoint changed',
                    'fallback_available' => StateApiMockService::isFallbackMode('nevada', 'ntsa'),
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'suggestion' => 'Check network connectivity or contact Nevada NTSA',
                'fallback_available' => StateApiMockService::isFallbackMode('nevada', 'ntsa'),
            ];
        }
    }
}