<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .cancel-card { background: white; border-radius: 8px; padding: 40px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; }
        .cancel-icon { font-size: 64px; color: #dc2626; margin-bottom: 20px; }
        h1 { color: #dc2626; margin-bottom: 15px; }
        p { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .btn { display: inline-block; padding: 15px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.3s; margin: 0 10px; }
        .btn:hover { background: #1d4ed8; }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
    </style>
</head>
<body>
    <div class="cancel-card">
        <div class="cancel-icon">❌</div>
        <h1>Payment Cancelled</h1>
        <p>Your payment was cancelled. No charges have been made to your account. You can try again or contact support if you need assistance.</p>
        <a href="/courses" class="btn btn-secondary">Browse Courses</a>
        <a href="/my-enrollments" class="btn">My Enrollments</a>
    </div>
</body>
</html>
