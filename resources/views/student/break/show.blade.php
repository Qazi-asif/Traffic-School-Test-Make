<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $breakSession->chapterBreak->break_title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .break-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .break-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
            padding: 40px;
        }
        
        .break-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .timer-display {
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            margin: 30px 0;
            font-family: 'Courier New', monospace;
        }
        
        .timer-expired {
            color: #28a745;
        }
        
        .progress-ring {
            width: 200px;
            height: 200px;
            margin: 20px auto;
        }
        
        .progress-ring-circle {
            stroke: #667eea;
            stroke-width: 8;
            fill: transparent;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dasharray 1s ease;
        }
        
        .progress-ring-bg {
            stroke: #e9ecef;
            stroke-width: 8;
            fill: transparent;
        }
        
        .break-message {
            font-size: 1.2rem;
            color: #666;
            margin: 20px 0;
            line-height: 1.6;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .btn-continue {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-skip {
            background: transparent;
            border: 2px solid #6c757d;
            padding: 10px 25px;
            border-radius: 50px;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .btn-skip:hover {
            background: #6c757d;
            color: white;
        }
        
        .break-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Particles Background -->
    <div class="floating-particles">
        <div class="particle" style="left: 10%; width: 20px; height: 20px; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; width: 15px; height: 15px; animation-delay: 1s;"></div>
        <div class="particle" style="left: 30%; width: 25px; height: 25px; animation-delay: 2s;"></div>
        <div class="particle" style="left: 40%; width: 18px; height: 18px; animation-delay: 3s;"></div>
        <div class="particle" style="left: 50%; width: 22px; height: 22px; animation-delay: 4s;"></div>
        <div class="particle" style="left: 60%; width: 16px; height: 16px; animation-delay: 5s;"></div>
        <div class="particle" style="left: 70%; width: 24px; height: 24px; animation-delay: 6s;"></div>
        <div class="particle" style="left: 80%; width: 19px; height: 19px; animation-delay: 7s;"></div>
        <div class="particle" style="left: 90%; width: 21px; height: 21px; animation-delay: 8s;"></div>
    </div>

    <div class="break-container">
        <div class="break-card">
            <div class="break-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            
            <h1 class="mb-3">{{ $breakSession->chapterBreak->break_title }}</h1>
            
            @if($breakSession->chapterBreak->break_message)
                <div class="break-message">
                    {{ $breakSession->chapterBreak->break_message }}
                </div>
            @else
                <div class="break-message">
                    Take a moment to rest and reflect on what you've learned. 
                    This break will help you absorb the material better.
                </div>
            @endif
            
            <!-- Progress Ring -->
            <svg class="progress-ring" viewBox="0 0 200 200">
                <circle class="progress-ring-bg" cx="100" cy="100" r="90"></circle>
                <circle class="progress-ring-circle" cx="100" cy="100" r="90" 
                        stroke-dasharray="565.48" stroke-dashoffset="0" id="progress-circle"></circle>
            </svg>
            
            <!-- Timer Display -->
            <div class="timer-display" id="timer-display">
                {{ $breakSession->formatted_remaining_time }}
            </div>
            
            <!-- Break Info -->
            <div class="break-info">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Break Type</small>
                        <div class="fw-bold">
                            <i class="fas fa-{{ $breakSession->chapterBreak->is_mandatory ? 'lock' : 'unlock' }} me-1"></i>
                            {{ $breakSession->chapterBreak->is_mandatory ? 'Mandatory' : 'Optional' }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Total Duration</small>
                        <div class="fw-bold">
                            <i class="fas fa-clock me-1"></i>
                            {{ $breakSession->chapterBreak->formatted_duration }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button id="continue-btn" class="btn btn-continue me-3" onclick="continueFromBreak()" disabled>
                    <i class="fas fa-play me-2"></i>Continue Learning
                </button>
                
                @if(!$breakSession->chapterBreak->is_mandatory)
                    <button id="skip-btn" class="btn btn-skip" onclick="skipBreak()">
                        <i class="fas fa-forward me-2"></i>Skip Break
                    </button>
                @endif
            </div>
            
            <!-- Status Messages -->
            <div id="status-message" class="mt-3"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let breakSessionId = {{ $breakSession->id }};
        let totalDurationMinutes = {{ $breakSession->chapterBreak->total_duration_minutes }};
        let isMandatory = {{ $breakSession->chapterBreak->is_mandatory ? 'true' : 'false' }};
        let updateInterval;
        
        // Initialize progress ring
        const circle = document.getElementById('progress-circle');
        const circumference = 2 * Math.PI * 90; // radius = 90
        circle.style.strokeDasharray = circumference;
        
        function updateBreakStatus() {
            fetch(`/student/break/${breakSessionId}/status`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update timer display
                document.getElementById('timer-display').textContent = data.formatted_remaining_time;
                
                // Update progress ring
                const progress = Math.max(0, (totalDurationMinutes - data.remaining_minutes) / totalDurationMinutes);
                const offset = circumference - (progress * circumference);
                circle.style.strokeDashoffset = offset;
                
                // Update continue button
                const continueBtn = document.getElementById('continue-btn');
                if (data.can_continue) {
                    continueBtn.disabled = false;
                    continueBtn.innerHTML = '<i class="fas fa-play me-2"></i>Continue Learning';
                    document.getElementById('timer-display').classList.add('timer-expired');
                    
                    if (data.is_expired) {
                        showStatusMessage('Break time completed! You can now continue.', 'success');
                    }
                } else {
                    continueBtn.disabled = true;
                    const remainingText = isMandatory ? 'Please wait...' : 'Continue Learning';
                    continueBtn.innerHTML = `<i class="fas fa-clock me-2"></i>${remainingText}`;
                }
                
                // If break is completed, redirect
                if (data.is_completed || data.was_skipped) {
                    clearInterval(updateInterval);
                    window.location.href = `/course/continue?enrollment_id={{ $breakSession->enrollment_id }}`;
                }
            })
            .catch(error => {
                console.error('Error updating break status:', error);
            });
        }
        
        function continueFromBreak() {
            fetch(`/student/break/${breakSessionId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatusMessage('Continuing to next chapter...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    showStatusMessage(data.message, 'warning');
                }
            })
            .catch(error => {
                console.error('Error completing break:', error);
                showStatusMessage('Error occurred. Please try again.', 'danger');
            });
        }
        
        function skipBreak() {
            if (!confirm('Are you sure you want to skip this break?')) {
                return;
            }
            
            fetch(`/student/break/${breakSessionId}/skip`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatusMessage('Break skipped. Continuing to next chapter...', 'info');
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    showStatusMessage(data.message, 'warning');
                }
            })
            .catch(error => {
                console.error('Error skipping break:', error);
                showStatusMessage('Error occurred. Please try again.', 'danger');
            });
        }
        
        function showStatusMessage(message, type) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            
            setTimeout(() => {
                statusDiv.innerHTML = '';
            }, 5000);
        }
        
        // Start updating break status every second
        updateBreakStatus(); // Initial call
        updateInterval = setInterval(updateBreakStatus, 1000);
        
        // Prevent page refresh/close during mandatory breaks
        if (isMandatory) {
            window.addEventListener('beforeunload', function(e) {
                e.preventDefault();
                e.returnValue = 'Your break is still in progress. Are you sure you want to leave?';
            });
        }
    </script>
</body>
</html>