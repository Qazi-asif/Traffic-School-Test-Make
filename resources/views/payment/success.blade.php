<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .success-card { background: white; border-radius: 8px; padding: 40px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; }
        .success-icon { font-size: 64px; color: #516425; margin-bottom: 20px; }
        h1 { color: #516425; margin-bottom: 15px; }
        p { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .btn { display: inline-block; padding: 15px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.3s; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">✅</div>
        <h1>Payment Successful!</h1>
        <p>Thank you for your payment. Your enrollment has been confirmed and you can now access your course.</p>
        <a href="/my-enrollments" class="btn">View My Courses</a>
    </div>
</body>
</html>
