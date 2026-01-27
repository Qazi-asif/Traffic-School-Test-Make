<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSRF Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }
        button { 
            background: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        button:hover { background: #0056b3; }
        .info { 
            background: #e7f3ff; 
            border: 1px solid #b6d7ff; 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
        }
        .token-info { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            font-family: monospace; 
            font-size: 12px; 
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>CSRF Token Test</h1>
        
        <div class="info">
            <strong>Test Instructions:</strong><br>
            1. Fill out the form below<br>
            2. Wait 5+ minutes (or manually expire session)<br>
            3. Submit the form to test CSRF error handling<br>
            4. The form should automatically retry with a fresh token
        </div>
        
        <div class="token-info">
            <strong>Current CSRF Token:</strong><br>
            <span id="current-token">{{ csrf_token() }}</span><br>
            <small>Last updated: <span id="token-timestamp">{{ now() }}</span></small>
        </div>
        
        @if(session('success'))
            <div style="background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <strong>Success:</strong> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <strong>Validation Errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="/test-csrf">
            @csrf
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            
            <div class="form-group">
                <label for="message">Message:</label>
                <input type="text" id="message" name="message" value="{{ old('message') }}" required>
            </div>
            
            <button type="submit">Submit Test Form</button>
        </form>
        
        <div style="margin-top: 30px;">
            <button onclick="refreshToken()" style="background: #28a745;">Refresh CSRF Token</button>
            <button onclick="expireSession()" style="background: #dc3545;">Simulate Session Expiry</button>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="/login">← Back to Login</a> | 
            <a href="/register">Go to Registration</a>
        </div>
    </div>
    
    <script src="/js/csrf-handler.js"></script>
    <script>
        async function refreshToken() {
            try {
                const response = await fetch('/api/csrf-token');
                const data = await response.json();
                document.getElementById('current-token').textContent = data.csrf_token;
                document.getElementById('token-timestamp').textContent = new Date().toLocaleString();
                alert('CSRF token refreshed successfully!');
            } catch (error) {
                alert('Failed to refresh token: ' + error.message);
            }
        }
        
        function expireSession() {
            // This would normally require server-side session manipulation
            // For testing, we can manually change the token to an invalid one
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                tokenInput.value = 'expired_token_for_testing';
                alert('Token set to invalid value for testing. Try submitting the form now.');
            }
        }
        
        // Update token display every 30 seconds
        setInterval(() => {
            const timestamp = document.getElementById('token-timestamp');
            if (timestamp) {
                timestamp.textContent = new Date().toLocaleString();
            }
        }, 30000);
    </script>
</body>
</html>