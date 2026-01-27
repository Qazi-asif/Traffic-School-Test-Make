<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * California TVCC service using HTTP instead of SOAP extension.
 * Works on shared hosting without SOAP extension.
 */
class CaliforniaTvccHttpService
{
    protected string $endpoint;
    protected string $username;
    protected string $password;
    protected HttpSoapService $httpSoap;

    public function __construct()
    {
        $this->endpoint = config('state-integrations.california.tvcc.url', 'https://xsg.dmv.ca.gov/tvcc/tvccservice');
        $this->username = config('state-integrations.california.tvcc.user', 'Support@dummiestrafficschool.com');
        $this->password = $this->getTvccPassword();
        
        $this->httpSoap = new HttpSoapService(
            $this->endpoint,
            'http://tempuri.org/submitCertificate', // SOAP Action for TVCC
            30 // timeout
        );
    }

    /**
     * Submit certificate completion to California TVCC using HTTP SOAP.
     */
    public function submitCertificate(array $certificateData): array
    {
        // Check if we're in mock mode
        if (config('states.development.force_fallback_mode') || 
            config('states.california.mode') === 'mock' ||
            env('STATE_API_FORCE_FALLBACK', false)) {
            return $this->getMockResponse();
        }

        try {
            Log::info('Submitting to California TVCC via HTTP SOAP', [
                'endpoint' => $this->endpoint,
                'username' => $this->username,
            ]);

            // Build parameters for TVCC API
            $parameters = $this->buildTvccApiParameters($certificateData);

            // Send HTTP SOAP request
            $response = $this->httpSoap->call('submitCertificate', $parameters);

            if (!$response['success']) {
                Log::error('California TVCC HTTP SOAP request failed', [
                    'error' => $response['error'],
                ]);
                
                return $this->handleFailedRequest($response);
            }

            // Parse the response
            return $this->parseTvccResponse($response['data']);

        } catch (Exception $e) {
            Log::error('California TVCC HTTP submission failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->handleException($e);
        }
    }

    /**
     * Build TVCC API parameters.
     */
    protected function buildTvccApiParameters(array $certificate): array
    {
        $user = $certificate['user'] ?? null;
        $enrollment = $certificate['enrollment'] ?? null;
        
        $params = [];
        
        // Authentication
        $params['userDto'] = [
            'userId' => $this->username,
            'password' => $this->password,
        ];
        
        // Certificate data
        $completionDate = $enrollment ? $enrollment->completed_at : now();
        
        $params['ccDate'] = $completionDate->format('Y-m-d'); // yyyy-MM-dd format
        $params['courtCd'] = $this->getCourtCode($enrollment);
        $params['dateOfBirth'] = $user && $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '1990-01-01';
        $params['dlNbr'] = $user ? ($user->driver_license ?? '') : '';
        $params['firstName'] = $user ? $user->first_name : ($certificate['first_name'] ?? '');
        $params['lastName'] = $user ? $user->last_name : ($certificate['last_name'] ?? '');
        $params['modality'] = '4T'; // Fixed value for online courses
        $params['refNbr'] = $enrollment ? $enrollment->citation_number : ($certificate['citation_number'] ?? '');
        
        return $params;
    }

    /**
     * Get court code from enrollment.
     */
    protected function getCourtCode($enrollment): string
    {
        if ($enrollment && $enrollment->court) {
            return $enrollment->court->tvcc_court_code ?? 'DEFAULT';
        }
        
        return 'DEFAULT'; // Default court code
    }

    /**
     * Parse TVCC response.
     */
    protected function parseTvccResponse(array $responseData): array
    {
        // Navigate through the SOAP response structure
        $result = null;
        
        // Try different possible response structures
        if (isset($responseData['Body']['submitCertificateResponse']['submitCertificateResult'])) {
            $result = $responseData['Body']['submitCertificateResponse']['submitCertificateResult'];
        } elseif (isset($responseData['submitCertificateResponse']['submitCertificateResult'])) {
            $result = $responseData['submitCertificateResponse']['submitCertificateResult'];
        } elseif (isset($responseData['submitCertificateResult'])) {
            $result = $responseData['submitCertificateResult'];
        }

        if (!$result) {
            Log::warning('Unexpected California TVCC response structure', [
                'response_data' => $responseData,
            ]);
            
            return [
                'success' => false,
                'error' => 'Unexpected response format from California TVCC',
                'code' => 'INVALID_RESPONSE',
                'status' => 500,
            ];
        }

        // Extract response fields
        $ccSeqNbr = $result['ccSeqNbr'] ?? null;
        $ccStatCd = $result['ccStatCd'] ?? null;
        $ccSubTstamp = $result['ccSubTstamp'] ?? null;

        Log::info('California TVCC response', [
            'ccSeqNbr' => $ccSeqNbr,
            'ccStatCd' => $ccStatCd,
            'ccSubTstamp' => $ccSubTstamp,
        ]);

        // Check if submission was successful
        if ($ccStatCd === 'SUCCESS' || $ccStatCd === '0' || !empty($ccSeqNbr)) {
            return [
                'success' => true,
                'certificate_number' => $ccSeqNbr ?: 'CA' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'response_code' => $ccStatCd ?: 'SUCCESS',
                'message' => 'Certificate submitted successfully to California TVCC',
                'submission_timestamp' => $ccSubTstamp,
                'status' => 200,
            ];
        }

        // Handle error response
        $errorMessage = $this->mapTvccErrorCode($ccStatCd);
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'code' => $ccStatCd,
            'status' => 400,
            'retryable' => $this->isTvccErrorRetryable($ccStatCd),
        ];
    }

    /**
     * Map TVCC error codes to human-readable messages.
     */
    protected function mapTvccErrorCode(?string $errorCode): string
    {
        $errorMappings = [
            'INVALID_USER' => 'Invalid user credentials',
            'INVALID_COURT' => 'Invalid court code',
            'INVALID_DL' => 'Invalid driver license number',
            'DUPLICATE_SUBMISSION' => 'Certificate already submitted for this citation',
            'INVALID_DATE' => 'Invalid completion or birth date',
            'MISSING_REQUIRED_FIELD' => 'Required field is missing',
            'SYSTEM_ERROR' => 'California TVCC system error',
        ];

        return $errorMappings[$errorCode] ?? "California TVCC error: $errorCode";
    }

    /**
     * Check if TVCC error is retryable.
     */
    protected function isTvccErrorRetryable(?string $errorCode): bool
    {
        $retryableErrors = ['SYSTEM_ERROR', 'TIMEOUT', 'CONNECTION_ERROR'];
        return in_array($errorCode, $retryableErrors);
    }

    /**
     * Get TVCC password from database.
     */
    protected function getTvccPassword(): string
    {
        try {
            // Try to get password from tvcc_passwords table
            $passwordRecord = \DB::table('tvcc_passwords')
                ->orderBy('updated_at', 'desc')
                ->first();
            
            if ($passwordRecord) {
                return $passwordRecord->password;
            }
        } catch (Exception $e) {
            Log::warning('Could not retrieve TVCC password from database', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback to environment variable
        return env('CALIFORNIA_TVCC_PASSWORD', 'default_password');
    }

    /**
     * Handle failed HTTP request.
     */
    protected function handleFailedRequest(array $response): array
    {
        $errorMessage = $response['error'] ?? 'Unknown error';
        $statusCode = $response['status_code'] ?? 500;
        
        // If it's a network/connection error, make it retryable
        $retryable = in_array($statusCode, [408, 429, 500, 502, 503, 504]);
        
        return [
            'success' => false,
            'error' => "California TVCC request failed: $errorMessage",
            'code' => 'HTTP_ERROR',
            'status' => $statusCode,
            'retryable' => $retryable,
        ];
    }

    /**
     * Handle exceptions.
     */
    protected function handleException(Exception $e): array
    {
        return [
            'success' => false,
            'error' => 'California TVCC submission failed: ' . $e->getMessage(),
            'code' => 'EXCEPTION',
            'status' => 500,
            'retryable' => true,
        ];
    }

    /**
     * Get mock response for testing.
     */
    protected function getMockResponse(): array
    {
        $certificateNumber = 'CA' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        return [
            'success' => true,
            'certificate_number' => $certificateNumber,
            'response_code' => 'MOCK_SUCCESS',
            'message' => 'Certificate submitted successfully to California TVCC (MOCK MODE)',
            'submission_timestamp' => now()->toISOString(),
            'status' => 200,
        ];
    }

    /**
     * Test connection to California TVCC.
     */
    public function testConnection(): array
    {
        try {
            // Test with minimal data
            $response = $this->httpSoap->call('submitCertificate', [
                'userDto' => [
                    'userId' => $this->username,
                    'password' => $this->password,
                ],
                'ccDate' => date('Y-m-d'),
                'courtCd' => 'TEST',
                'dateOfBirth' => '1990-01-01',
                'dlNbr' => 'TEST123',
                'firstName' => 'TEST',
                'lastName' => 'USER',
                'modality' => '4T',
                'refNbr' => 'TEST123',
            ]);

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'California TVCC HTTP SOAP connection successful',
                    'endpoint' => $this->endpoint,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'suggestion' => 'Check endpoint URL and credentials',
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'suggestion' => 'Check endpoint configuration and network connectivity',
            ];
        }
    }
}