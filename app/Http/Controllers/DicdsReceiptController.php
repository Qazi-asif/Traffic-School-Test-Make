<?php

namespace App\Http\Controllers;

use App\Models\DicdsCertificateOrder;
use App\Models\DicdsOrderReceipt;
use Illuminate\Http\Request;

class DicdsReceiptController extends Controller
{
    public function generate(Request $request, $id)
    {
        $order = DicdsCertificateOrder::with(['school', 'course'])->findOrFail($id);

        $receiptData = [
            'order_id' => $order->id,
            'school_name' => $order->school->name,
            'course_type' => $order->course->name,
            'certificate_count' => $order->certificate_count,
            'unit_price' => $order->total_amount / $order->certificate_count,
            'total_amount' => $order->total_amount,
            'order_date' => $order->created_at->format('Y-m-d'),
            'florida_mailing_address' => 'Florida DHSMV, Bureau of Records, Neil Kirkman Building, Tallahassee, FL 32399',
        ];

        $validated = $request->validate([
            'receipt_data' => 'sometimes|array',
            'florida_mailing_address' => 'sometimes|string',
        ]);

        $receiptData = array_merge($receiptData, $validated['receipt_data'] ?? []);

        $receipt = DicdsOrderReceipt::create([
            'order_id' => $order->id,
            'receipt_number' => 'RCP-'.strtoupper(uniqid()),
            'receipt_data' => $receiptData,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        return response()->json($receipt, 201);
    }

    public function show($id)
    {
        $receipt = DicdsOrderReceipt::where('order_id', $id)
            ->with('generatedBy')
            ->latest('generated_at')
            ->firstOrFail();

        return response()->json($receipt);
    }

    public function markPrinted(Request $request, $id)
    {
        $receipt = DicdsOrderReceipt::where('order_id', $id)
            ->latest('generated_at')
            ->firstOrFail();

        $receipt->update(['printed_at' => now()]);

        return response()->json($receipt);
    }

    public function generateWeb(Request $request, $id)
    {
        return $this->generate($request, $id);
    }
}
