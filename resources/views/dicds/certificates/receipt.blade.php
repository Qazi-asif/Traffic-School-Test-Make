@extends('layouts.dicds')
@section('title', 'Certificate Order Receipt')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Certificate Order Receipt</h1>

    <div style="background: white; padding: 20px; border: 2px solid #003366; margin: 20px 0;">
        <h2>Order Confirmation</h2>
        <p><strong>Order ID:</strong> {{ $order->id ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ now()->format('m/d/Y H:i:s') }}</p>
        <p><strong>Status:</strong> <span style="color: green;">Confirmed</span></p>

        <h3>Order Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #003366; color: white;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Item</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Quantity</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Price</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Certificates</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $order->quantity ?? 0 }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${{ number_format($order->unit_price ?? 0, 2) }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${{ number_format($order->total_amount ?? 0, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td colspan="3" style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total:</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${{ number_format($order->total_amount ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #003366;">
            <p><strong>Note:</strong> This receipt confirms your certificate order. Certificates will be available for distribution once processed.</p>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <button onclick="window.print()" class="btn" style="margin-right: 10px;">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <a href="{{ route('dicds.certificates.order') }}" class="btn">New Order</a>
        <a href="{{ route('dicds.provider-menu') }}" class="btn">Return to Menu</a>
    </div>
</div>
@endsection
