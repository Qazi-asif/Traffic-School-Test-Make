<?php

namespace App\Http\Controllers;

use App\Services\PayPalPaymentService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    protected $stripeService;

    protected $paypalService;

    public function __construct(StripePaymentService $stripeService, PayPalPaymentService $paypalService)
    {
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
    }

    public function createStripePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'metadata' => 'nullable|array',
        ]);

        $result = $this->stripeService->createPaymentIntent(
            $request->amount,
            $request->currency ?? 'usd',
            $request->metadata ?? []
        );

        return response()->json($result);
    }

    public function processStripePayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_id' => 'required|exists:payments,id',
        ]);

        $result = $this->stripeService->processPayment(
            $request->payment_intent_id,
            auth()->id(),
            $request->payment_id
        );

        return response()->json($result);
    }

    public function createPayPalOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
        ]);

        $result = $this->paypalService->createOrder(
            $request->amount,
            $request->currency ?? 'USD'
        );

        return response()->json($result);
    }

    public function capturePayPalOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'payment_id' => 'required|exists:payments,id',
        ]);

        $result = $this->paypalService->captureOrder(
            $request->order_id,
            auth()->id(),
            $request->payment_id
        );

        return response()->json($result);
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Handle Stripe webhook events
        // Implement webhook verification and processing

        return response()->json(['received' => true]);
    }

    public function paypalWebhook(Request $request)
    {
        // Handle PayPal webhook events
        // Implement webhook verification and processing

        return response()->json(['received' => true]);
    }

    public function processDummyPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $payment = \App\Models\Payment::findOrFail($request->payment_id);

            // Update payment status to completed
            $payment->update([
                'status' => 'completed',
                'gateway' => 'dummy',
                'gateway_payment_id' => 'dummy_'.time().'_'.auth()->id(),
            ]);

            \Log::info('Dummy payment processed', ['payment_id' => $payment->id, 'amount' => $request->amount]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            \Log::error('Dummy payment error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
