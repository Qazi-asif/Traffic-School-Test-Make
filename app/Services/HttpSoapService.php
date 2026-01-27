<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP-based SOAP service that works without PHP SOAP extension.
 * Manually constructs SOAP XML and sends via HTTP POST.
 */
class HttpSoapService
{
    protected string $endpoint;
    protected string $soapAction;
    protected int $timeout;
    protected array $headers;

    public function __construct(string $endpoint, string $soapAction = '', int $timeout = 30)
    {
        $this->endpoint = $endpoint;
        $this->soapAction = $soapAction;
        $this->timeout = $timeout;
        $this->headers = [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => $soapAction,
        ];
    }

    /**
     * Send SOAP request via HTTP POST.
     */
    public function call(string $method, array $parameters = []): array
    {
        try {
            $soapXml = $this->buildSoapEnvelope($method, $parameters);
            
            Log::info('Sending HTTP SOAP request', [
                'endpoint' => $this->endpoint,
                'method' => $method,
                'soap_action' => $this->soapAction,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->withOptions([
                    'verify' => false, // Disable SSL verification for problematic certificates
                ])
                ->send('POST', $this->endpoint, [
                    'body' => $soapXml,
                ]);

            if ($response->successful()) {
                return $this->parseSoapResponse($response->body());
            } else {
                return [
                    'success' => false,
                    'error' => 'HTTP request failed: ' . $response->body(),
                    'status_code' => $response->status(),
                ];
            }

        } catch (Exception $e) {
            Log::error('HTTP SOAP request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $this->endpoint,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 500,
            ];
        }
    }

    /**
     * Build SOAP envelope XML.
     */
    protected function buildSoapEnvelope(string $method, array $parameters): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';
        $xml .= '<soap:Body>';
        $xml .= "<{$method}>";
        
        foreach ($parameters as $key => $value) {
            $xml .= $this->buildXmlElement($key, $value);
        }
        
        $xml .= "</{$method}>";
        $xml .= '</soap:Body>';
        $xml .= '</soap:Envelope>';

        return $xml;
    }

    /**
     * Build XML element from parameter.
     */
    protected function buildXmlElement(string $key, $value): string
    {
        if (is_array($value) || is_object($value)) {
            $xml = "<{$key}>";
            foreach ((array) $value as $subKey => $subValue) {
                $xml .= $this->buildXmlElement($subKey, $subValue);
            }
            $xml .= "</{$key}>";
            return $xml;
        } else {
            $escapedValue = htmlspecialchars((string) $value, ENT_XML1, 'UTF-8');
            return "<{$key}>{$escapedValue}</{$key}>";
        }
    }

    /**
     * Parse SOAP response XML.
     */
    protected function parseSoapResponse(string $xmlResponse): array
    {
        try {
            // Remove namespaces for easier parsing
            $cleanXml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlResponse);
            $cleanXml = preg_replace('/[a-zA-Z0-9]+:/', '', $cleanXml);
            
            $xml = simplexml_load_string($cleanXml);
            
            if ($xml === false) {
                return [
                    'success' => false,
                    'error' => 'Invalid XML response',
                    'raw_response' => $xmlResponse,
                ];
            }

            // Convert to array for easier handling
            $array = json_decode(json_encode($xml), true);
            
            return [
                'success' => true,
                'data' => $array,
                'raw_response' => $xmlResponse,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to parse SOAP response: ' . $e->getMessage(),
                'raw_response' => $xmlResponse,
            ];
        }
    }

    /**
     * Set custom headers.
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set timeout.
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }
}