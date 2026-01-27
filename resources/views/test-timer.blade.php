<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Strict Timer Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/strict-timer.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Strict Timer System Test</h3>
                    </div>
                    <div class="card-body">
                        <!-- Timer Display -->
                        <div class="alert alert-info mb-3" id="timer-display" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Chapter Timer:</strong> <span id="timer-text">00:00</span>
                                    <span class="ms-3 text-muted">Required: <span id="required-time">0</span> minutes</span>
                                </div>
                                <div>
                                    <span class="badge bg-warning" id="timer-status">In Progress</span>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar" id="timer-progress" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Test Instructions</h5>
                            <p>This page tests the strict timer system with maximum security restrictions:</p>
                            <ul>
                                <li><strong>Tab Switching:</strong> Try switching to another tab - it will be detected</li>
                                <li><strong>Right-Click:</strong> Right-click is disabled and logged</li>
                                <li><strong>Developer Tools:</strong> Opening dev tools (F12) is detected</li>
                                <li><strong>Keyboard Shortcuts:</strong> Ctrl+R, F5, Ctrl+U, etc. are blocked</li>
                                <li><strong>Page Reload:</strong> Attempting to reload shows a warning</li>
                                <li><strong>Time Manipulation:</strong> System clock changes are detected</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label for="test-chapter-id" class="form-label">Test Chapter ID:</label>
                            <input type="number" id="test-chapter-id" class="form-control" value="1" min="1">
                            <small class="text-muted">Enter a chapter ID that has a timer configured</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button id="start-timer-btn" class="btn btn-primary" onclick="startTestTimer()">
                                Start Timer Test
                            </button>
                            <button id="stop-timer-btn" class="btn btn-danger" onclick="stopTestTimer()" style="display: none;">
                                Stop Timer Test
                            </button>
                        </div>

                        <div class="mt-4">
                            <h6>Violation Log:</h6>
                            <div id="violation-log" class="border p-3" style="height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                                <small class="text-muted">Violations will appear here...</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h6>Timer Status:</h6>
                            <div id="timer-status-log" class="border p-3" style="background-color: #f8f9fa;">
                                <small class="text-muted">Timer status updates will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let testTimer = null;
        
        async function startTestTimer() {
            const chapterId = document.getElementById('test-chapter-id').value;
            
            if (!chapterId) {
                alert('Please enter a chapter ID');
                return;
            }
            
            try {
                logStatus('Starting timer test for chapter ' + chapterId + '...');
                
                const result = await window.strictTimer.startTimer(chapterId);
                
                if (result.success) {
                    if (result.timer_required) {
                        logStatus('✅ Timer started successfully! Strict mode activated.');
                        document.getElementById('start-timer-btn').style.display = 'none';
                        document.getElementById('stop-timer-btn').style.display = 'block';
                        
                        // Override the violation recording to show in our log
                        const originalRecordViolation = window.strictTimer.recordViolation;
                        window.strictTimer.recordViolation = function(type, details) {
                            originalRecordViolation.call(this, type, details);
                            logViolation(type, details);
                        };
                        
                    } else {
                        logStatus('ℹ️ No timer required for this chapter.');
                    }
                } else {
                    logStatus('❌ Failed to start timer: ' + result.error);
                }
            } catch (error) {
                logStatus('❌ Error: ' + error.message);
                console.error('Timer test error:', error);
            }
        }
        
        function stopTestTimer() {
            if (window.strictTimer) {
                window.strictTimer.stopTimer();
                logStatus('Timer stopped by user');
                document.getElementById('start-timer-btn').style.display = 'block';
                document.getElementById('stop-timer-btn').style.display = 'none';
            }
        }
        
        function logViolation(type, details) {
            const log = document.getElementById('violation-log');
            const timestamp = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = 'mb-1';
            entry.innerHTML = `
                <small>
                    <span class="badge bg-danger">${timestamp}</span>
                    <strong>${type.toUpperCase()}:</strong> 
                    ${typeof details === 'object' ? JSON.stringify(details) : details}
                </small>
            `;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }
        
        function logStatus(message) {
            const log = document.getElementById('timer-status-log');
            const timestamp = new Date().toLocaleTimeString();
            log.innerHTML = `
                <small>
                    <span class="badge bg-info">${timestamp}</span>
                    ${message}
                </small>
            `;
        }
        
        // Test violation triggers
        document.addEventListener('DOMContentLoaded', function() {
            // Add test buttons for manual violation testing
            const testButtons = document.createElement('div');
            testButtons.className = 'mt-3';
            testButtons.innerHTML = `
                <h6>Manual Violation Tests:</h6>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-warning" onclick="testTabSwitch()">Test Tab Switch</button>
                    <button type="button" class="btn btn-outline-info" onclick="testWindowBlur()">Test Window Blur</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="testContextMenu()">Test Right Click</button>
                </div>
            `;
            document.querySelector('.card-body').appendChild(testButtons);
        });
        
        function testTabSwitch() {
            if (window.strictTimer && window.strictTimer.isActive) {
                window.strictTimer.recordViolation('tab_switch', {
                    timestamp: Date.now(),
                    type: 'manual_test'
                });
                logStatus('Manual tab switch violation triggered');
            } else {
                alert('Timer is not active. Start the timer first.');
            }
        }
        
        function testWindowBlur() {
            if (window.strictTimer && window.strictTimer.isActive) {
                window.strictTimer.recordViolation('window_blur', {
                    timestamp: Date.now(),
                    type: 'manual_test'
                });
                logStatus('Manual window blur violation triggered');
            } else {
                alert('Timer is not active. Start the timer first.');
            }
        }
        
        function testContextMenu() {
            if (window.strictTimer && window.strictTimer.isActive) {
                window.strictTimer.recordViolation('context_menu', {
                    timestamp: Date.now(),
                    type: 'manual_test'
                });
                logStatus('Manual context menu violation triggered');
            } else {
                alert('Timer is not active. Start the timer first.');
            }
        }
        
        // Show timer violations count
        setInterval(() => {
            if (window.strictTimer && window.strictTimer.isActive) {
                const counts = window.strictTimer.getViolationCount();
                document.getElementById('timer-status-log').innerHTML += `
                    <br><small class="text-muted">
                        Violations: Tab switches: ${counts.tabSwitches}, 
                        Page reloads: ${counts.pageReloads}, 
                        Focus losses: ${counts.focusLosses}, 
                        Total: ${counts.totalViolations}
                    </small>
                `;
            }
        }, 10000); // Update every 10 seconds
    </script>
</body>
</html>