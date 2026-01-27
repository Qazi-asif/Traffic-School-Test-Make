<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            background-color: #f5f5f5;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .email-wrapper {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header { 
            background: linear-gradient(135deg, #556B2F 0%, #6B8E23 100%);
            color: white; 
            padding: 40px 20px; 
            text-align: center; 
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .header p {
            font-size: 16px;
            opacity: 0.95;
        }
        .content { 
            background: #fafafa; 
            padding: 40px 30px; 
        }
        .content p {
            margin-bottom: 15px;
            color: #555;
        }
        .content h2 { 
            color: #556B2F;
            font-size: 22px;
            margin: 25px 0 15px 0;
            font-weight: 600;
        }
        .content h3 {
            color: #6B8E23;
            font-size: 18px;
            margin: 20px 0 10px 0;
            font-weight: 600;
        }
        .details { 
            background: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-left: 5px solid #DAA520;
            border-radius: 4px;
        }
        .details p {
            margin: 8px 0;
        }
        .details strong {
            color: #556B2F;
        }
        .button { 
            display: inline-block; 
            background: linear-gradient(135deg, #6B8E23 0%, #556B2F 100%);
            color: white; 
            padding: 14px 32px; 
            text-decoration: none; 
            border-radius: 6px; 
            margin: 20px 10px 20px 0;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .button-secondary {
            background: linear-gradient(135deg, #DAA520 0%, #B8860B 100%);
        }
        .button-success {
            background: linear-gradient(135deg, #228B22 0%, #1a6b1a 100%);
        }
        .button-dark-blue {
            background: linear-gradient(135deg, #003d99 0%, #002966 100%);
        }
        .achievement-box { 
            background: white; 
            padding: 25px; 
            border-radius: 6px; 
            margin: 20px 0; 
            text-align: center; 
            border: 2px solid #6B8E23;
        }
        .achievement-box h2 {
            margin: 0 0 15px 0;
            color: #556B2F;
        }
        .stats { 
            display: flex; 
            justify-content: space-around; 
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .stat { 
            text-align: center; 
            flex: 1;
            min-width: 100px;
            padding: 10px;
        }
        .stat-value { 
            font-size: 24px; 
            font-weight: bold; 
            color: #6B8E23;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .alert {
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .alert-info {
            background: #e8f4f8;
            border-left: 4px solid #6B8E23;
            color: #333;
        }
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #DAA520;
            color: #333;
        }
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #228B22;
            color: #333;
        }
        .footer { 
            text-align: center; 
            padding: 20px; 
            color: #999; 
            font-size: 12px;
            background: #f5f5f5;
            border-top: 1px solid #eee;
        }
        .divider {
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #999;
            font-size: 14px;
        }
        .highlight {
            color: #556B2F;
            font-weight: 600;
        }
        .accent-gold {
            color: #DAA520;
        }
        .accent-green {
            color: #228B22;
        }
        .text-dark-blue {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            @yield('content')
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'E-Learning Platform') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">If you have any questions, please contact our support team.</p>
            </div>
        </div>
    </div>
</body>
</html>
