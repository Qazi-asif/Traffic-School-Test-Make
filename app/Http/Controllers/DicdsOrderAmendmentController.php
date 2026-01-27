<?php

namespace App\Http\Controllers;

use App\Models\DicdsCertificateOrder;
use App\Models\DicdsOrderAmendment;
use Illuminate\Http\Request;

class DicdsOrderAmendmentController extends Controller
{
    public function amend(Request $request, $id)
    {
        $validated = $request->validate([
            'amended_certificate_count' => 'required|integer|min:1',
            'amendment_reason' => 'required|string|min:10',
        ]);

        $order = DicdsCertificateOrder::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Only pending orders can be amended'], 422);
        }

        $unitPrice = $order->total_amount / $order->certificate_count;
        $amendedTotal = $validated['amended_certificate_count'] * $unitPrice;

        $amendment = DicdsOrderAmendment::create([
            'order_id' => $order->id,
            'original_certificate_count' => $order->certificate_count,
            'amended_certificate_count' => $validated['amended_certificate_count'],
            'original_total_amount' => $order->total_amount,
            'amended_total_amount' => $amendedTotal,
            'amended_by' => auth()->id(),
            'amendment_reason' => $validated['amendment_reason'],
            'amended_at' => now(),
        ]);

        $order->update([
            'certificate_count' => $validated['amended_certificate_count'],
            'total_amount' => $amendedTotal,
        ]);

        return response()->json($amendment, 200);
    }

    public function history($id)
    {
        $amendments = DicdsOrderAmendment::where('order_id', $id)
            ->with('amendedBy')
            ->orderBy('amended_at', 'desc')
            ->get();

        return response()->json($amendments);
    }

    public function amendWeb(Request $request, $id)
    {
        return $this->amend($request, $id);
    }
}
