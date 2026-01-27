<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMissingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for payments that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for payments without invoices...');

        $payments = \App\Models\Payment::whereDoesntHave('invoice')
            ->where('status', 'completed')
            ->with('enrollment.course', 'user')
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No payments found without invoices.');

            return 0;
        }

        $this->info("Found {$payments->count()} payment(s) without invoices.");

        $bar = $this->output->createProgressBar($payments->count());
        $bar->start();

        $created = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            try {
                $course = $payment->enrollment?->course;

                // Calculate tax (8% tax rate)
                $taxRate = 8.00;
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
                    'invoice_date' => $payment->created_at ?? now(),
                    'due_date' => ($payment->created_at ?? now())->addDays(30),
                    'items' => $items,
                    'subtotal' => round($subtotal, 2),
                    'tax_amount' => round($taxAmount, 2),
                    'tax_rate' => $taxRate,
                    'total_amount' => $payment->amount,
                ]);

                $created++;
            } catch (\Exception $e) {
                $this->error("\nFailed to create invoice for payment #{$payment->id}: {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Successfully created {$created} invoice(s).");
        if ($failed > 0) {
            $this->warn("Failed to create {$failed} invoice(s).");
        }

        return 0;
    }
}
