<?php

namespace App\Http\Controllers;

use App\Models\DicdsCertificateOrder;
use Illuminate\Http\Request;

class DicdsOrderController extends Controller
{
    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:florida_schools,id',
            'course_id' => 'required|exists:florida_courses,id',
            'certificate_count' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $order = DicdsCertificateOrder::create($validated);

        return response()->json($order->load(['school', 'course']), 201);
    }
}
