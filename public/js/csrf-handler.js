/**
 * CSRF Token Handler
 * Handles CSRF token refresh and form resubmission for 419 errors
 */
class CSRFHandler {
    constructor() {
        this.init();
    }

    init() {
        // Refresh CSRF token every 30 minutes (before session expires)
        setInterval(() => {
            this.refreshCSRFToken();
        }, 30 * 60 * 1000);

        // Handle form submissions with CSRF error retry
        this.attachFormHandlers();
    }

    async refreshCSRFToken() {
        try {
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateCSRFTokens(data.csrf_token);
                console.log('CSRF token refreshed successfully');
            }
        } catch (error) {
            console.warn('Failed to refresh CSRF token:', error);
        }
    }

    updateCSRFTokens(newToken) {
        // Update all CSRF token inputs
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = newToken;
        });

        // Update meta tag if exists
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', newToken);
        }
    }

    attachFormHandlers() {
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            if (!form.tagName || form.tagName.toLowerCase() !== 'form') return;

            // Skip if form doesn't have CSRF token
            const csrfInput = form.querySelector('input[name="_token"]');
            if (!csrfInput) return;

            // Only intercept if we detect potential CSRF issues
            // Let normal form submission work first
            const formData = new FormData(form);
            
            // Add a flag to detect if this is a retry
            if (!form.dataset.csrfRetry) {
                return; // Let normal form submission proceed
            }

            // Handle retry submission
            e.preventDefault();
            await this.submitFormWithRetry(form);
        });

        // Add error handling for 419 responses
        window.addEventListener('beforeunload', () => {
            // Refresh token before page unload to prevent stale tokens
            this.refreshCSRFToken();
        });
    }

    async submitFormWithRetry(form, retryCount = 0) {
        const maxRetries = 2;
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action || window.location.href, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.status === 419 && retryCount < maxRetries) {
                console.log('CSRF token expired, refreshing and retrying...');
                
                // Refresh CSRF token
                await this.refreshCSRFToken();
                
                // Wait a moment for token to update
                await new Promise(resolve => setTimeout(resolve, 100));
                
                // Retry submission
                return this.submitFormWithRetry(form, retryCount + 1);
            }

            // Handle successful response or redirect
            if (response.redirected) {
                window.location.href = response.url;
            } else if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    document.open();
                    document.write(await response.text());
                    document.close();
                } else {
                    // Handle JSON response
                    const data = await response.json();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            } else {
                // Handle error response
                if (response.status === 422) {
                    // Validation errors - reload page to show errors
                    window.location.reload();
                } else {
                    console.error('Form submission failed:', response.status, response.statusText);
                    this.showError('Form submission failed. Please try again.');
                }
            }

        } catch (error) {
            console.error('Form submission error:', error);
            this.showError('Network error. Please check your connection and try again.');
        }
    }

    showError(message) {
        // Create or update error message
        let errorDiv = document.querySelector('.csrf-error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'csrf-error-message';
            errorDiv.style.cssText = `
                background: #f8d7da;
                border: 1px solid #f5c2c7;
                color: #842029;
                padding: 15px;
                border-radius: 0.375rem;
                margin-bottom: 20px;
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                max-width: 500px;
            `;
            document.body.appendChild(errorDiv);
        }
        
        errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }

    // Method to handle 419 errors from server responses
    handle419Error(form) {
        console.log('Handling 419 error, marking form for retry...');
        form.dataset.csrfRetry = 'true';
        
        // Show user-friendly message
        this.showError('Session expired. Refreshing and retrying...');
        
        // Refresh token and resubmit
        this.refreshCSRFToken().then(() => {
            setTimeout(() => {
                form.submit();
            }, 500);
        });
    }
}

// Initialize CSRF handler when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.csrfHandler = new CSRFHandler();
    });
} else {
    window.csrfHandler = new CSRFHandler();
}

// Expose method for manual 419 error handling
window.handle419Error = function(form) {
    if (window.csrfHandler) {
        window.csrfHandler.handle419Error(form);
    }
};