<?php

namespace App\Http\Controllers;

use App\Events\PaymentApproved;
use App\Events\UserEnrolled;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\Payment;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentPageController extends Controller
{
    public function create(Request $request)
    {
        $courseId = $request->course_id;
        $table = $request->table ?? 'courses';

        // Prefix the course ID with table name for disambiguation
        if ($table === 'florida_courses') {
            $courseId = 'florida_'.$courseId;
        } else {
            $courseId = 'courses_'.$courseId;
        }

        $course = $this->findCourse($courseId);

        if (! $course) {
            return redirect('/courses')->with('error', 'Course not found');
        }

        // Create pending enrollment
        $enrollment = UserCourseEnrollment::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $this->getRealCourseId($courseId),
        ], [
            'amount_paid' => $course->price,
            'payment_status' => 'pending',
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        return view('payment.checkout', compact('course', 'enrollment'));
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        if ($enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        $course = $this->findCourse($enrollment->course_id);

        return view('payment.checkout', compact('course', 'enrollment'));
    }

    public function processStripe(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'payment_method_id' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // If amount is 0 or less, mark as free and complete
        if ($enrollment->amount_paid <= 0) {
            $this->completePayment($enrollment, 'free', 'free-' . time(), $request->only(['address', 'city', 'state', 'country', 'zipcode']));
            return response()->json(['success' => true, 'redirect' => route('payment.success')]);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $enrollment->amount_paid * 100, // Convert to cents
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('payment.success'),
                'metadata' => [
                    'enrollment_id' => $enrollment->id,
                    'user_id' => auth()->id(),
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $this->completePayment($enrollment, 'stripe', $paymentIntent->id, $request->only(['address', 'city', 'state', 'country', 'zipcode']));

                return response()->json(['success' => true, 'redirect' => route('payment.success')]);
            }

            return response()->json(['error' => 'Payment failed'], 400);

        } catch (\Exception $e) {
            \Log::error('Stripe payment error: '.$e->getMessage());

            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    public function processPaypal(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'paypal_order_id' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // If amount is 0 or less, mark as free and complete
        if ($enrollment->amount_paid <= 0) {
            $this->completePayment($enrollment, 'free', 'free-' . time(), $request->only(['address', 'city', 'state', 'country', 'zipcode']));
            return response()->json(['success' => true, 'redirect' => route('payment.success')]);
        }

        try {
            // Verify PayPal payment (simplified - implement full PayPal SDK integration)
            $this->completePayment($enrollment, 'paypal', $request->paypal_order_id, $request->only(['address', 'city', 'state', 'country', 'zipcode']));

            return response()->json(['success' => true, 'redirect' => route('payment.success')]);

        } catch (\Exception $e) {
            \Log::error('PayPal payment error: '.$e->getMessage());

            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    public function processAuthorizenet(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'card_number' => 'required|string',
            'expiry_month' => 'required|string',
            'expiry_year' => 'required|string',
            'cvv' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string',
            'amount' => 'sometimes|numeric|min:0',
            'original_amount' => 'sometimes|numeric|min:0',
            'coupon_code' => 'nullable|string|max:6',
            'discount_amount' => 'sometimes|numeric|min:0',
            'optional_services' => 'sometimes|array',
            'optional_services_total' => 'sometimes|numeric|min:0',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Set the transaction's reference ID
            $refId = 'ref'.time();

            // Get credentials from database or .env fallback
            $gatewayService = app(\App\Services\PaymentGatewayConfigService::class);
            $config = $gatewayService->getAuthorizeNetConfig();

            $loginId = $config['api_login_id'];
            $transactionKey = $config['transaction_key'];
            $environment = $config['environment'];

            // Debug logging
            \Log::info('Authorize.Net Credentials Check', [
                'login_id' => $loginId ? 'Present' : 'MISSING',
                'transaction_key' => $transactionKey ? 'Present' : 'MISSING',
                'environment' => $environment,
                'source' => $config['source'],
            ]);

            // Create a merchant authentication instance
            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType;
            $merchantAuthentication->setName($loginId);
            $merchantAuthentication->setTransactionKey($transactionKey);

            // Create the payment data for a credit card
            $creditCard = new AnetAPI\CreditCardType;
            $creditCard->setCardNumber($request->card_number);
            $creditCard->setExpirationDate($request->expiry_year.'-'.$request->expiry_month);
            $creditCard->setCardCode($request->cvv);

            // Add the payment data to a paymentType object
            $paymentOne = new AnetAPI\PaymentType;
            $paymentOne->setCreditCard($creditCard);

            // Create order information
            $order = new AnetAPI\OrderType;
            $order->setInvoiceNumber('INV-'.$enrollment->id);
            $order->setDescription('Course Enrollment Payment');

            // Set the customer's Bill To address
            $customerAddress = new AnetAPI\CustomerAddressType;
            $customerAddress->setFirstName(auth()->user()->first_name);
            $customerAddress->setLastName(auth()->user()->last_name);
            $customerAddress->setAddress($request->address);
            $customerAddress->setCity($request->city);
            $customerAddress->setState($request->state);
            $customerAddress->setZip($request->zipcode);
            $customerAddress->setCountry($request->country);

            // Set the customer's identifying information
            $customerData = new AnetAPI\CustomerDataType;
            $customerData->setType('individual');
            $customerData->setId(auth()->id());
            $customerData->setEmail(auth()->user()->email);

            // Use coupon amount if provided, otherwise use enrollment amount
            $paymentAmount = $request->has('amount') ? $request->amount : $enrollment->amount_paid;
            
            // If amount is 0 or less, mark as free and complete
            if ($paymentAmount <= 0) {
                $this->completePayment($enrollment, 'free', 'free-' . time(), $request->only(['address', 'city', 'state', 'country', 'zipcode']));
                return response()->json(['success' => true, 'redirect' => route('payment.success')]);
            }
            
            // Update enrollment amount with final total (including optional services)
            $enrollment->update(['amount_paid' => $paymentAmount]);
            
            // Store optional services data if provided
            if ($request->has('optional_services') && !empty($request->optional_services)) {
                $enrollment->update([
                    'optional_services' => json_encode($request->optional_services),
                    'optional_services_total' => $request->optional_services_total ?? 0
                ]);
            }

            // Create a transaction
            $transactionRequestType = new AnetAPI\TransactionRequestType;
            $transactionRequestType->setTransactionType('authCaptureTransaction');
            $transactionRequestType->setAmount($paymentAmount);
            $transactionRequestType->setOrder($order);
            $transactionRequestType->setPayment($paymentOne);
            $transactionRequestType->setBillTo($customerAddress);
            $transactionRequestType->setCustomer($customerData);

            // Assemble the complete transaction request
            $requestApi = new AnetAPI\CreateTransactionRequest;
            $requestApi->setMerchantAuthentication($merchantAuthentication);
            $requestApi->setRefId($refId);
            $requestApi->setTransactionRequest($transactionRequestType);

            // Create the controller and get the response
            $controller = new AnetController\CreateTransactionController($requestApi);

            // Set endpoint based on mode
            if ($environment === 'production') {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            } else {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
            }

            if ($response != null) {
                if ($response->getMessages()->getResultCode() == 'Ok') {
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getMessages() != null) {
                        $transactionId = $tresponse->getTransId();

                        $this->completePayment(
                            $enrollment,
                            'authorizenet',
                            $transactionId,
                            $request->only(['address', 'city', 'state', 'country', 'zipcode']),
                            'authorizenet',
                            $request->only(['coupon_code', 'discount_amount', 'original_amount'])
                        );

                        return response()->json([
                            'success' => true,
                            'redirect' => route('payment.success'),
                            'transaction_id' => $transactionId,
                        ]);
                    } else {
                        $errorMessage = 'Transaction Failed';
                        if ($tresponse->getErrors() != null) {
                            $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                        }
                        \Log::error('Authorize.Net transaction error: '.$errorMessage);

                        return response()->json(['error' => $errorMessage], 400);
                    }
                } else {
                    $errorMessage = 'Transaction Failed';
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getErrors() != null) {
                        $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                    } else {
                        $errorMessage = $response->getMessages()->getMessage()[0]->getText();
                    }

                    \Log::error('Authorize.Net error: '.$errorMessage);

                    return response()->json(['error' => $errorMessage], 400);
                }
            } else {
                \Log::error('Authorize.Net: No response returned');

                return response()->json(['error' => 'No response from payment gateway'], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Authorize.Net payment error: '.$e->getMessage());

            return response()->json(['error' => 'Payment processing failed: '.$e->getMessage()], 500);
        }
    }

    public function processDummy(Request $request)
    {
        try {
            \Log::info('Dummy payment started', ['request' => $request->all()]);
            
            $request->validate([
                'enrollment_id' => 'required|exists:user_course_enrollments,id',
                'amount' => 'required|numeric|min:0',
                'original_amount' => 'sometimes|numeric|min:0',
                'coupon_code' => 'nullable|string|max:6',
                'discount_amount' => 'sometimes|numeric|min:0',
                'optional_services' => 'sometimes|array',
                'optional_services_total' => 'sometimes|numeric|min:0',
            ]);

            $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);
            \Log::info('Enrollment found', ['enrollment_id' => $enrollment->id]);

            if ($enrollment->user_id !== auth()->id()) {
                \Log::warning('Unauthorized dummy payment attempt', [
                    'enrollment_user_id' => $enrollment->user_id,
                    'auth_user_id' => auth()->id()
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Update enrollment amount if coupon was applied
            if ($request->has('coupon_code') && $request->coupon_code) {
                \Log::info('Updating enrollment amount for coupon', [
                    'old_amount' => $enrollment->amount_paid,
                    'new_amount' => $request->amount,
                    'coupon_code' => $request->coupon_code
                ]);
                $enrollment->update(['amount_paid' => $request->amount]);
            }
            
            // Update enrollment amount with final total (including optional services)
            $enrollment->update(['amount_paid' => $request->amount]);
            
            // Store optional services data if provided
            if ($request->has('optional_services') && !empty($request->optional_services)) {
                \Log::info('Storing optional services', [
                    'services' => $request->optional_services,
                    'total' => $request->optional_services_total
                ]);
                $enrollment->update([
                    'optional_services' => json_encode($request->optional_services),
                    'optional_services_total' => $request->optional_services_total ?? 0
                ]);
            }

            \Log::info('Calling completePayment method');
            
            // Use 'free' as gateway if amount is 0, otherwise use 'stripe'
            $gateway = $request->amount <= 0 ? 'free' : 'stripe';
            $this->completePayment(
                $enrollment, 
                $gateway, 
                ($gateway === 'free' ? 'free-' : 'dummy_') . time() . '_' . auth()->id(), 
                [], 
                'dummy',
                $request->only(['coupon_code', 'discount_amount', 'original_amount'])
            );

            \Log::info('Payment completed successfully');

            return response()->json([
                'success' => true, 
                'redirect' => route('payment.success'),
                'message' => 'Payment processed successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Dummy payment validation error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Dummy payment error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Payment processing failed', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function success()
    {
        return view('payment.success');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }

    private function completePayment($enrollment, $gateway, $gatewayPaymentId, $addressData = [], $paymentMethod = null, $couponData = [])
    {
        try {
            \Log::info('Starting completePayment', [
                'enrollment_id' => $enrollment->id,
                'gateway' => $gateway,
                'payment_method' => $paymentMethod
            ]);

            // Update enrollment
            $enrollment->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod ?? $gateway,
                'payment_id' => $gatewayPaymentId,
            ]);

            \Log::info('Enrollment updated to paid status');

            // Create payment record
            $paymentData = [
                'user_id' => $enrollment->user_id,
                'enrollment_id' => $enrollment->id,
                'amount' => $enrollment->amount_paid,
                'payment_method' => $paymentMethod ?? $gateway,
                'gateway' => $gateway,
                'gateway_payment_id' => $gatewayPaymentId,
                'billing_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
                'billing_email' => auth()->user()->email,
                'address' => $addressData['address'] ?? null,
                'city' => $addressData['city'] ?? null,
                'state' => $addressData['state'] ?? null,
                'country' => $addressData['country'] ?? null,
                'zipcode' => $addressData['zipcode'] ?? null,
                'status' => 'completed',
            ];

            // Add coupon data if provided
            if (!empty($couponData['coupon_code'])) {
                \Log::info('Processing coupon data', ['coupon_code' => $couponData['coupon_code']]);
                
                $paymentData['coupon_code'] = $couponData['coupon_code'];
                $paymentData['discount_amount'] = $couponData['discount_amount'] ?? 0;
                $paymentData['original_amount'] = $couponData['original_amount'] ?? $enrollment->amount_paid;
                
                // Mark coupon as used
                try {
                    if ($coupon = \App\Models\Coupon::where('code', $couponData['coupon_code'])->first()) {
                        $coupon->markAsUsed();
                        
                        // Create coupon usage record
                        \App\Models\CouponUsage::create([
                            'coupon_id' => $coupon->id,
                            'user_id' => $enrollment->user_id,
                            'discount_amount' => $couponData['discount_amount'] ?? 0,
                            'original_amount' => $couponData['original_amount'] ?? $enrollment->amount_paid,
                            'final_amount' => $enrollment->amount_paid,
                        ]);
                        
                        \Log::info('Coupon marked as used and usage recorded');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing coupon: ' . $e->getMessage());
                    // Don't fail the payment for coupon errors
                }
            }

            $payment = Payment::create($paymentData);
            \Log::info('Payment record created', ['payment_id' => $payment->id]);

            // Generate and send payment receipt via email (non-blocking)
            try {
                $course = $this->findCourse($enrollment->course_id);
                if ($course) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.receipt', compact('payment'));

                    \Mail::send('emails.payment-receipt', compact('payment', 'course'), function ($message) use ($payment, $pdf) {
                        $message->to($payment->billing_email)
                            ->subject('Payment Receipt #'.$payment->id.' - Traffic School')
                            ->attachData($pdf->output(), 'payment-receipt-'.$payment->id.'.pdf');
                    });

                    \Log::info('Payment receipt sent', ['payment_id' => $payment->id, 'email' => $payment->billing_email]);
                }
            } catch (\Exception $e) {
                \Log::error('Payment receipt email error: '.$e->getMessage());
                // Don't fail the payment for email errors
            }

            // Send enrollment confirmation email (non-blocking)
            try {
                $course = $this->findCourse($enrollment->course_id);
                if ($course && class_exists('\App\Mail\EnrollmentConfirmation')) {
                    \Mail::to(auth()->user()->email)->send(new \App\Mail\EnrollmentConfirmation(
                        auth()->user(),
                        $course,
                        $enrollment
                    ));
                    \Log::info('Enrollment confirmation email sent');
                }
            } catch (\Exception $e) {
                \Log::error('Enrollment email error: '.$e->getMessage());
                // Don't fail the payment for email errors
            }

            // Dispatch events (non-blocking)
            try {
                if (class_exists('\App\Events\UserEnrolled')) {
                    event(new \App\Events\UserEnrolled($enrollment));
                }
                if (class_exists('\App\Events\PaymentApproved')) {
                    event(new \App\Events\PaymentApproved($payment));
                }
                \Log::info('Events dispatched successfully');
            } catch (\Exception $e) {
                \Log::error('Event dispatch error: '.$e->getMessage());
                // Don't fail the payment for event errors
            }

            \Log::info('Payment completion finished successfully');

        } catch (\Exception $e) {
            \Log::error('Critical error in completePayment', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e; // Re-throw critical errors
        }
    }

    private function findCourse($courseId)
    {
        if (is_numeric($courseId)) {
            return Course::find($courseId) ?? FloridaCourse::find($courseId);
        }

        if (str_starts_with($courseId, 'florida_')) {
            $realId = str_replace('florida_', '', $courseId);

            return FloridaCourse::find($realId);
        } elseif (str_starts_with($courseId, 'courses_')) {
            $realId = str_replace('courses_', '', $courseId);

            return Course::find($realId);
        }

        return null;
    }

    private function getRealCourseId($courseId)
    {
        if (str_starts_with($courseId, 'florida_')) {
            return str_replace('florida_', '', $courseId);
        } elseif (str_starts_with($courseId, 'courses_')) {
            return str_replace('courses_', '', $courseId);
        }

        return $courseId;
    }
}
