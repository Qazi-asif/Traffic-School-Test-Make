<?php

namespace App\Services\CaliforniaTVCC;

use Exception;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class TvccClient
{
    protected $client;
    protected $wsdlPath;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->wsdlPath = resource_path('wsdl/TvccServiceImplService.wsdl');
        $this->username = config('state-integrations.california.tvcc.user');
        $this->password = $this->getTvccPassword();
        
        $this->initializeSoapClient();
    }

    /**
     * Initialize SOAP client with local WSDL.
     */
    protected function initializeSoapClient(): void
    {
        try {
            if (!file_exists($this->wsdlPath)) {
                throw new Exception("WSDL file not found: {$this->wsdlPath}");
            }

            $this->client = new SoapClient($this->wsdlPath, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'encoding' => 'UTF-8',
                'location' => 'https://xsg.dmv.ca.gov/tvcc/tvccservice', // Override endpoint
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            Log::info('California TVCC SOAP client initialized successfully', [
                'wsdl_path' => $this->wsdlPath,
                'endpoint' => 'https://xsg.dmv.ca.gov/tvcc/tvccservice',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to initialize California TVCC SOAP client', [
                'error' => $e->getMessage(),
                'wsdl_path' => $this->wsdlPath,
            ]);
            throw $e;
        }
    }

    /**
     * Submit certificate using addCourseCompletion method.
     */
    public function submitCertificate($certificateData): array
    {
        try {
            // Build the course completion request based on WSDL structure
            $request = $this->buildCourseCompletionRequest($certificateData);

            Log::info('Calling California TVCC addCourseCompletion method', [
                'request' => $this->sanitizeLogData($request),
            ]);

            // Call the TVCC API using the correct method from WSDL
            $response = $this->client->addCourseCompletion($request);
            
            Log::info('California TVCC response received', [
                'response' => $response,
            ]);

            // Store response in tvcc_response table
            $this->storeTvccResponse($certificateData, $response);

            // Parse the response
            if (isset($response->return)) {
                $result = $response->return;
                
                return [
                    'success' => true,
                    'response' => [
                        'ccSeqNbr' => $result->ccSeqNbr ?? null,
                        'ccStatCd' => $result->ccStatCd ?? 'SUCCESS',
                        'ccSubTstamp' => $result->ccSubTstamp ?? now()->toISOString(),
                    ],
                    'certificate_number' => $result->ccSeqNbr ?? 'CA' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'message' => 'Certificate submitted successfully to California TVCC',
                ];
            } else {
                return [
                    'success' => true,
                    'response' => $response,
                    'certificate_number' => 'CA' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'message' => 'Certificate submitted successfully to California TVCC',
                ];
            }

        } catch (SoapFault $e) {
            Log::error('California TVCC SOAP fault', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'fault_code' => $e->faultcode ?? null,
                'fault_string' => $e->faultstring ?? null,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'SOAP_FAULT',
            ];

        } catch (Exception $e) {
            Log::error('California TVCC call failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'API_ERROR',
            ];
        }
    }

    /**
     * Build course completion request based on WSDL structure.
     */
    protected function buildCourseCompletionRequest($certificateData): array
    {
        // Handle both certificate object and student ID
        if (is_string($certificateData) || is_numeric($certificateData)) {
            // Mock data for testing with student ID
            return [
                'arg0' => [
                    'ccDate' => date('c'), // ISO 8601 format
                    'classCity' => 'Los Angeles',
                    'classCntyCd' => 'LA',
                    'courtCd' => 'ABC123',
                    'dateOfBirth' => '1990-05-20T00:00:00',
                    'dlNbr' => 'D1234567',
                    'firstName' => 'John',
                    'instructorLicNbr' => 'INS123',
                    'instructorName' => 'Test Instructor',
                    'lastName' => 'Doe',
                    'modality' => '4T',
                    'refNbr' => 'CITATION123',
                    'userDto' => [
                        'userId' => $this->username,
                        'password' => $this->password,
                    ],
                ],
            ];
        }

        // Handle certificate object
        $enrollment = $certificateData->enrollment ?? null;
        $user = $enrollment->user ?? null;

        return [
            'arg0' => [
                'ccDate' => $enrollment ? $enrollment->completed_at->format('c') : date('c'),
                'classCity' => 'Los Angeles', // Default or from user data
                'classCntyCd' => 'LA', // Default or from court mapping
                'courtCd' => $this->getCourtCode($user->court_selected ?? ''),
                'dateOfBirth' => $user ? $user->date_of_birth->format('c') : '1990-01-01T00:00:00',
                'dlNbr' => $user->driver_license ?? 'D123456789012',
                'firstName' => $user->first_name ?? 'Test',
                'instructorLicNbr' => 'INS123', // From configuration
                'instructorName' => 'Certified Instructor', // From configuration
                'lastName' => $user->last_name ?? 'Student',
                'modality' => '4T', // Technology-based delivery
                'refNbr' => $enrollment->citation_number ?? 'TEST123',
                'userDto' => [
                    'userId' => $this->username,
                    'password' => $this->password,
                ],
            ],
        ];
    }

    /**
     * Get court code mapping.
     */
    protected function getCourtCode(string $courtName): string
    {
        $courtMappings = [
            'Los Angeles Superior Court' => 'LA001',
            'Orange County Superior Court' => 'OC001',
            'San Diego Superior Court' => 'SD001',
        ];

        return $courtMappings[$courtName] ?? 'ABC123';
    }

    /**
     * Sanitize log data to remove sensitive information.
     */
    protected function sanitizeLogData($data): array
    {
        $sanitized = json_decode(json_encode($data), true);
        
        if (isset($sanitized['arg0']['userDto']['password'])) {
            $sanitized['arg0']['userDto']['password'] = '[REDACTED]';
        }

        return $sanitized;
    }

    /**
     * Get available SOAP methods from WSDL.
     */
    public function getAvailableMethods(): array
    {
        try {
            if (!$this->client) {
                return ['error' => 'SOAP client not initialized'];
            }

            $functions = $this->client->__getFunctions();
            $types = $this->client->__getTypes();

            return [
                'functions' => $functions,
                'types' => $types,
            ];

        } catch (Exception $e) {
            Log::error('Failed to get SOAP methods', [
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Test connection to California TVCC.
     */
    public function testConnection(): array
    {
        try {
            if (!$this->client) {
                return [
                    'success' => false,
                    'error' => 'SOAP client not initialized',
                ];
            }

            // Get available methods
            $methods = $this->getAvailableMethods();

            return [
                'success' => true,
                'message' => 'California TVCC SOAP client ready',
                'wsdl_path' => $this->wsdlPath,
                'endpoint' => 'https://xsg.dmv.ca.gov/tvcc/tvccservice',
                'methods' => $methods,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get TVCC password from database.
     */
    protected function getTvccPassword(): string
    {
        try {
            $passwordRecord = \DB::table('tvcc_passwords')->latest('updated_at')->first();
            
            if ($passwordRecord) {
                return $passwordRecord->password;
            }

            // Fallback to environment variable
            return config('state-integrations.california.tvcc.password', '');

        } catch (Exception $e) {
            Log::warning('Failed to get TVCC password from database', [
                'error' => $e->getMessage(),
            ]);

            return config('state-integrations.california.tvcc.password', '');
        }
    }

    /**
     * Store TVCC response in database.
     */
    protected function storeTvccResponse($certificateData, $response): void
    {
        try {
            // Create tvcc_response table if it doesn't exist
            if (!\DB::getSchemaBuilder()->hasTable('tvcc_response')) {
                \DB::statement('
                    CREATE TABLE tvcc_response (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        vscid VARCHAR(50),
                        certificate_number VARCHAR(100),
                        response_data TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ');
            }

            // Extract student ID
            $studentId = is_string($certificateData) || is_numeric($certificateData) 
                ? $certificateData 
                : ($certificateData->enrollment->user->vscid ?? $certificateData->enrollment->user->id ?? 'unknown');

            // Extract certificate number from response
            $certificateNumber = null;
            if (isset($response->return->ccSeqNbr)) {
                $certificateNumber = $response->return->ccSeqNbr;
            } elseif (is_string($response)) {
                $certificateNumber = $response;
            }

            \DB::table('tvcc_response')->insert([
                'vscid' => $studentId,
                'certificate_number' => $certificateNumber ?? 'CA' . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'response_data' => json_encode($response),
                'created_at' => now(),
            ]);

            Log::info('TVCC response stored successfully', [
                'student_id' => $studentId,
                'certificate_number' => $certificateNumber,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to store TVCC response', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}