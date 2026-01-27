<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\PayPalPayment;
use Exception;

class PayPalPaymentService
{
    private $clientId;

    private $clientSecret;

    private $mode;

    private $baseUrl;

    public function __construct()
    {
        $this->clientId = config('payment.paypal.client_id');
        $this->clientSecret = config('payment.paypal.client_secret');
        $this->mode = config('payment.paypal.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function createOrder($amount, $currency = 'USD', $metadata = [])
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = $this->makeRequest('/v2/checkout/orders', 'POST', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('payment.paypal.success'),
                    'cancel_url' => route('payment.paypal.cancel'),
                ],
            ], $accessToken);

            return [
                'success' => true,
                'order_id' => $response['id'],
                'approval_url' => $this->getApprovalUrl($response),
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function captureOrder($orderId, $userId, $paymentId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = $this->makeRequest("/v2/checkout/orders/{$orderId}/capture", 'POST', [], $accessToken);

            $transaction = PaymentTransaction::create([
                'user_id' => $userId,
                'payment_id' => $paymentId,
                'gateway' => 'paypal',
                'transaction_id' => $response['id'],
                'amount' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
                'currency' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
                'status' => $response['status'],
                'metadata' => $response,
                'processed_at' => now(),
            ]);

            PayPalPayment::create([
                'payment_transaction_id' => $transaction->id,
                'paypal_order_id' => $response['id'],
                'paypal_payer_id' => $response['payer']['payer_id'] ?? null,
                'paypal_transaction_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'],
                'status' => $response['status'],
                'amount' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
                'currency' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
                'metadata' => $response,
            ]);

            return ['success' => true, 'transaction' => $transaction];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function getAccessToken()
    {
        $response = $this->makeRequest('/v1/oauth2/token', 'POST', [
            'grant_type' => 'client_credentials',
        ], null, true);

        return $response['access_token'];
    }

    private function makeRequest($endpoint, $method, $data = [], $accessToken = null, $isAuth = false)
    {
        $ch = curl_init($this->baseUrl.$endpoint);

        $headers = ['Content-Type: application/json'];

        if ($isAuth) {
            $headers[] = 'Authorization: Basic '.base64_encode($this->clientId.':'.$this->clientSecret);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            if ($accessToken) {
                $headers[] = 'Authorization: Bearer '.$accessToken;
            }
            if (! empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception('PayPal API Error: '.$response);
        }

        return json_decode($response, true);
    }

    private function getApprovalUrl($response)
    {
        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }

        return null;
    }
}
