<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\UserCourseEnrollment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\PaymentExecution;

class PaymentService
{
    private $stripeSecretKey;
    private $paypalApiContext;

    public function __construct()
    {
        $this->stripeSecretKey = config('payment.stripe.secret_key');
        
        // Initialize PayPal
        $this->paypalApiContext = new ApiContext(
            new OAuthTokenCredential(
                config('payment.paypal.client_id'),
                config('payment.paypal.client_secret')
            )
        );
        
        $this->paypalApiContext->setConfig([
            'mode' => config('payment.paypal.mode', 'sandbox'),
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'ERROR'
        ]);
    }

    /**
     * Create payment intent for course enrollment
     */
    public function createPaymentIntent(User $user, Course $course, array $options = []): array
    {
        try {
            // Calculate total amount
            $amount = $this->calculateAmount($course, $options);
            
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'course_table' => $this->getCourseTable($course),
                'amount' => $amount,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_method' => $options['payment_method'] ?? 'stripe',
                'metadata' => [
                    'course_title' => $course->title,
                    'student_name' => $user->first_name . ' ' . $user->last_name,
                    'student_email' => $user->email,
                    'optional_services' => $options['optional_services'] ?? [],
                ],
            ]);

            // Process based on payment method
            switch ($options['payment_method'] ?? 'stripe') {
                case 'stripe':
                    return $this->createStripePaymentIntent($payment, $user, $course);
                case 'paypal':
                    return $this->createPayPalPayment($payment, $user, $course);
                default:
                    throw new \Exception('Unsupported payment method');
            }

        } catch (\Exception $e) {
            Log::error('Payment intent creation failed', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Stripe payment intent
     */
    private function createStripePaymentIntent(Payment $payment, User $user, Course $course): array
    {
        Stripe::setApiKey($this->stripeSecretKey);

        try {
            // Create or retrieve Stripe customer
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $payment->amount * 100, // Convert to cents
                'currency' => strtolower($payment->currency),
                'customer' => $stripeCustomer->id,
                'description' => "Course: {$course->title}",
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Update payment record
            $payment->update([
                'gateway_payment_id' => $paymentIntent->id,
                'gateway_customer_id' => $stripeCustomer->id,
            ]);

            Log::info('Stripe payment intent created', [
                'payment_id' => $payment->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount' => $payment->amount
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ];

        } catch (\Exception $e) {
            $payment->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create PayPal payment
     */
    private function createPayPalPayment(Payment $payment, User $user, Course $course): array
    {
        try {
            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod('paypal');

            $amount = new \PayPal\Api\Amount();
            $amount->setTotal($payment->amount);
            $amount->setCurrency($payment->currency);

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount);
            $transaction->setDescription("Course: {$course->title}");

            $redirectUrls = new \PayPal\Api\RedirectUrls();
            $redirectUrls->setReturnUrl(route('payment.paypal.success', ['payment' => $payment->id]))
                        ->setCancelUrl(route('payment.paypal.cancel', ['payment' => $payment->id]));

            $paypalPayment = new PayPalPayment();
            $paypalPayment->setIntent('sale')
                         ->setPayer($payer)
                         ->setTransactions([$transaction])
                         ->setRedirectUrls($redirectUrls);

            $paypalPayment->create($this->paypalApiContext);

            // Update payment record
            $payment->update([
                'gateway_payment_id' => $paypalPayment->getId(),
            ]);

            Log::info('PayPal payment created', [
                'payment_id' => $payment->id,
                'paypal_payment_id' => $paypalPayment->getId(),
                'amount' => $payment->amount
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'paypal_payment_id' => $paypalPayment->getId(),
                'approval_url' => $paypalPayment->getApprovalLink(),
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ];

        } catch (\Exception $e) {
            $payment->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Confirm Stripe payment
     */
    public function confirmStripePayment(string $paymentIntentId): array
    {
        Stripe::setApiKey($this->stripeSecretKey);

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $payment = Payment::where('gateway_payment_id', $paymentIntentId)->first();

            if (!$payment) {
                throw new \Exception('Payment record not found');
            }

            if ($paymentIntent->status === 'succeeded') {
                return $this->processSuccessfulPayment($payment, [
                    'gateway_transaction_id' => $paymentIntent->charges->data[0]->id ?? null,
                    'gateway_fee' => ($paymentIntent->charges->data[0]->application_fee_amount ?? 0) / 100,
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => 'Payment not completed'
                ]);

                return [
                    'success' => false,
                    'error' => 'Payment was not completed'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Stripe payment confirmation failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute PayPal payment
     */
    public function executePayPalPayment(string $paymentId, string $payerId): array
    {
        try {
            $payment = Payment::where('gateway_payment_id', $paymentId)->first();

            if (!$payment) {
                throw new \Exception('Payment record not found');
            }

            $paypalPayment = PayPalPayment::get($paymentId, $this->paypalApiContext);

            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $paypalPayment->execute($execution, $this->paypalApiContext);

            if ($result->getState() === 'approved') {
                $transaction = $result->getTransactions()[0];
                $relatedResource = $transaction->getRelatedResources()[0];
                $sale = $relatedResource->getSale();

                return $this->processSuccessfulPayment($payment, [
                    'gateway_transaction_id' => $sale->getId(),
                    'gateway_fee' => 0, // PayPal fees are handled separately
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => 'PayPal payment not approved'
                ]);

                return [
                    'success' => false,
                    'error' => 'Payment was not approved'
                ];
            }

        } catch (\Exception $e) {
            Log::error('PayPal payment execution failed', [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment(Payment $payment, array $gatewayData): array
    {
        try {
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'gateway_transaction_id' => $gatewayData['gateway_transaction_id'] ?? null,
                'gateway_fee' => $gatewayData['gateway_fee'] ?? 0,
            ]);

            // Create course enrollment
            $enrollment = $this->createCourseEnrollment($payment);

            // Send confirmation email
            $this->sendPaymentConfirmationEmail($payment, $enrollment);

            // Log successful payment
            Log::info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'enrollment_id' => $enrollment->id,
                'amount' => $payment->amount,
                'method' => $payment->payment_method
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'enrollment_id' => $enrollment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ];

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $payment->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create course enrollment after successful payment
     */
    private function createCourseEnrollment(Payment $payment): UserCourseEnrollment
    {
        $course = $this->getCourseFromPayment($payment);

        $enrollment = UserCourseEnrollment::create([
            'user_id' => $payment->user_id,
            'course_id' => $payment->course_id,
            'course_table' => $payment->course_table,
            'payment_id' => $payment->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'expires_at' => now()->addDays(config('courses.enrollment_duration_days', 90)),
        ]);

        // Create initial progress record
        $this->createInitialProgress($enrollment, $course);

        return $enrollment;
    }

    /**
     * Create initial progress for enrollment
     */
    private function createInitialProgress(UserCourseEnrollment $enrollment, $course): void
    {
        // This would integrate with the course player system
        // to create initial chapter progress records
        
        Log::info('Initial progress created for enrollment', [
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id
        ]);
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmationEmail(Payment $payment, UserCourseEnrollment $enrollment): void
    {
        try {
            $user = $payment->user;
            $course = $this->getCourseFromPayment($payment);

            \Mail::to($user->email)->send(new \App\Mail\PaymentConfirmation(
                $user,
                $course,
                $payment,
                $enrollment
            ));

            Log::info('Payment confirmation email sent', [
                'payment_id' => $payment->id,
                'user_email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate payment amount including optional services
     */
    private function calculateAmount(Course $course, array $options): float
    {
        $baseAmount = $course->price ?? 0;
        $optionalServicesAmount = 0;

        if (isset($options['optional_services'])) {
            foreach ($options['optional_services'] as $service) {
                $optionalServicesAmount += $service['price'] ?? 0;
            }
        }

        return $baseAmount + $optionalServicesAmount;
    }

    /**
     * Get or create Stripe customer
     */
    private function getOrCreateStripeCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            try {
                return Customer::retrieve($user->stripe_customer_id);
            } catch (\Exception $e) {
                // Customer not found, create new one
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Get course from payment
     */
    private function getCourseFromPayment(Payment $payment)
    {
        switch ($payment->course_table) {
            case 'florida_courses':
                return \App\Models\FloridaCourse::find($payment->course_id);
            case 'missouri_courses':
                return \App\Models\Missouri\Course::find($payment->course_id);
            case 'texas_courses':
                return \App\Models\Texas\Course::find($payment->course_id);
            case 'delaware_courses':
                return \App\Models\Delaware\Course::find($payment->course_id);
            default:
                return \App\Models\Course::find($payment->course_id);
        }
    }

    /**
     * Get course table name
     */
    private function getCourseTable($course): string
    {
        $className = get_class($course);
        
        switch ($className) {
            case \App\Models\FloridaCourse::class:
                return 'florida_courses';
            case \App\Models\Missouri\Course::class:
                return 'missouri_courses';
            case \App\Models\Texas\Course::class:
                return 'texas_courses';
            case \App\Models\Delaware\Course::class:
                return 'delaware_courses';
            default:
                return 'courses';
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(array $filters = []): array
    {
        $query = Payment::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $totalPayments = $query->count();
        $completedPayments = $query->where('status', 'completed')->count();
        $totalRevenue = $query->where('status', 'completed')->sum('amount');
        $averageAmount = $completedPayments > 0 ? $totalRevenue / $completedPayments : 0;

        return [
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'failed_payments' => $query->where('status', 'failed')->count(),
            'pending_payments' => $query->where('status', 'pending')->count(),
            'total_revenue' => $totalRevenue,
            'average_amount' => round($averageAmount, 2),
            'success_rate' => $totalPayments > 0 ? round(($completedPayments / $totalPayments) * 100, 2) : 0,
        ];
    }
}