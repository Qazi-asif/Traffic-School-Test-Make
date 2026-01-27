<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #516425;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #ddd;
        }
        .code-box {
            background: white;
            border: 2px solid #516425;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #516425;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Two-Factor Authentication</h1>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->first_name ?? 'User' }},</h2>
        
        <p>You have requested to log in to your account. To complete the login process, please use the verification code below:</p>
        
        <div class="code-box">
            <div class="code">{{ $code }}</div>
            <p style="margin: 10px 0 0 0; color: #666;">Enter this code to verify your identity</p>
        </div>
        
        <div class="warning">
            <strong>Important Security Information:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>This code will expire in <strong>10 minutes</strong></li>
                <li>Never share this code with anyone</li>
                <li>If you didn't request this code, please secure your account immediately</li>
                <li>This code can only be used once</li>
            </ul>
        </div>
        
        <p>If you didn't attempt to log in, please:</p>
        <ul>
            <li>Change your password immediately</li>
            <li>Contact our support team</li>
            <li>Review your account activity</li>
        </ul>
        
        <p>For security reasons, this email was sent from an automated system. Please do not reply to this email.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} {{ config('app.name', 'Traffic School') }}</p>
        <p>This is an automated security message.</p>
    </div>
</body>
</html>