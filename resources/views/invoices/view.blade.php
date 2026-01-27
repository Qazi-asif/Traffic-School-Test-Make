<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #007bff;
        }
        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 24px;
            color: #333;
            margin-top: 10px;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        .section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
        }
        .section h3 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .section p {
            margin: 8px 0;
            color: #555;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .invoice-table th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .invoice-table tr:hover {
            background: #f8f9fa;
        }
        .total-section {
            text-align: right;
            margin-top: 30px;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            padding: 8px 0;
            font-size: 16px;
        }
        .total-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            border-top: 2px solid #007bff;
            padding-top: 15px;
            margin-top: 10px;
        }
        .total-label {
            margin-right: 30px;
            min-width: 150px;
        }
        .total-value {
            min-width: 120px;
            text-align: right;
        }
        .actions {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-completed {
            background: #f4f6f0;
            color: #516425;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">DummiesTrafficSchool.com</div>
            <div style="color: #666; margin-top: 5px;">Professional Driver Education Services</div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <div class="invoice-details">
            <div class="section">
                <h3>Invoice Details</h3>
                <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</p>
                <p><strong>Status:</strong> <span class="status-badge status-completed">{{ ucfirst($invoice->payment->status) }}</span></p>
            </div>
            
            <div class="section">
                <h3>Bill To</h3>
                <p><strong>{{ $invoice->payment->user->first_name }} {{ $invoice->payment->user->last_name }}</strong></p>
                <p>{{ $invoice->payment->user->email }}</p>
                @if($invoice->payment->user->phone_1)
                    <p>{{ $invoice->payment->user->phone_1 }}-{{ $invoice->payment->user->phone_2 }}-{{ $invoice->payment->user->phone_3 }}</p>
                @endif
                @if($invoice->payment->user->mailing_address)
                    <p>{{ $invoice->payment->user->mailing_address }}</p>
                    <p>{{ $invoice->payment->user->city }}, {{ $invoice->payment->user->state }} {{ $invoice->payment->user->zip }}</p>
                @endif
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: center; width: 100px;">Quantity</th>
                    <th style="text-align: right; width: 120px;">Unit Price</th>
                    <th style="text-align: right; width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->items && count($invoice->items) > 0)
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item['description'] ?? 'Course Enrollment' }}</td>
                            <td style="text-align: center;">{{ $item['quantity'] ?? 1 }}</td>
                            <td style="text-align: right;">${{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                            <td style="text-align: right;">${{ number_format($item['total'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $invoice->payment->enrollment->course->title ?? 'Course Enrollment' }}</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right;">${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Tax ({{ number_format($invoice->tax_rate ?? 0, 2) }}%):</div>
                <div class="total-value">${{ number_format($invoice->tax_amount ?? 0, 2) }}</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">Total:</div>
                <div class="total-value">${{ number_format($invoice->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="section" style="margin-top: 30px;">
            <h3>Payment Information</h3>
            <p><strong>Payment Method:</strong> {{ ucfirst($invoice->payment->payment_method) }}</p>
            <p><strong>Transaction ID:</strong> {{ $invoice->payment->gateway_payment_id }}</p>
            <p><strong>Payment Date:</strong> {{ $invoice->payment->created_at->format('M d, Y h:i A') }}</p>
        </div>

        <div class="actions">
            <a href="{{ route('invoice.download', $invoice->id) }}" class="btn">Download PDF</a>
            <a href="{{ url('/my-payments') }}" class="btn btn-secondary">Back to Payments</a>
        </div>

        <div class="footer">
            <p>Thank you for choosing DummiesTrafficSchool.com!</p>
            <p>For questions about this invoice, please contact us at support@dummiestrafficschool.com</p>
        </div>
    </div>
</body>
</html>
