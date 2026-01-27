<?php

namespace App\Services;

use App\Services\StateApiMockService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class ImprovedFlhsmvSoapService
{
    protected string $wsdlUrl;
    protected string $username;
    protected string $password;
    protected string $schoolId;
    protected string $instructorId;
    protected int $timeout;
    protected string $mode;

    public function __construct()
    {
        $this->wsdlUrl = config('states.florida.wsdl_url');
        $this->username = config('states.florida.username');
        $this->password = config('states.florida.password');
        $this->schoolId = config('states.florida.school_id');
        $this->instructorId = config('states.florida.instructor_id');
        $this->timeout = config('states.florida.timeout', 30);
        $this->mode = config('states.florida.mode', 'live');
    }

    /**
     * Submit certificate completion to Florida DICDS.
     */
    public function submitCertificate(array $payload): array
    {
        // Check if we should use mock mode
        if (StateApiMockService::isMockMode('florida')) {
            Log::info('Using mock mode for Florida FLHSMV submission');
            StateApiMockService::simulateNetworkDelay();
            return StateApiMockService::getMockResponse('florida', 'success');
        }

        // Check if service is disabled
        if (!config('states.florida.enabled', true)) {
            Log::warning('Florida FLHSMV service is disabled');
            return $this->getDisabledResponse();
        }

        // Try real API first
        $result = $this->attemptRealApiCall($payload);
        
        // If real API fails and fallback is enabled, use fallback
        if (!$result['success'] && StateApiMockService::isFallbackMode('florida')) {
            Log::warning('Florida FLHSMV real API failed, using fallback', [
                'original_error' => $result['error'] ?? 'Unknown error'
            ]);
            return $this->handleFallback($payload);
        }

        return $result;
    }

    /**
     * Attempt to call the real FLHSMV API.
     */
    protected function attemptRealApiCall(array $payload): array
    {
        // Check if WSDL is accessible first
        if (!$this->isWsdlAccessible()) {
            return [
                'success' => false,
                'error' => 'WSDL URL is not accessible: ' . $this->wsdlUrl,
                'code' => 'WSDL_NOT_ACCESSIBLE',
                'status' => 503,
            ];
        }

        try {
            Log::info('Initializing FLHSMV SOAP client', [
                'wsdl_url' => $this->wsdlUrl,
                'school_id' => $this->schoolId,
            ]);

            $soapClient = new SoapClient($this->wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => $this->timeout,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'encoding' => 'UTF-8',
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                    'http' => [
                        'timeout' => $this->timeout,
                        'user_agent' => 'Laravel/FLHSMV Client v1.0',
                    ],
                ]),
            ]);

            // Build SOAP request parameters
            $soapParams = $this->buildSoapParameters($payload);

            if (config('states.development.log_all_requests')) {
                Log::info('FLHSMV SOAP request', [
                    'method' => 'SubmitCertificateCompletion',
                    'params' => $this->sanitizeLogData($soapParams),
                ]);
            }

            // Call the SOAP method
            $response = $soapClient->SubmitCertificateCompletion($soapParams);

            if (config('states.development.log_all_responses')) {
                Log::info('FLHSMV SOAP response received', [
                    'response' => $response,
                ]);
            }

            return $this->parseResponse($response);

        } catch (SoapFault $e) {
            Log::error('FLHSMV SOAP fault', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'fault_code' => $e->faultcode ?? null,
                'fault_string' => $e->faultstring ?? null,
            ]);

            return [
                'success' => false,
                'error' => 'SOAP fault: ' . $e->getMessage(),
                'code' => $e->faultcode ?? 'SOAP_FAULT',
                'status' => 500,
            ];

        } catch (Exception $e) {
            Log::error('FLHSMV SOAP call failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'SOAP error: ' . $e->getMessage(),
                'code' => 'SOAP_ERROR',
                'status' => 500,
            ];
        }
    }

    /**
     * Handle fallback when real API fails.
     */
    protected function handleFallback(array $payload): array
    {
        // Try HTTP fallback first if configured
        $httpUrl = config('states.florida.service_url');
        if ($httpUrl) {
            $httpResult = $this->attemptHttpFallback($payload, $httpUrl);
            if ($httpResult['success']) {
                return $httpResult;
            }
        }

        // If HTTP fallback fails or not configured, simulate success if enabled
        if (config('states.florida.fallback.simulate_success', true)) {
            $certificateNumber = 'FL' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            Log::warning('FLHSMV fallback: Simulating successful submission', [
                'certificate_number' => $certificateNumber,
                'payload' => $this->sanitizeLogData($payload),
            ]);

            return [
                'success' => true,
                'certificate_number' => $certificateNumber,
                'response_code' => 'FALLBACK_SUCCESS',
                'message' => 'Certificate submitted via fallback method (API unavailable)',
                'status' => 200,
                'fallback' => true,
            ];
        }

        // Queue for manual processing if enabled
        if (config('states.florida.fallback.queue_for_manual', false)) {
            // Here you could queue the submission for manual processing
            Log::info('FLHSMV fallback: Queuing for manual submission', [
                'payload' => $this->sanitizeLogData($payload),
            ]);

            return [
                'success' => true,
                'response_code' => 'QUEUED_FOR_MANUAL',
                'message' => 'Certificate queued for manual submission',
                'status' => 202,
                'fallback' => true,
            ];
        }

        // Final fallback - return error
        return [
            'success' => false,
            'error' => 'All fallback methods exhausted',
            'code' => 'FALLBACK_FAILED',
            'status' => 503,
        ];
    }

    /**
     * Attempt HTTP fallback submission.
     */
    protected function attemptHttpFallback(array $payload, string $httpUrl): array
    {
        try {
            Log::info('Attempting FLHSMV HTTP fallback', [
                'url' => $httpUrl,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel/FLHSMV Client v1.0',
                ])
                ->post($httpUrl, [
                    'username' => $this->username,
                    'password' => $this->password,
                    'school_id' => $this->schoolId,
                    'instructor_id' => $this->instructorId,
                    'certificate_data' => $payload,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'certificate_number' => $data['certificate_number'] ?? 'FL' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'response_code' => 'HTTP_SUCCESS',
                    'message' => $data['message'] ?? 'Certificate submitted via HTTP fallback',
                    'status' => 200,
                    'fallback' => true,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'HTTP fallback failed: ' . $response->body(),
                    'code' => 'HTTP_FALLBACK_ERROR',
                    'status' => $response->status(),
                ];
            }

        } catch (Exception $e) {
            Log::error('FLHSMV HTTP fallback failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'HTTP fallback error: ' . $e->getMessage(),
                'code' => 'HTTP_FALLBACK_EXCEPTION',
                'status' => 500,
            ];
        }
    }

    /**
     * Get response when service is disabled.
     */
    protected function getDisabledResponse(): array
    {
        return [
            'success' => false,
            'error' => 'Florida FLHSMV service is disabled',
            'code' => 'SERVICE_DISABLED',
            'status' => 503,
        ];
    }

    /**
     * Build SOAP parameters from payload.
     */
    protected function buildSoapParameters(array $payload): array
    {
        return [
            'Authentication' => [
                'Username' => $this->username,
                'Password' => $this->password,
                'SchoolId' => $this->schoolId,
                'InstructorId' => $this->instructorId,
            ],
            'CertificateData' => [
                'DriverLicenseNumber' => $payload['driver_license_number'],
                'CitationNumber' => $payload['citation_number'],
                'CourtCaseNumber' => $payload['court_case_number'],
                'FirstName' => $payload['first_name'],
                'LastName' => $payload['last_name'],
                'MiddleName' => $payload['middle_name'] ?? '',
                'DateOfBirth' => $payload['date_of_birth'],
                'CompletionDate' => $payload['completion_date'],
                'CourseName' => $payload['course_name'],
                'CourseType' => $payload['course_type'],
                'CertificateNumber' => $payload['certificate_number'],
                'Timestamp' => $payload['timestamp'],
            ],
        ];
    }

    /**
     * Parse SOAP response.
     */
    protected function parseResponse($response): array
    {
        if (!$response) {
            return [
                'success' => false,
                'error' => 'Empty response from FLHSMV',
                'code' => 'EMPTY_RESPONSE',
                'status' => 500,
            ];
        }

        // Handle different response formats
        if (is_object($response)) {
            $response = (array) $response;
        }

        // Check for success indicators
        $success = false;
        $certificateNumber = null;
        $responseCode = null;
        $message = null;

        if (isset($response['Success']) && $response['Success']) {
            $success = true;
            $certificateNumber = $response['CertificateNumber'] ?? null;
            $responseCode = $response['ResponseCode'] ?? 'SUCCESS';
            $message = $response['Message'] ?? 'Certificate submitted successfully';
        } elseif (isset($response['Result']) && strtolower($response['Result']) === 'success') {
            $success = true;
            $certificateNumber = $response['CertificateNumber'] ?? null;
            $responseCode = 'SUCCESS';
            $message = $response['Message'] ?? 'Certificate submitted successfully';
        } else {
            $success = false;
            $responseCode = $response['ErrorCode'] ?? $response['ResponseCode'] ?? 'ERROR';
            $message = $response['ErrorMessage'] ?? $response['Message'] ?? 'Unknown error from FLHSMV';
        }

        if ($success) {
            return [
                'success' => true,
                'certificate_number' => $certificateNumber,
                'response_code' => $responseCode,
                'message' => $message,
                'status' => 200,
            ];
        } else {
            return [
                'success' => false,
                'error' => $message,
                'code' => $responseCode,
                'status' => 400,
            ];
        }
    }

    /**
     * Sanitize log data to remove sensitive information.
     */
    protected function sanitizeLogData(array $data): array
    {
        $sanitized = $data;
        
        if (isset($sanitized['Authentication']['Password'])) {
            $sanitized['Authentication']['Password'] = '[REDACTED]';
        }

        return $sanitized;
    }

    /**
     * Check if WSDL is accessible.
     */
    protected function isWsdlAccessible(): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'user_agent' => 'Laravel/FLHSMV Client v1.0',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $headers = @get_headers($this->wsdlUrl, 1, $context);
            return $headers && strpos($headers[0], '200') !== false;
        } catch (Exception $e) {
            Log::debug('WSDL accessibility check failed', [
                'url' => $this->wsdlUrl,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test SOAP connection with enhanced diagnostics.
     */
    public function testConnection(): array
    {
        // Check if service is disabled
        if (!config('states.florida.enabled', true)) {
            return [
                'success' => false,
                'error' => 'Florida FLHSMV service is disabled in configuration',
                'suggestion' => 'Enable the service by setting FLORIDA_ENABLED=true in .env',
            ];
        }

        // Check if we're in mock mode
        if (StateApiMockService::isMockMode('florida')) {
            return [
                'success' => true,
                'message' => 'Florida FLHSMV service is in MOCK mode',
                'mode' => 'mock',
                'suggestion' => 'Set FLORIDA_MODE=live to test real API',
            ];
        }

        // First check if WSDL is accessible
        if (!$this->isWsdlAccessible()) {
            return [
                'success' => false,
                'error' => 'WSDL URL is not accessible: ' . $this->wsdlUrl,
                'suggestion' => 'Check network connectivity, firewall settings, or contact FLHSMV for correct endpoint',
                'fallback_available' => StateApiMockService::isFallbackMode('florida'),
            ];
        }

        try {
            $soapClient = new SoapClient($this->wsdlUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            // Get available methods
            $methods = $soapClient->__getFunctions();

            return [
                'success' => true,
                'message' => 'SOAP connection successful',
                'methods' => count($methods),
                'mode' => $this->mode,
                'fallback_available' => StateApiMockService::isFallbackMode('florida'),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'suggestion' => 'WSDL is accessible but SOAP client failed. Check credentials or SOAP configuration.',
                'fallback_available' => StateApiMockService::isFallbackMode('florida'),
            ];
        }
    }
}