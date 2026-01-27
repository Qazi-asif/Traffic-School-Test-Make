<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FloridaSoapProxyController extends Controller
{
    /**
     * Proxy SOAP requests to Florida DICDS
     * This allows requests from any IP to reach Florida's server
     */
    public function proxy(Request $request)
    {
        $soapRequest = $request->getContent();

        Log::info('Florida SOAP Proxy Request', [
            'size' => strlen($soapRequest),
        ]);

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://services.flhsmv.gov/DriverSchoolWebService/DriverSchoolWebService.asmx');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $soapRequest);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: ""',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            Log::info('Florida SOAP Proxy Response', [
                'http_code' => $httpCode,
                'error' => $curlError,
            ]);

            return response($response, $httpCode)
                ->header('Content-Type', 'text/xml; charset=utf-8');

        } catch (\Exception $e) {
            Log::error('Florida SOAP Proxy Error', [
                'error' => $e->getMessage(),
            ]);

            return response('Error: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }
}
