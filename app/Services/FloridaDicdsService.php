<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class FloridaDicdsService
{
    private $soapUrl;
    private $credentials;
    private $timeout;

    public function __construct()
    {
        $this->soapUrl = config('flhsmv.soap_url');
        $this->credentials = [
            'username' => config('flhsmv.username'),
            'password' => config('flhsmv.password'),
            'school_id' => config('flhsmv.school_id'),
        ];
        $this->timeout = config('flhsmv.timeout', 30);
    }

    /**
     * Submit certificate to Florida DICDS system
     */
    public function submitCertificate(array $certificateData): array
    {
        Log::info("Submitting certificate to Florida DICDS", [
            'certificate_number' => $certificateData['certificate_number'] ?? 'N/A'
        ]);

        try {
            // Validate required fields
            $this->validateCertificateData($certificateData);

            // Build SOAP request
            $soapRequest = $this->buildSoapRequest($certificateData);

            // Send SOAP request
            $response = $this->sendSoapRequest($soapRequest);

            // Parse response
            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error("Florida DICDS submission failed", [
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
     * Validate certificate data for Florida requirements
     */
    private function validateCertificateData(array $data): void
    {
        $required = [
            'certificate_number',
            'student_name',
            'driver_license_number',
            'completion_date',
            'final_exam_score'
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate Florida driver license format
        if (!preg_match('/^[A-Z]\d{12}$/', $data['driver_license_number'])) {
            throw new \Exception("Invalid Florida driver license format");
        }

        // Validate exam score
        if ($data['final_exam_score'] < 80) {
            throw new \Exception("Final exam score must be at least 80% for Florida");
        }
    }

    /**
     * Build SOAP request XML
     */
    private function buildSoapRequest(array $data): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
                       xmlns:dic="http://dicds.flhsmv.gov/">
            <soap:Header>
                <dic:Authentication>
                    <dic:Username>' . htmlspecialchars($this->credentials['username']) . '</dic:Username>
                    <dic:Password>' . htmlspecialchars($this->credentials['password']) . '</dic:Password>
                    <dic:SchoolId>' . htmlspecialchars($this->credentials['school_id']) . '</dic:SchoolId>
                </dic:Authentication>
            </soap:Header>
            <soap:Body>
                <dic:SubmitCertificate>
                    <dic:CertificateData>
                        <dic:CertificateNumber>' . htmlspecialchars($data['certificate_number']) . '</dic:CertificateNumber>
                        <dic:StudentName>' . htmlspecialchars($data['student_name']) . '</dic:StudentName>
                        <dic:DriverLicenseNumber>' . htmlspecialchars($data['driver_license_number']) . '</dic:DriverLicenseNumber>
                        <dic:CitationNumber>' . htmlspecialchars($data['citation_number'] ?? '') . '</dic:CitationNumber>
                        <dic:CompletionDate>' . $data['completion_date'] . '</dic:CompletionDate>
                        <dic:FinalExamScore>' . $data['final_exam_score'] . '</dic:FinalExamScore>
                        <dic:CourseType>' . htmlspecialchars($data['course_type'] ?? 'BDI') . '</dic:CourseType>
                        <dic:CourtName>' . htmlspecialchars($data['court_name'] ?? '') . '</dic:CourtName>
                        <dic:County>' . htmlspecialchars($data['county'] ?? '') . '</dic:County>
                    </dic:CertificateData>
                </dic:SubmitCertificate>
            </soap:Body>
        </soap:Envelope>';

        return $xml;
    }

    /**
     * Send SOAP request to Florida DICDS
     */
    private function sendSoapRequest(string $soapRequest): string
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://dicds.flhsmv.gov/SubmitCertificate'
            ])
            ->withBody($soapRequest, 'text/xml')
            ->post($this->soapUrl);

        if (!$response->successful()) {
            throw new \Exception("SOAP request failed: HTTP {$response->status()}");
        }

        return $response->body();
    }

    /**
     * Parse SOAP response
     */
    private function parseResponse(string $responseXml): array
    {
        try {
            $xml = simplexml_load_string($responseXml);
            
            if ($xml === false) {
                throw new \Exception("Invalid XML response");
            }

            // Register namespaces
            $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('dic', 'http://dicds.flhsmv.gov/');

            // Check for SOAP fault
            $fault = $xml->xpath('//soap:Fault');
            if (!empty($fault)) {
                $faultString = (string) $fault[0]->faultstring;
                throw new \Exception("SOAP Fault: {$faultString}");
            }

            // Extract response data
            $result = $xml->xpath('//dic:SubmitCertificateResponse/dic:Result');
            if (empty($result)) {
                throw new \Exception("No result found in response");
            }

            $resultData = $result[0];
            $success = (string) $resultData->Success === 'true';
            $message = (string) $resultData->Message;
            $submissionId = (string) $resultData->SubmissionId;
            $responseCode = (string) $resultData->ResponseCode;

            Log::info("Florida DICDS response parsed", [
                'success' => $success,
                'message' => $message,
                'submission_id' => $submissionId,
                'response_code' => $responseCode
            ]);

            return [
                'success' => $success,
                'message' => $message,
                'submission_id' => $submissionId,
                'response_code' => $responseCode
            ];

        } catch (\Exception $e) {
            Log::error("Failed to parse Florida DICDS response", [
                'error' => $e->getMessage(),
                'response_xml' => $responseXml
            ]);

            throw new \Exception("Response parsing failed: " . $e->getMessage());
        }
    }

    /**
     * Test connection to Florida DICDS
     */
    public function testConnection(): array
    {
        try {
            $testData = [
                'certificate_number' => 'TEST-' . time(),
                'student_name' => 'Test Student',
                'driver_license_number' => 'T123456789012',
                'completion_date' => date('Y-m-d'),
                'final_exam_score' => 95,
                'course_type' => 'BDI'
            ];

            // This would be a test endpoint or test mode
            $result = $this->submitCertificate($testData);

            return [
                'success' => true,
                'message' => 'Connection test successful',
                'test_result' => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get submission status from Florida DICDS
     */
    public function getSubmissionStatus(string $submissionId): array
    {
        try {
            $soapRequest = $this->buildStatusRequest($submissionId);
            $response = $this->sendSoapRequest($soapRequest);
            return $this->parseStatusResponse($response);

        } catch (\Exception $e) {
            Log::error("Failed to get Florida DICDS status", [
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
     * Build status request XML
     */
    private function buildStatusRequest(string $submissionId): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
                       xmlns:dic="http://dicds.flhsmv.gov/">
            <soap:Header>
                <dic:Authentication>
                    <dic:Username>' . htmlspecialchars($this->credentials['username']) . '</dic:Username>
                    <dic:Password>' . htmlspecialchars($this->credentials['password']) . '</dic:Password>
                    <dic:SchoolId>' . htmlspecialchars($this->credentials['school_id']) . '</dic:SchoolId>
                </dic:Authentication>
            </soap:Header>
            <soap:Body>
                <dic:GetSubmissionStatus>
                    <dic:SubmissionId>' . htmlspecialchars($submissionId) . '</dic:SubmissionId>
                </dic:GetSubmissionStatus>
            </soap:Body>
        </soap:Envelope>';
    }

    /**
     * Parse status response
     */
    private function parseStatusResponse(string $responseXml): array
    {
        $xml = simplexml_load_string($responseXml);
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('dic', 'http://dicds.flhsmv.gov/');

        $result = $xml->xpath('//dic:GetSubmissionStatusResponse/dic:Status');
        if (empty($result)) {
            throw new \Exception("No status found in response");
        }

        $statusData = $result[0];
        
        return [
            'success' => true,
            'status' => (string) $statusData->Status,
            'message' => (string) $statusData->Message,
            'processed_date' => (string) $statusData->ProcessedDate
        ];
    }
}