<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info, .customer-info {
            width: 48%;
        }
        .invoice-info h3, .customer-info h3 {
            margin-top: 0;
            color: #007bff;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">DummiesTrafficSchool.com</div>
        <div>Professional Driver Education Services</div>
    </div>

    <div class="invoice-details">
        <div class="invoice-info">
            <h3>Invoice Details</h3>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : $invoice->invoice_date->format('M d, Y') }}</p>
            <p><strong>Payment Status:</strong> <span style="color: #516425;">{{ ucfirst($invoice->payment->status) }}</span></p>
        </div>
        
        <div class="customer-info">
            <h3>Bill To</h3>
            <p><strong>{{ $invoice->payment->user->first_name }} {{ $invoice->payment->user->last_name }}</strong></p>
            <p>{{ $invoice->payment->user->email }}</p>
            @if($invoice->payment->user->phone)
                <p>{{ $invoice->payment->user->phone }}</p>
            @endif
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: center;">Quantity</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Total</th>
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
                    <td>Course Enrollment Fee</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Subtotal: ${{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</strong></p>
        <p><strong>Tax ({{ number_format($invoice->tax_rate ?? 0, 2) }}%): ${{ number_format($invoice->tax_amount ?? 0, 2) }}</strong></p>
        <p class="total-amount">Total: ${{ number_format($invoice->total_amount, 2) }}</p>
    </div>

    <div class="footer">
        <p>Thank you for choosing DummiesTrafficSchool.com!</p>
        <p>For questions about this invoice, please contact us at support@dummiestrafficschool.com</p>
    </div>
</body>
</html>
