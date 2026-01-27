<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['payment.user', 'payment.enrollment.course'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($invoices);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);

        return response()->json($invoice);
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);

        $pdf = Pdf::loadView('invoices.template', compact('invoice'));

        return $pdf->download($invoice->invoice_number.'.pdf');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:payments,id',
                'invoice_number' => 'required|string|unique:invoices',
                'total_amount' => 'required|numeric|min:0',
                'invoice_date' => 'required|date',
            ]);

            $invoice = Invoice::create($request->all());

            return response()->json($invoice->load(['payment.user', 'payment.enrollment.course']));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        try {
            $request->validate([
                'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,'.$invoice->id,
                'total_amount' => 'sometimes|numeric|min:0',
                'invoice_date' => 'sometimes|date',
            ]);

            $invoice->update($request->all());

            return response()->json($invoice->load(['payment.user', 'payment.enrollment.course']));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            $invoice->delete();

            return response()->json(['message' => 'Invoice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function send(Invoice $invoice)
    {
        try {
            return $this->emailInvoice($invoice);
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to send invoice: '.$e->getMessage()], 500);
        }
    }

    public function emailInvoice(Invoice $invoice)
    {
        try {
            $invoice->load(['payment.user', 'payment.enrollment.course']);

            $user = $invoice->payment->user;

            if (! $user || ! $user->email) {
                return response()->json(['error' => 'User email not found'], 400);
            }

            \Log::info('Generating PDF for invoice', ['invoice_id' => $invoice->id]);

            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.template', compact('invoice'));
                $pdfOutput = $pdf->output();
            } catch (\Exception $e) {
                \Log::error('PDF generation failed', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue without PDF attachment
                $pdfOutput = null;
            }

            \Log::info('Sending invoice email', [
                'invoice_id' => $invoice->id,
                'to' => $user->email,
                'has_pdf' => $pdfOutput !== null,
            ]);

            \Mail::send('emails.invoice', compact('invoice', 'user'), function ($message) use ($invoice, $pdfOutput, $user) {
                $message->to($user->email)
                    ->subject('Invoice '.$invoice->invoice_number);

                if ($pdfOutput) {
                    $message->attachData($pdfOutput, $invoice->invoice_number.'.pdf');
                }
            });

            $invoice->update(['sent_at' => now()]);

            \Log::info('Invoice email sent successfully', ['invoice_id' => $invoice->id]);

            return response()->json([
                'message' => 'Invoice emailed successfully',
                'sent_to' => $user->email,
                'invoice_number' => $invoice->invoice_number,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to send invoice: '.$e->getMessage()], 500);
        }
    }

    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);

        $pdf = Pdf::loadView('invoices.template', compact('invoice'));

        return $pdf->stream($invoice->invoice_number.'.pdf');
    }

    /**
     * Show invoice for authenticated user (public route)
     */
    public function showPublic(Invoice $invoice)
    {
        // Verify the invoice belongs to the authenticated user
        if ($invoice->payment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        $invoice->load(['payment.user', 'payment.enrollment.course']);

        return view('invoices.view', compact('invoice'));
    }

    /**
     * Download invoice PDF for authenticated user (public route)
     */
    public function downloadPublic(Invoice $invoice)
    {
        // Verify the invoice belongs to the authenticated user
        if ($invoice->payment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        $invoice->load(['payment.user', 'payment.enrollment.course']);

        $pdf = Pdf::loadView('invoices.template', compact('invoice'));

        return $pdf->download($invoice->invoice_number.'.pdf');
    }
}
