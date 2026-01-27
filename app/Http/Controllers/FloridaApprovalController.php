<?php

namespace App\Http\Controllers;

use App\Models\DicdsCertificateOrder;
use App\Models\FloridaOrderApproval;
use Illuminate\Http\Request;

class FloridaApprovalController extends Controller
{
    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'approved_by_florida' => 'required|boolean',
            'florida_approval_date' => 'nullable|date',
            'florida_reference_number' => 'nullable|string',
            'certificate_numbers_released' => 'nullable|boolean',
        ]);

        $order = DicdsCertificateOrder::findOrFail($id);

        $approval = FloridaOrderApproval::updateOrCreate(
            ['order_id' => $order->id],
            array_merge($validated, [
                'release_date' => $validated['certificate_numbers_released'] ?? false ? now() : null,
            ])
        );

        if ($validated['approved_by_florida']) {
            $order->update(['status' => 'active']);
        }

        return response()->json($approval);
    }

    public function pendingApproval()
    {
        $orders = DicdsCertificateOrder::where('status', 'pending')
            ->with(['school', 'course'])
            ->get();

        return response()->json($orders);
    }

    public function indexWeb()
    {
        $orders = DicdsCertificateOrder::with(['school', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateApprovalWeb(Request $request, $id)
    {
        return $this->updateApproval($request, $id);
    }
}
