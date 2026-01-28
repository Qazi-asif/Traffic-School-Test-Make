<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show payment form
     */
    public function show(Request $request)
    {
        $courseId = $request->get('course_id');
        $courseTable = $request->get('table', 'florida_courses');
        
        if (!$courseId) {
            return redirect('/courses')->with('error', 'Course not specified');
        }

        // Get course based on table
        $course = $this->getCourseByTable($courseTable, $courseId);
        
        if (!$course) {
            return redirect('/courses')->with('error', 'Course not found');
        }

        // Check if user already has an active enrollment for this course
        $existingEnrollment = auth()->user()->enrollments()
            ->where('course_id', $courseId)
            ->where('course_table', $courseTable)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->payment_status === 'paid') {
                return redirect()->route('course.player', $existingEnrollment->id)
                    ->with('info', 'You are already enrolled in this course');
            } else {
                return redirect()->route('payment.status', $existingEnrollment->payment_id)
                    ->with('info', 'You have a pending payment for this course');
            }
        }

        return view('payment.checkout', compact('course', 'courseTable'));
    }

    /**
     * Create payment intent
     */
    public function createIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer',
            'course_table' => 'required|string',
            'payment_method' => 'required|in:stripe,paypal',
            'optional_services' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $course = $this->getCourseByTable($request->course_table, $request->course_id);
            
            if (!$course) {
                return response()->json([
                    'success' => false,
                    'error' => 'Course not found'
                ], 404);
            }

            $result = $this->paymentService->createPaymentIntent(
                auth()->user(),
                $course,
                [
                    'payment_method' => $request->payment_method,
                    'optional_services' => $request->optional_services ?? [],
                ]
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Payment intent creation failed', [
                'user_id' => auth()->id(),
                'course_id' => $request->course_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment processing error. Please try again.'
            ], 500);
        }
    }

    /**
     * Confirm Stripe payment
     */
    public function confirmStripe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->paymentService->confirmStripePayment($request->payment_intent_id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('payment.success', ['payment' => $result['payment_id']])
                ]);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Stripe payment confirmation failed', [
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment confirmation failed. Please contact support.'
            ], 500);
        }
    }

    /**
     * Handle PayPal success
     */
    public function paypalSuccess(Request $request)
    {
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        if (!$paymentId || !$payerId) {
            return redirect()->route('payment.cancel')
                ->with('error', 'Invalid PayPal response');
        }

        try {
            $result = $this->paymentService->executePayPalPayment($paymentId, $payerId);
            
            if ($result['success']) {
                return redirect()->route('payment.success', ['payment' => $result['payment_id']])
                    ->with('success', 'Payment completed successfully!');
            }

            return redirect()->route('payment.cancel')
                ->with('error', $result['error'] ?? 'Payment failed');

        } catch (\Exception $e) {
            Log::error('PayPal payment execution failed', [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('payment.cancel')
                ->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Handle PayPal cancel
     */
    public function paypalCancel(Request $request)
    {
        $paymentId = $request->route('payment');
        
        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment && $payment->user_id === auth()->id()) {
                $payment->markAsFailed('Payment cancelled by user');
            }
        }

        return redirect('/courses')->with('info', 'Payment was cancelled');
    }

    /**
     * Show payment success page
     */
    public function success(Request $request)
    {
        $paymentId = $request->route('payment');
        $payment = Payment::with(['user', 'enrollment'])
            ->where('id', $paymentId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$payment) {
            return redirect('/dashboard')->with('error', 'Payment not found');
        }

        if ($payment->status !== 'completed') {
            return redirect()->route('payment.status', $payment->id)
                ->with('info', 'Payment is still processing');
        }

        return view('payment.success', compact('payment'));
    }

    /**
     * Show payment status page
     */
    public function status(Request $request)
    {
        $paymentId = $request->route('payment');
        $payment = Payment::where('id', $paymentId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$payment) {
            return redirect('/dashboard')->with('error', 'Payment not found');
        }

        return view('payment.status', compact('payment'));
    }

    /**
     * Show payment cancel page
     */
    public function cancel()
    {
        return view('payment.cancel');
    }

    /**
     * Admin: List all payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('gateway_payment_id', 'like', "%{$search}%");
        }

        $payments = $query->paginate(25);

        // Get statistics
        $stats = $this->paymentService->getPaymentStatistics([
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'payments' => $payments,
                'stats' => $stats
            ]);
        }

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    /**
     * Admin: Show payment details
     */
    public function show(Payment $payment)
    {
        $payment->load(['user', 'enrollment']);
        
        return response()->json($payment);
    }

    /**
     * Admin: Process refund
     */
    public function refund(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$payment->is_refundable) {
            return response()->json([
                'success' => false,
                'error' => 'Payment is not refundable'
            ], 400);
        }

        try {
            // Process refund through payment gateway
            $refundAmount = $request->amount ?? $payment->amount;
            
            // TODO: Implement actual gateway refund calls
            // For now, just mark as refunded in database
            
            $payment->processRefund($refundAmount, $request->reason);

            Log::info('Payment refunded', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'reason' => $request->reason,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Refund processing failed'
            ], 500);
        }
    }

    /**
     * User: Get my payments
     */
    public function myPayments(Request $request)
    {
        $payments = Payment::where('user_id', auth()->id())
            ->with(['enrollment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($payments);
        }

        return view('payment.my-payments', compact('payments'));
    }

    /**
     * Webhook handler for Stripe
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('payment.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handleStripePaymentSuccess($event['data']['object']);
                break;
            case 'payment_intent.payment_failed':
                $this->handleStripePaymentFailed($event['data']['object']);
                break;
            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event['type']]);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle successful Stripe payment
     */
    private function handleStripePaymentSuccess($paymentIntent)
    {
        $payment = Payment::where('gateway_payment_id', $paymentIntent['id'])->first();
        
        if ($payment && $payment->status === 'pending') {
            $this->paymentService->confirmStripePayment($paymentIntent['id']);
        }
    }

    /**
     * Handle failed Stripe payment
     */
    private function handleStripePaymentFailed($paymentIntent)
    {
        $payment = Payment::where('gateway_payment_id', $paymentIntent['id'])->first();
        
        if ($payment && $payment->status === 'pending') {
            $payment->markAsFailed($paymentIntent['last_payment_error']['message'] ?? 'Payment failed');
        }
    }

    /**
     * Get course by table and ID
     */
    private function getCourseByTable($table, $courseId)
    {
        switch ($table) {
            case 'florida_courses':
                return FloridaCourse::find($courseId);
            case 'missouri_courses':
                return \App\Models\Missouri\Course::find($courseId);
            case 'texas_courses':
                return \App\Models\Texas\Course::find($courseId);
            case 'delaware_courses':
                return \App\Models\Delaware\Course::find($courseId);
            default:
                return Course::find($courseId);
        }
    }
}