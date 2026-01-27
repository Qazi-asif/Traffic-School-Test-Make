<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Expired</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .license-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            text-align: center;
            padding: 3rem;
        }
        .license-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        .license-title {
            color: #495057;
            margin-bottom: 1rem;
        }
        .license-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .contact-button {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .contact-button:hover {
            transform: translateY(-2px);
            color: white;
        }
        .features-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="license-card">
        <div class="license-icon">
            <i class="fas fa-key"></i>
        </div>
        
        <h2 class="license-title">License Expired</h2>
        
        <p class="license-message">
            Your software license has expired. To continue using all features and receive 
            ongoing support, please renew your maintenance contract.
        </p>
        
        <div class="features-list">
            <h6 class="mb-3"><i class="fas fa-star text-warning"></i> Maintenance Contract Includes:</h6>
            <ul class="list-unstyled">
                <li><i class="fas fa-check text-success me-2"></i> Continued software access</li>
                <li><i class="fas fa-check text-success me-2"></i> Security updates & bug fixes</li>
                <li><i class="fas fa-check text-success me-2"></i> Technical support</li>
                <li><i class="fas fa-check text-success me-2"></i> New feature updates</li>
                <li><i class="fas fa-check text-success me-2"></i> Data backup assistance</li>
            </ul>
        </div>
        
        <a href="mailto:support@yourcompany.com?subject=License Renewal Request" class="contact-button">
            <i class="fas fa-envelope me-2"></i>Contact Support for Renewal
        </a>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-phone"></i> Call us: +1 (555) 123-4567<br>
                <i class="fas fa-clock"></i> Business Hours: Mon-Fri 9AM-5PM EST
            </small>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                License ID: {{ config('app.license_id', 'N/A') }}<br>
                Expired: {{ now()->format('M j, Y') }}
            </small>
        </div>
    </div>
</body>
</html>