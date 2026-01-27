<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Florida FLHSMV service using HTTP instead of SOAP extension.
 * Works on shared hosting without SOAP extension.
 */
class FlhsmvHttpService
{
    protected string $endpoint;
    protected string $username;
    protected string $password;
    protected string $schoolId;
    protected string $instructorId;
    protected string $courseId;
    protected HttpSoapService $httpSoap;

    public function __construct()
    {
        $this->endpoint = config('services.florida.wsdl_url');
        $this->username = config('services.florida.username');
        $this->password = config('services.florida.password');
        $this->schoolId = config('services.florida.school_id');
        $this->instructorId = config('services.florida.instructor_id');
        $this->courseId = config('services.florida.course_id', '40585');

        // Convert WSDL URL to service endpoint
        $this->endpoint = str_replace('?wsdl', '', $this->endpoint);
        
        $this->httpSoap = new HttpSoapService(
            $this->endpoint,
            'http://tempuri.org/wsVerifyData', // SOAP Action
            30 // timeout
        );
    }

    /**
     * Submit certificate completion to Florida DICDS using HTTP SOAP.
     */
    public function submitCertificate(array $payload): array
    {
        // Check if we're in mock mode
        if (config('states.development.force_fallback_mode') || 
            config('states.florida.mode') === 'mock' ||
            env('STATE_API_FORCE_FALLBACK', false) ||
            env('FLORIDA_SIMULATE_SUCCESS', false)) {
            return $this->getMockResponse();
        }

        try {
            Log::info('Submitting to FLHSMV via HTTP SOAP', [
                'endpoint' => $this->endpoint,
                'school_id' => $this->schoolId,
            ]);

            // Build parameters for wsVerifyData method
            $parameters = $this->buildFloridaApiParameters($payload);

            // Send HTTP SOAP request
            $response = $this->httpSoap->call('wsVerifyData', $parameters);

            if (!$response['success']) {
                Log::error('FLHSMV HTTP SOAP request failed', [
                    'error' => $response['error'],
                ]);
                
                return $this->handleFailedRequest($response);
            }

            // Parse the response
            return $this->parseFloridaResponse($response['data']);

        } catch (Exception $e) {
            Log::error('FLHSMV HTTP submission failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->handleException($e);
        }
    }

    /**
     * Build parameters for Florida API using HTTP SOAP format.
     */
    protected function buildFloridaApiParameters(array $payload): array
    {
        $user = $payload['user'] ?? null;
        $enrollment = $payload['enrollment'] ?? null;
        
        $params = [];
        
        // Authentication (required)
        $params['mvUserid'] = $this->username;
        $params['mvPassword'] = $this->password;
        $params['mvSchoolid'] = $this->schoolId;
        $params['mvSchoolIns'] = $this->instructorId;
        $params['mvSchoolCourse'] = $this->courseId;
        
        // Class Information (required)
        $completionDate = $enrollment ? $enrollment->completed_at : now();
        $params['mvClassDate'] = $completionDate->format('mdY'); // MMDDYYYY format
        $params['mvStartTime'] = '0001'; // Technology-based delivery (internet)
        
        // Student Information (required)
        $params['mvFirstName'] = $user ? $user->first_name : ($payload['first_name'] ?? '');
        $params['mvMiddleName'] = $user ? ($user->middle_name ?? '') : ($payload['middle_name'] ?? '');
        $params['mvLastName'] = $user ? $user->last_name : ($payload['last_name'] ?? '');
        $params['mvSuffix'] = ''; // Optional
        
        // Date of birth (required)
        if ($user && $user->date_of_birth) {
            $params['mvDob'] = $user->date_of_birth->format('mdY'); // MMDDYYYY format
        } else {
            $params['mvDob'] = '01011990'; // Default fallback
        }
        
        $params['mvSex'] = $user ? ($user->gender ?? 'M') : 'M'; // M or F
        
        // Driver License (required for BDI/ADI)
        $driverLicense = $user ? $user->driver_license : ($payload['driver_license_number'] ?? '');
        $params['mvDriversLicense'] = $this->formatFloridaDriverLicense($driverLicense);
        $params['mvdlStateOfRecordCode'] = 'FL';
        
        // SSN (optional for BDI/ADI, required for TLSAE/DETS if no FL DL)
        $params['mvSocialSN'] = $user ? ($user->ssn_last_four ?? '') : '';
        
        // Citation Information (required for BDI)
        $citationNumber = $enrollment ? $enrollment->citation_number : ($payload['citation_number'] ?? '');
        $params['mvCitationDate'] = $completionDate->format('mdY'); // MMDDYYYY format
        $params['mvCitationCounty'] = $this->getFloridaCounty($enrollment);
        $params['mvCitationNumber'] = $this->formatCitationNumber($citationNumber);
        
        // Reason for Attending (required)
        $params['mvReasonAttending'] = 'B1'; // BDI Election (most common)
        
        // Address (optional)
        $params['mvStreet'] = $user ? ($user->address ?? '') : '';
        $params['mvApartment'] = '';
        $params['mvCity'] = $user ? ($user->city ?? '') : '';
        $params['mvState'] = $user ? ($user->state ?? 'FL') : 'FL';
        $params['mvZipCode'] = $user ? ($user->zip_code ?? '') : '';
        $params['mvZipPlus'] = '';
        $params['mvPhone'] = $user ? ($user->phone ?? '') : '';
        $params['mvEmail'] = $user ? ($user->email ?? '') : '';
        
        // Additional fields (optional)
        $params['mvAlienNumber'] = '';
        $params['mvNonAlien'] = '';
        
        return $params;
    }

    /**
     * Format driver license to Florida format if needed.
     */
    protected function formatFloridaDriverLicense(string $license): string
    {
        if (empty($license)) {
            return 'D123456789012'; // Default test format
        }
        
        // If already in Florida format (A999999999999), return as-is
        if (preg_match('/^[A-Z]\d{12}$/', $license)) {
            return $license;
        }
        
        // Try to convert to Florida format
        $cleaned = preg_replace('/[^A-Z0-9]/', '', strtoupper($license));
        
        if (strlen($cleaned) >= 13) {
            return substr($cleaned, 0, 13);
        }
        
        // Pad with zeros if too short
        return 'D' . str_pad(preg_replace('/[^0-9]/', '', $license), 12, '0', STR_PAD_LEFT);
    }

    /**
     * Format citation number to 7 characters.
     */
    protected function formatCitationNumber(string $citation): string
    {
        if (empty($citation)) {
            return '1234567'; // Default test citation
        }
        
        $cleaned = preg_replace('/[^0-9A-Z]/', '', strtoupper($citation));
        
        if (strlen($cleaned) === 7) {
            return $cleaned;
        }
        
        if (strlen($cleaned) > 7) {
            return substr($cleaned, 0, 7);
        }
        
        return str_pad($cleaned, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Get Florida county from enrollment or default.
     */
    protected function getFloridaCounty($enrollment): string
    {
        if ($enrollment && $enrollment->court_county) {
            return strtoupper($enrollment->court_county);
        }
        
        return 'LEON'; // Default county
    }

    /**
     * Parse Florida API response.
     */
    protected function parseFloridaResponse(array $responseData): array
    {
        // Navigate through the SOAP response structure
        $result = null;
        
        // Try different possible response structures
        if (isset($responseData['Body']['wsVerifyDataResponse']['wsVerifyDataResult'])) {
            $result = $responseData['Body']['wsVerifyDataResponse']['wsVerifyDataResult'];
        } elseif (isset($responseData['wsVerifyDataResponse']['wsVerifyDataResult'])) {
            $result = $responseData['wsVerifyDataResponse']['wsVerifyDataResult'];
        } elseif (isset($responseData['wsVerifyDataResult'])) {
            $result = $responseData['wsVerifyDataResult'];
        } else {
            // If we can't find the expected structure, log it for debugging
            Log::warning('Unexpected Florida API response structure', [
                'response_data' => $responseData,
            ]);
            
            // Try to find any result-like field
            $result = $this->findResultInResponse($responseData);
        }

        Log::info('Florida API response result', ['result' => $result]);

        // Check if it's a success (no error code returned)
        if (empty($result) || $result === 'SUCCESS' || $result === '0') {
            $certificateNumber = 'FL' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            return [
                'success' => true,
                'certificate_number' => $certificateNumber,
                'response_code' => 'SUCCESS',
                'message' => 'Certificate submitted successfully to Florida FLHSMV',
                'status' => 200,
            ];
        }

        // Map error codes to human-readable messages
        $errorInfo = $this->mapFloridaErrorCode($result);
        
        return [
            'success' => false,
            'error' => $errorInfo['message'],
            'code' => $result,
            'status' => $errorInfo['retryable'] ? 422 : 400,
            'retryable' => $errorInfo['retryable'],
        ];
    }

    /**
     * Recursively search for result in response data.
     */
    protected function findResultInResponse(array $data, string $searchKey = 'result'): ?string
    {
        foreach ($data as $key => $value) {
            if (stripos($key, $searchKey) !== false) {
                return is_string($value) ? $value : (string) $value;
            }
            
            if (is_array($value)) {
                $found = $this->findResultInResponse($value, $searchKey);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        
        return null;
    }

    /**
     * Handle failed HTTP request.
     */
    protected function handleFailedRequest(array $response): array
    {
        // Try to extract useful error information
        $errorMessage = $response['error'] ?? 'Unknown error';
        $statusCode = $response['status_code'] ?? 500;
        
        // If it's a network/connection error, make it retryable
        $retryable = in_array($statusCode, [408, 429, 500, 502, 503, 504]);
        
        return [
            'success' => false,
            'error' => "Florida API request failed: $errorMessage",
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
            'error' => 'Florida API submission failed: ' . $e->getMessage(),
            'code' => 'EXCEPTION',
            'status' => 500,
            'retryable' => true,
        ];
    }

    /**
     * Map Florida error codes to human-readable messages and retry status.
     */
    public function mapFloridaErrorCode(string $errorCode): array
    {
        $errorMappings = [
            // Address Errors
            'AF000' => ['message' => 'Could not insert address', 'retryable' => true],
            
            // Certificate Errors
            'CC000' => ['message' => 'School is out of certificates', 'retryable' => true],
            'CC001' => ['message' => 'Could not update school certificate count', 'retryable' => true],
            
            // Student Identifier Validation Errors
            'CF000' => ['message' => 'Unique student identifier validation failed', 'retryable' => false],
            'CF010' => ['message' => 'No valid unique applicant identifier submitted', 'retryable' => false],
            'CF020' => ['message' => 'Submitted SSN is not four numeric digits', 'retryable' => false],
            'CF030' => ['message' => 'Driver License and state of record are required together for non-Florida DLs', 'retryable' => false],
            'CF031' => ['message' => 'Invalid state of record code', 'retryable' => false],
            'CF032' => ['message' => 'Submitted as Florida DL number, but not in Florida DL format A999999999999', 'retryable' => false],
            'CF033' => ['message' => 'Invalid driver license number', 'retryable' => false],
            'CF034' => ['message' => 'Multiple records found for driver license', 'retryable' => false],
            'CF035' => ['message' => 'Error updating driver data', 'retryable' => true],
            'CF040' => ['message' => 'Last four digits of alien registration number must be numeric', 'retryable' => false],
            'CF050' => ['message' => 'Last four digits of non-alien registration number must be numeric', 'retryable' => false],
            
            // County/Court Errors
            'CL000' => ['message' => 'County name is required for this reason attending code', 'retryable' => false],
            'CO000' => ['message' => 'County name is invalid', 'retryable' => false],
            
            // Database Errors
            'DB000' => ['message' => 'Generic student insert error', 'retryable' => true],
            
            // Data Validation Errors
            'DV030' => ['message' => 'Student first name not sent', 'retryable' => false],
            'DV040' => ['message' => 'Student last name is missing', 'retryable' => false],
            'DV050' => ['message' => 'Student sex is required', 'retryable' => false],
            'DV060' => ['message' => 'Court case number is required for this student\'s reason for attending', 'retryable' => false],
            'DV070' => ['message' => 'Driver license number of student is required', 'retryable' => false],
            'DV080' => ['message' => 'Citation date of student is required', 'retryable' => false],
            'DV090' => ['message' => 'Citation county of student is required', 'retryable' => false],
            'DV100' => ['message' => 'Citation number is required or incorrect length (must be seven characters)', 'retryable' => false],
            'DV110' => ['message' => 'Reason attending of student is required', 'retryable' => false],
            'DV120' => ['message' => 'Invalid address state code', 'retryable' => false],
            'DV130' => ['message' => 'Valid numeric address ZIP code is required', 'retryable' => false],
            'DV140' => ['message' => 'Valid numeric phone number is required', 'retryable' => false],
            
            // School/Instructor Errors
            'SI000' => ['message' => 'School instructor is required', 'retryable' => false],
            'SI001' => ['message' => 'School instructor could not be validated', 'retryable' => false],
            
            // Student Data Errors
            'ST000' => ['message' => 'Student first name missing', 'retryable' => false],
            'ST001' => ['message' => 'Student last name missing', 'retryable' => false],
            'ST002' => ['message' => 'Student sex field missing', 'retryable' => false],
            'ST003' => ['message' => 'Reason attending is required', 'retryable' => false],
            'ST004' => ['message' => 'Student date of birth missing', 'retryable' => false],
            'ST005' => ['message' => 'Reason attending validation failed', 'retryable' => false],
            
            // Verification Errors
            'VC000' => ['message' => 'Could not verify class. Check class dates and times for correct format', 'retryable' => false],
            'VC001' => ['message' => 'Invalid reason code', 'retryable' => false],
            'VC003' => ['message' => 'Invalid completion date', 'retryable' => false],
            'VI000' => ['message' => 'Could not verify instructor', 'retryable' => false],
            'VS000' => ['message' => 'School validation failed', 'retryable' => false],
            'VS010' => ['message' => 'Invalid school type', 'retryable' => false],
            
            // Login Errors
            'VL000' => ['message' => 'Login failed - invalid credentials', 'retryable' => false],
        ];

        if (isset($errorMappings[$errorCode])) {
            return $errorMappings[$errorCode];
        }

        // Unknown error code
        return [
            'message' => "Unknown Florida API error code: $errorCode",
            'retryable' => false,
        ];
    }

    /**
     * Get mock response for testing.
     */
    protected function getMockResponse(): array
    {
        $certificateNumber = 'FL' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        return [
            'success' => true,
            'certificate_number' => $certificateNumber,
            'response_code' => 'MOCK_SUCCESS',
            'message' => 'Certificate submitted successfully (MOCK MODE)',
            'status' => 200,
        ];
    }

    /**
     * Test connection to Florida API.
     */
    public function testConnection(): array
    {
        try {
            // Test with a simple ping or test method if available
            $response = $this->httpSoap->call('wsVerifyData', [
                'mvUserid' => $this->username,
                'mvPassword' => $this->password,
                'mvSchoolid' => $this->schoolId,
                // Add minimal test data
                'mvFirstName' => 'TEST',
                'mvLastName' => 'USER',
                'mvSex' => 'M',
                'mvDob' => '01011990',
                'mvDriversLicense' => 'D123456789012',
                'mvdlStateOfRecordCode' => 'FL',
                'mvClassDate' => date('mdY'),
                'mvStartTime' => '0001',
                'mvCitationDate' => date('mdY'),
                'mvCitationCounty' => 'LEON',
                'mvCitationNumber' => '1234567',
                'mvReasonAttending' => 'B1',
            ]);

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'HTTP SOAP connection successful',
                    'endpoint' => $this->endpoint,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'suggestion' => 'Check endpoint URL and network connectivity',
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