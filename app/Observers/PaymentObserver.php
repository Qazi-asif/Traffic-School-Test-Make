<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Only create invoice if payment is completed and doesn't have one
        if ($payment->status === 'completed' && ! $payment->invoice) {
            $this->createInvoice($payment);
        }
    }

    /**
     * Create invoice for payment
     */
    private function createInvoice(Payment $payment): void
    {
        try {
            // Load enrollment and course
            $payment->load('enrollment.course', 'user');

            $course = $payment->enrollment?->course;
            $user = $payment->user;

            // Calculate tax (assuming 8% tax rate, adjust as needed)
            $taxRate = 8.00; // 8%
            $subtotal = $payment->amount / (1 + ($taxRate / 100));
            $taxAmount = $payment->amount - $subtotal;

            // Prepare invoice items
            $items = [];
            if ($course) {
                $items[] = [
                    'description' => $course->title ?? 'Course Enrollment',
                    'course_id' => $course->id,
                    'quantity' => 1,
                    'unit_price' => round($subtotal, 2),
                    'total' => round($subtotal, 2),
                ];
            }

            // Generate invoice number
            $invoiceNumber = 'INV-'.date('Y').'-'.str_pad($payment->id, 6, '0', STR_PAD_LEFT);

            // Create invoice
            \App\Models\Invoice::create([
                'payment_id' => $payment->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'items' => $items,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'tax_rate' => $taxRate,
                'total_amount' => $payment->amount,
            ]);

            \Log::info('Invoice created automatically', [
                'payment_id' => $payment->id,
                'invoice_number' => $invoiceNumber,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create invoice for payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Record merchant transaction when payment is completed
        if ($payment->wasChanged('status') && $payment->status === 'completed') {
            try {
                $merchantService = app(\App\Services\MerchantService::class);
                $merchantService->recordCharge($payment, $payment->gateway_response ?? []);

                \Log::info('Merchant transaction recorded', ['payment_id' => $payment->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to record merchant transaction', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
