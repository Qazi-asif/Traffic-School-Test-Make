<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Mode Control</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status { 
            padding: 20px; 
            border-radius: 8px; 
            margin: 20px 0; 
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .enabled { 
            background: #ffebee; 
            color: #c62828; 
            border: 2px solid #ef5350;
        }
        .disabled { 
            background: #e8f5e8; 
            color: #2e7d32; 
            border: 2px solid #66bb6a;
        }
        button { 
            padding: 15px 30px; 
            font-size: 16px; 
            cursor: pointer; 
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .success { 
            color: #2e7d32; 
            font-weight: bold; 
            background: #e8f5e8;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .error { 
            color: #c62828; 
            font-weight: bold; 
            background: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn-enable {
            background: #d32f2f;
            color: white;
        }
        .btn-disable {
            background: #388e3c;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
            color: #666;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #message {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Maintenance Mode Control</h1>
        
        <div id="status-display" class="status {{ $isEnabled ? 'enabled' : 'disabled' }}">
            <span id="status-text">{{ $isEnabled ? '🔴 MAINTENANCE MODE ON' : '🟢 SITE ONLINE' }}</span>
        </div>

        <div id="message">
            @if($status === 'enabled')
                <div class="success">✓ Maintenance mode ENABLED - Normal users see 503 page</div>
            @elseif($status === 'disabled')
                <div class="success">✓ Maintenance mode DISABLED - Site is online</div>
            @endif

            @if($error)
                <div class="error">❌ {{ $error }}</div>
            @endif
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <span>Processing...</span>
        </div>

        <div class="actions">
            <button id="maintenance-btn" 
                    class="{{ $isEnabled ? 'btn-disable' : 'btn-enable' }}"
                    data-action="{{ $isEnabled ? 'disable' : 'enable' }}">
                <span id="btn-text">{{ $isEnabled ? '🟢 DISABLE Maintenance Mode' : '🔴 ENABLE Maintenance Mode' }}</span>
            </button>
        </div>

        <div class="footer">
            <p><strong>⚠️ Admin only - Keep this URL secret!</strong></p>
            <p><small>Laravel Maintenance Control Panel (AJAX Version)</small></p>
            <hr>
            <p><small><strong>Backup Control:</strong> <a href="/maintenance-direct-cbfbvib4767436667gdgdggdgfgfdfghdgh" target="_blank" style="color: #666;">Direct PHP Version</a> (works even if Laravel fails)</small></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Set up CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#maintenance-btn').click(function() {
                const action = $(this).data('action');
                const button = $(this);
                
                // Show loading state
                button.prop('disabled', true);
                $('#loading').show();
                $('#message').hide();
                
                // Make AJAX request
                $.ajax({
                    url: `/admin-maintenance-cbfbvib4767436667gdgdggdgfgfdfghdgh/${action}`,
                    method: 'POST',
                    success: function(response) {
                        // Update UI based on response
                        updateUI(response);
                        showMessage(response.message, 'success');
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        showMessage(errorMessage, 'error');
                    },
                    complete: function() {
                        // Hide loading state
                        $('#loading').hide();
                        $('#maintenance-btn').prop('disabled', false);
                    }
                });
            });

            function updateUI(response) {
                const isEnabled = response.enabled;
                const statusDisplay = $('#status-display');
                const statusText = $('#status-text');
                const button = $('#maintenance-btn');
                const btnText = $('#btn-text');

                if (isEnabled) {
                    // Maintenance mode is ON
                    statusDisplay.removeClass('disabled').addClass('enabled');
                    statusText.text('🔴 MAINTENANCE MODE ON');
                    button.removeClass('btn-enable').addClass('btn-disable');
                    button.data('action', 'disable');
                    btnText.text('🟢 DISABLE Maintenance Mode');
                } else {
                    // Maintenance mode is OFF
                    statusDisplay.removeClass('enabled').addClass('disabled');
                    statusText.text('🟢 SITE ONLINE');
                    button.removeClass('btn-disable').addClass('btn-enable');
                    button.data('action', 'enable');
                    btnText.text('🔴 ENABLE Maintenance Mode');
                }
            }

            function showMessage(message, type) {
                const messageDiv = $('#message');
                messageDiv.html(`<div class="${type}">` + (type === 'success' ? '✓ ' : '❌ ') + message + '</div>');
                messageDiv.show();
                
                // Auto-hide success messages after 3 seconds
                if (type === 'success') {
                    setTimeout(() => {
                        messageDiv.fadeOut();
                    }, 3000);
                }
            }
        });
    </script>
</body>
</html>