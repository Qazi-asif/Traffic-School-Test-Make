/**
 * Strict Timer System - Maximum Security Implementation
 * Prevents users from bypassing chapter timers through various methods
 */

class StrictTimer {
    constructor() {
        this.sessionId = null;
        this.sessionToken = null;
        this.chapterId = null;
        this.requiredTime = 0;
        this.elapsedTime = 0;
        this.startTime = null;
        this.interval = null;
        this.heartbeatInterval = null;
        this.isActive = false;
        this.violations = [];
        this.browserFingerprint = this.generateBrowserFingerprint();
        this.tabSwitches = 0;
        this.pageReloads = 0;
        this.focusLosses = 0;
        
        // Bind event listeners
        this.bindEvents();
        
        // Prevent common bypass methods
        this.preventBypassMethods();
        
        // Start monitoring immediately
        this.startMonitoring();
    }

    generateBrowserFingerprint() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('Browser fingerprint', 2, 2);
        
        return btoa(JSON.stringify({
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            cookieEnabled: navigator.cookieEnabled,
            screenResolution: `${screen.width}x${screen.height}`,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            canvas: canvas.toDataURL(),
            timestamp: Date.now()
        }));
    }

    bindEvents() {
        // Detect tab switches and window focus changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.recordViolation('tab_switch', {
                    timestamp: Date.now(),
                    type: 'visibility_hidden'
                });
                this.tabSwitches++;
            }
        });

        // Detect window blur/focus
        window.addEventListener('blur', () => {
            this.recordViolation('window_blur', {
                timestamp: Date.now(),
                type: 'window_blur'
            });
            this.focusLosses++;
        });

        // Detect page reload attempts
        window.addEventListener('beforeunload', (e) => {
            if (this.isActive) {
                this.recordViolation('page_reload', {
                    timestamp: Date.now(),
                    type: 'before_unload'
                });
                this.pageReloads++;
                
                // Try to save current progress
                this.saveProgress(true);
                
                // Show warning message
                const message = 'Timer is still running! Leaving this page will be recorded as a violation.';
                e.returnValue = message;
                return message;
            }
        });

        // Detect right-click context menu
        document.addEventListener('contextmenu', (e) => {
            if (this.isActive) {
                e.preventDefault();
                this.recordViolation('context_menu', {
                    timestamp: Date.now()
                });
                this.showWarning('Right-click is disabled during timer sessions.');
            }
        });

        // Detect developer tools
        let devtools = {open: false, orientation: null};
        const threshold = 160;
        
        setInterval(() => {
            if (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    this.recordViolation('devtools_opened', {
                        timestamp: Date.now(),
                        outerDimensions: `${window.outerWidth}x${window.outerHeight}`,
                        innerDimensions: `${window.innerWidth}x${window.innerHeight}`
                    });
                    this.showWarning('Developer tools detected! This action has been logged.');
                }
            } else {
                devtools.open = false;
            }
        }, 500);

        // Detect key combinations that might be used to bypass
        document.addEventListener('keydown', (e) => {
            if (this.isActive) {
                // Prevent F12, Ctrl+Shift+I, Ctrl+U, etc.
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                    (e.ctrlKey && e.key === 'u') ||
                    (e.ctrlKey && e.shiftKey && e.key === 'C') ||
                    (e.ctrlKey && e.key === 'r') ||
                    (e.key === 'F5')) {
                    
                    e.preventDefault();
                    this.recordViolation('blocked_shortcut', {
                        timestamp: Date.now(),
                        key: e.key,
                        ctrlKey: e.ctrlKey,
                        shiftKey: e.shiftKey
                    });
                    this.showWarning('Keyboard shortcuts are disabled during timer sessions.');
                }
            }
        });
    }

    preventBypassMethods() {
        // Disable text selection
        document.onselectstart = () => this.isActive ? false : true;
        document.ondragstart = () => this.isActive ? false : true;

        // Override console methods to detect script injection
        const originalLog = console.log;
        console.log = (...args) => {
            if (this.isActive && args.some(arg => 
                typeof arg === 'string' && 
                (arg.includes('timer') || arg.includes('bypass')))) {
                this.recordViolation('console_manipulation', {
                    timestamp: Date.now(),
                    args: args.map(arg => String(arg))
                });
            }
            return originalLog.apply(console, args);
        };

        // Prevent iframe injection - wait for DOM to be ready
        const setupIframeObserver = () => {
            if (document.body) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'childList') {
                            mutation.addedNodes.forEach((node) => {
                                if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'IFRAME' && this.isActive) {
                                    this.recordViolation('iframe_injection', {
                                        timestamp: Date.now(),
                                        src: node.src
                                    });
                                    node.remove();
                                }
                            });
                        }
                    });
                });
                observer.observe(document.body, { childList: true, subtree: true });
            } else {
                // If body is not ready, wait for DOM content loaded
                document.addEventListener('DOMContentLoaded', () => {
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.type === 'childList') {
                                mutation.addedNodes.forEach((node) => {
                                    if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'IFRAME' && this.isActive) {
                                        this.recordViolation('iframe_injection', {
                                            timestamp: Date.now(),
                                            src: node.src
                                        });
                                        node.remove();
                                    }
                                });
                            }
                        });
                    });
                    observer.observe(document.body, { childList: true, subtree: true });
                });
            }
        };
        setupIframeObserver();
    }

    startMonitoring() {
        // Monitor for time manipulation
        let lastTime = Date.now();
        setInterval(() => {
            const currentTime = Date.now();
            const timeDiff = currentTime - lastTime;
            
            // If time jumps more than 2 seconds, it might be manipulation
            if (timeDiff > 2500 && this.isActive) {
                this.recordViolation('time_manipulation', {
                    timestamp: currentTime,
                    expectedDiff: 1000,
                    actualDiff: timeDiff
                });
            }
            lastTime = currentTime;
        }, 1000);

        // Monitor for multiple tabs
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data.type === 'MULTIPLE_TABS_DETECTED' && this.isActive) {
                    this.recordViolation('multiple_tabs', {
                        timestamp: Date.now(),
                        tabCount: event.data.tabCount
                    });
                    this.showWarning('Multiple tabs detected! Please use only one tab for the course.');
                }
            });
        }
    }

    async startTimer(chapterId, enrollmentId = null, chapterDurationMinutes = null) {
        console.log('=== Starting Timer ===');
        console.log('Chapter ID:', chapterId);
        console.log('Chapter Duration (minutes):', chapterDurationMinutes);
        console.log('Browser fingerprint:', this.browserFingerprint);
        
        try {
            this.chapterId = chapterId;
            
            const requestData = {
                chapter_id: chapterId,
                browser_fingerprint: this.browserFingerprint,
                enrollment_id: enrollmentId
            };
            
            console.log('Request data:', requestData);
            
            const response = await fetch('/api/timer/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('HTTP Error:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const data = await response.json();
            console.log('Response data:', data);
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to start timer');
            }

            // Check if timer is required OR if strict duration is globally enabled
            const strictDurationEnabled = window.strictDurationEnabled || false;
            
            if (!data.session && !strictDurationEnabled) {
                console.log('No timer required for this chapter and strict duration not enabled');
                return { success: true, timer_required: false };
            }

            // If we have a session, use it
            if (data.session) {
                this.sessionId = data.session.id;
                this.sessionToken = data.session_token || data.session.session_token;
                this.requiredTime = data.required_time;
                this.elapsedTime = data.elapsed_time || 0;
                this.isActive = true;

                console.log('Timer activated:', {
                    sessionId: this.sessionId,
                    sessionToken: this.sessionToken,
                    requiredTime: this.requiredTime,
                    elapsedTime: this.elapsedTime
                });

                // Start the timer display and countdown
                this.startCountdown();
                this.startHeartbeat();
                this.showTimerDisplay();

                return { success: true, timer_required: true };
            }
            
            // If strict duration is enabled but no specific timer, use chapter duration or default
            if (strictDurationEnabled) {
                // Use chapter duration if provided, otherwise use 5 minute default
                const durationMinutes = chapterDurationMinutes || 5;
                this.requiredTime = durationMinutes * 60;
                this.elapsedTime = 0;
                this.isActive = true;
                
                console.log('Strict duration enabled - using chapter duration:', durationMinutes, 'minutes');
                
                this.startCountdown();
                this.showTimerDisplay();
                return { success: true, timer_required: true };
            }

            return { success: true, timer_required: false };

        } catch (error) {
            console.error('Timer start error:', error);
            this.showError('Failed to start timer: ' + error.message);
            return { success: false, error: error.message };
        }
    }

    startCountdown() {
        this.startTime = Date.now() - (this.elapsedTime * 1000);
        
        this.interval = setInterval(() => {
            if (!this.isActive) return;
            
            this.elapsedTime = Math.floor((Date.now() - this.startTime) / 1000);
            this.updateDisplay();
            
            // Save progress every 30 seconds
            if (this.elapsedTime % 30 === 0) {
                this.saveProgress();
            }
            
            // Check if timer is complete
            if (this.elapsedTime >= this.requiredTime) {
                this.completeTimer();
            }
        }, 1000);
    }

    startHeartbeat() {
        // Send heartbeat every 15 seconds to detect if user is still active
        this.heartbeatInterval = setInterval(() => {
            if (this.isActive) {
                this.sendHeartbeat();
            }
        }, 15000);
    }

    async sendHeartbeat() {
        try {
            await fetch('/api/timer/heartbeat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    session_token: this.sessionToken,
                    timestamp: Date.now()
                })
            });
        } catch (error) {
            console.error('Heartbeat failed:', error);
        }
    }

    async saveProgress(isUnloading = false) {
        if (!this.sessionId) return;

        const data = {
            session_id: this.sessionId,
            session_token: this.sessionToken,
            time_spent: this.elapsedTime,
            browser_fingerprint: this.browserFingerprint,
            violations: this.violations.splice(0) // Send and clear violations
        };

        try {
            if (isUnloading) {
                // Use sendBeacon for unload events
                navigator.sendBeacon('/api/timer/update', JSON.stringify(data));
            } else {
                await fetch('/api/timer/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
            }
        } catch (error) {
            console.error('Failed to save progress:', error);
        }
    }

    recordViolation(type, details) {
        this.violations.push({
            type: type,
            details: details,
            timestamp: Date.now()
        });

        // Log to console for debugging
        console.warn(`Timer violation: ${type}`, details);
    }

    updateDisplay() {
        const minutes = Math.floor(this.elapsedTime / 60);
        const seconds = this.elapsedTime % 60;
        const timeText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        const timerText = document.getElementById('timer-text');
        if (timerText) {
            timerText.textContent = timeText;
        }
        
        // Ensure timer display is visible
        const timerDisplay = document.getElementById('timer-display');
        if (timerDisplay && this.isActive) {
            timerDisplay.style.display = 'block';
        }
        
        // Update progress bar
        const progress = Math.min((this.elapsedTime / this.requiredTime) * 100, 100);
        const progressBar = document.getElementById('timer-progress');
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        // Update status
        const status = document.getElementById('timer-status');
        if (status) {
            if (this.elapsedTime >= this.requiredTime) {
                status.textContent = 'Complete';
                status.className = 'badge bg-success';
            } else {
                status.textContent = 'In Progress';
                status.className = 'badge bg-warning';
            }
        }
        
        // Update action buttons if function exists
        if (typeof updateActionButtons === 'function') {
            updateActionButtons();
        }
    }

    showTimerDisplay() {
        const timerDisplay = document.getElementById('timer-display');
        if (timerDisplay) {
            timerDisplay.style.display = 'block';
            
            const requiredTimeElement = document.getElementById('required-time');
            if (requiredTimeElement) {
                requiredTimeElement.textContent = Math.floor(this.requiredTime / 60);
            }
        }
    }

    hideTimerDisplay() {
        const timerDisplay = document.getElementById('timer-display');
        if (timerDisplay) {
            timerDisplay.style.display = 'none';
        }
    }

    completeTimer() {
        this.isActive = false;
        
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
        
        this.saveProgress();
        this.showSuccess('Timer completed successfully!');
        
        // Enable next chapter or final exam
        this.enableNextStep();
    }

    enableNextStep() {
        // This will be called when timer is complete
        // Implementation depends on the course structure
        const nextButton = document.querySelector('.btn-next-chapter');
        if (nextButton) {
            nextButton.disabled = false;
            nextButton.classList.remove('disabled');
        }
    }

    showWarning(message) {
        // Create or update warning modal
        let modal = document.getElementById('timer-warning-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'timer-warning-modal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">Timer Warning</h5>
                        </div>
                        <div class="modal-body">
                            <p id="warning-message">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } else {
            document.getElementById('warning-message').textContent = message;
        }
        
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }

    showError(message) {
        alert('Timer Error: ' + message);
    }

    showSuccess(message) {
        // Show success notification
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    stopTimer() {
        this.isActive = false;
        
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
        
        this.hideTimerDisplay();
    }

    isTimerComplete() {
        return this.elapsedTime >= this.requiredTime;
    }

    getViolationCount() {
        return {
            tabSwitches: this.tabSwitches,
            pageReloads: this.pageReloads,
            focusLosses: this.focusLosses,
            totalViolations: this.violations.length
        };
    }
}

// Global timer instance - initialize when DOM is ready
function initializeStrictTimer() {
    if (!window.strictTimer) {
        window.strictTimer = new StrictTimer();
        console.log('✅ StrictTimer initialized successfully');
    }
}

// Try multiple initialization methods to ensure it works
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeStrictTimer);
} else {
    // DOM is already ready
    initializeStrictTimer();
}

// Also initialize on window load as a fallback
window.addEventListener('load', initializeStrictTimer);

// Ensure timer is available even if page loads very quickly
setTimeout(initializeStrictTimer, 100);