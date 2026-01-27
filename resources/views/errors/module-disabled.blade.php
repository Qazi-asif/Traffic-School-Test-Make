<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Disabled</title>
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
        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 2rem;
        }
        .error-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .error-title {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        .error-card {
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
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 1rem;
        }
        .admin-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .module-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 4px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon pulse">
                <i class="fas fa-ban"></i>
            </div>
            <h1 class="error-title">Module Disabled</h1>
            <div class="module-info">
                <strong>{{ ucfirst(str_replace('_', ' ', $module)) }}</strong> module is currently disabled
            </div>
            <p class="error-message">
                This feature has been temporarily disabled by the system administrator. 
                Please contact support if you need access to this functionality.
            </p>
            
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                <a href="/" class="admin-link">
                    <i class="fas fa-home me-2"></i>Return Home
                </a>
                <a href="/dashboard" class="admin-link">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    Error Code: MODULE_{{ strtoupper($module) }}_DISABLED
                </small>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds to check if module is re-enabled
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>