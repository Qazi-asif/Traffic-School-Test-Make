<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .maintenance-container {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 2rem;
        }
        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 1rem;
        }
        .maintenance-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        .maintenance-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        .admin-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-card">
            <div class="maintenance-icon pulse">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="maintenance-title">Site Under Maintenance</h1>
            <p class="maintenance-message">
                {{ $message ?? 'We are currently performing scheduled maintenance to improve your experience. Please check back later.' }}
            </p>
            <div class="d-flex justify-content-center align-items-center">
                <div class="spinner-border text-light me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>We'll be back soon...</span>
            </div>
        </div>
    </div>

    <a href="/admin/settings" class="admin-link">
        <i class="fas fa-cog me-2"></i>Admin Access
    </a>

    <script>
        // Auto-refresh every 30 seconds to check if maintenance is over
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>