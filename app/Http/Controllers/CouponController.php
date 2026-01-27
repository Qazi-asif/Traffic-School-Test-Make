<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with('usage')->orderBy('created_at', 'desc')->get();

        if (request()->expectsJson()) {
            return response()->json($coupons);
        }

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:6|unique:coupons,code',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'quantity' => 'required|integer|min:1|max:100',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $quantity = $request->quantity;
        $coupons = [];

        for ($i = 0; $i < $quantity; $i++) {
            $code = $request->code ?: Coupon::generateCode();

            // If custom code provided and quantity > 1, append number
            if ($request->code && $quantity > 1) {
                $code = substr($request->code, 0, 4).($i + 1);
            }

            $coupons[] = Coupon::create([
                'code' => $code,
                'amount' => $request->amount,
                'type' => $request->type,
                'expires_at' => $request->expires_at,
                'usage_limit' => $request->usage_limit,
                'is_active' => true,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json($coupons, 201);
        }

        return redirect()->back()->with('success', $quantity.' coupon(s) created successfully!');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        $coupon->update([
            'amount' => $request->amount,
            'type' => $request->type,
            'usage_limit' => $request->usage_limit,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully!',
                'coupon' => $coupon->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Coupon updated successfully!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Coupon deleted successfully']);
        }

        return redirect()->back()->with('success', 'Coupon deleted successfully!');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            \Log::warning('Coupon not found: ' . $request->code);
            return response()->json(['error' => 'Coupon code not found'], 400);
        }

        if (!$coupon->isValid()) {
            $reason = '';
            if (!$coupon->is_active) {
                $reason = 'inactive';
            } elseif ($coupon->is_used) {
                $reason = 'already used';
            } elseif ($coupon->expires_at && $coupon->expires_at->isPast()) {
                $reason = 'expired';
            }
            \Log::warning('Coupon invalid: ' . $request->code . ' (' . $reason . ')');
            return response()->json(['error' => 'Invalid or expired coupon'], 400);
        }

        $discount = $coupon->calculateDiscount($request->amount);
        $finalAmount = $request->amount - $discount;

        return response()->json([
            'valid' => true,
            'discount' => (float) $discount,
            'final_amount' => (float) $finalAmount,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'amount' => $coupon->amount,
            ],
        ]);
    }

    public function use(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (! $coupon || ! $coupon->isValid()) {
            return response()->json(['error' => 'Invalid, expired, or already used coupon'], 400);
        }

        $discount = $coupon->calculateDiscount($request->amount);
        $finalAmount = $request->amount - $discount;

        // Record usage
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => auth()->id(),
            'discount_amount' => $discount,
            'original_amount' => $request->amount,
            'final_amount' => $finalAmount,
        ]);

        // Increment usage count
        $coupon->incrementUsage();

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'final_amount' => $finalAmount,
        ]);
    }
}
