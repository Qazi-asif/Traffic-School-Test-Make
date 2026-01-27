<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\StripePayment;
use Exception;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('payment.stripe.secret_key'));
    }

    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function processPayment($paymentIntentId, $userId, $paymentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            $transaction = PaymentTransaction::create([
                'user_id' => $userId,
                'payment_id' => $paymentId,
                'gateway' => 'stripe',
                'transaction_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'payment_method' => $paymentIntent->payment_method,
                'metadata' => $paymentIntent->metadata->toArray(),
                'processed_at' => now(),
            ]);

            StripePayment::create([
                'payment_transaction_id' => $transaction->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_charge_id' => $paymentIntent->charges->data[0]->id ?? null,
                'stripe_customer_id' => $paymentIntent->customer,
                'payment_method_id' => $paymentIntent->payment_method,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'metadata' => $paymentIntent->metadata->toArray(),
            ]);

            return ['success' => true, 'transaction' => $transaction];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refund($paymentIntentId, $amount = null)
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount ? $amount * 100 : null,
            ]);

            return ['success' => true, 'refund' => $refund];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
