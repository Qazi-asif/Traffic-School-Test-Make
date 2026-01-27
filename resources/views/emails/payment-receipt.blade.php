<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.4; 
            color: #333; 
            margin: 0; 
            padding: 20px;
            background-color: #f5f5f5;
        }
        .receipt-container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: white;
            border: 2px solid #000;
            padding: 0;
        }
        .header { 
            background: #ffffff; 
            padding: 20px; 
            text-align: center; 
            border-bottom: 2px solid #000;
        }
        .header h1 { 
            margin: 0 0 10px 0; 
            font-size: 24px; 
            font-weight: bold;
            color: #000;
        }
        .header h2 { 
            margin: 0 0 15px 0; 
            font-size: 18px; 
            font-weight: bold;
            color: #000;
        }
        .company-info { 
            font-size: 12px; 
            line-height: 1.3;
            margin-bottom: 10px;
        }
        .content { 
            padding: 20px; 
        }
        .customer-info { 
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.4;
        }
        .course-info { 
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .transaction-details { 
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .services-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
            font-size: 14px;
        }
        .services-table th { 
            background: #f0f0f0; 
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ccc;
            font-weight: bold;
        }
        .services-table td { 
            padding: 8px; 
            border: 1px solid #ccc;
        }
        .total-row { 
            font-weight: bold;
            background: #f9f9f9;
        }
        .footer { 
            margin-top: 30px; 
            font-size: 12px; 
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .highlight { 
            font-weight: bold; 
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Payment Receipt</h1>
            <h2>DummiesTrafficSchool.com</h2>
            <div class="company-info">
                4235 Hillsboro Pike. #300644<br>
                Nashville, TN 37215<br>
                (877) 382-3700<br>
                Monday to Friday 8AM to 4PM<br>
                Support@DummiesTrafficSchool.com
            </div>
        </div>

        <div class="content">
            <!-- Customer Information -->
            <div class="customer-info">
                <strong>{{ $payment->billing_name ?? ($payment->user->first_name . ' ' . $payment->user->last_name) }}</strong><br>
                @if($payment->billing_address_1 ?? $payment->user->mailing_address ?? $payment->user->address)
                    {{ $payment->billing_address_1 ?? $payment->user->mailing_address ?? $payment->user->address }}<br>
                @endif
                @if($payment->billing_city ?? $payment->user->city)
                    {{ $payment->billing_city ?? $payment->user->city }}, 
                @endif
                @if($payment->billing_state ?? $payment->user->state)
                    {{ $payment->billing_state ?? $payment->user->state }} 
                @endif
                @if($payment->billing_zip ?? $payment->user->zip)
                    {{ $payment->billing_zip ?? $payment->user->zip }}
                @endif
            </div>

            <!-- Course Information -->
            <div class="course-info">
                @if(isset($course))
                    {{ $course->title ?? 'Course' }} (${{ number_format($payment->amount, 2) }})
                @else
                    Traffic School Course (${{ number_format($payment->amount, 2) }})
                @endif
            </div>

            <!-- Certificate Information -->
            <div style="margin-bottom: 20px; font-size: 14px;">
                <strong>Certificate:</strong> We will electronically send your completion report
            </div>

            <!-- Transaction Details -->
            <div class="transaction-details">
                <strong>Date of Transaction:</strong> {{ $payment->created_at->format('m/d/Y') }}<br>
                <strong>The amount of the transaction:</strong> ${{ number_format($payment->amount, 2) }}<br>
                <strong>Transaction Type:</strong> "Sale" ({{ ucfirst($payment->payment_method) }})<br>
                @if($payment->gateway_payment_id)
                    <strong>Account #:</strong> {{ substr($payment->gateway_payment_id, -4) }}<br>
                @endif
                <strong>Location of Transaction:</strong> Nashville
            </div>

            <!-- Services Ordered -->
            <div style="margin-bottom: 15px; font-size: 14px; font-weight: bold;">
                Here are the services you ordered:
            </div>

            <table class="services-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Service</th>
                        <th style="width: 20%; text-align: right;">Price</th>
                        <th style="width: 20%; text-align: right;">Discount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Flat Rate Course</td>
                        <td style="text-align: right;">${{ number_format($payment->amount, 2) }}</td>
                        <td style="text-align: right;">$0.00</td>
                    </tr>
                    <tr>
                        <td>Cert Verify Service</td>
                        <td style="text-align: right;">$0.00</td>
                        <td style="text-align: right;">-$0.00</td>
                    </tr>
                    @if(isset($payment->coupon_code) && $payment->coupon_code)
                    <tr>
                        <td>Discount Coupon ({{ $payment->coupon_code }})</td>
                        <td style="text-align: right;">-${{ number_format($payment->discount_amount ?? 0, 2) }}</td>
                        <td style="text-align: right;">-$0.00</td>
                    </tr>
                    @else
                    <tr>
                        <td>Discount Coupon</td>
                        <td style="text-align: right;">-$0.00</td>
                        <td style="text-align: right;">-$0.00</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td><strong>Total</strong></td>
                        <td style="text-align: right;"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                        <td style="text-align: right;"><strong>$0.00</strong></td>
                    </tr>
                </tbody>
            </table>

            <!-- Footer -->
            <div class="footer">
                <p>Thank you for choosing DummiesTrafficSchool.com!</p>
                <p>For support, please contact us at Support@DummiesTrafficSchool.com or (877) 382-3700</p>
                <p><a href="https://www.dummiestrafficschool.com" style="color: #333;">www.dummiestrafficschool.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
