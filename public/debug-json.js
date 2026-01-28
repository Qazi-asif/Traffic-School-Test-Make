
// Add this to your browser console to catch JSON errors
(function() {
    const originalFetch = window.fetch;
    const originalXHR = XMLHttpRequest.prototype.open;
    
    // Intercept fetch requests
    window.fetch = function(...args) {
        console.log('🔍 FETCH REQUEST:', args[0]);
        return originalFetch.apply(this, args)
            .then(response => {
                console.log('📡 FETCH RESPONSE:', response.url, 'Status:', response.status);
                return response.clone().text().then(text => {
                    if (response.headers.get('content-type')?.includes('application/json')) {
                        try {
                            JSON.parse(text);
                            console.log('✅ Valid JSON response from:', response.url);
                        } catch (e) {
                            console.error('❌ JSON PARSE ERROR from:', response.url);
                            console.error('Response text:', text.substring(0, 200));
                        }
                    }
                    return response;
                });
            });
    };
    
    // Intercept XMLHttpRequest
    XMLHttpRequest.prototype.open = function(method, url, ...args) {
        console.log('🔍 XHR REQUEST:', method, url);
        
        this.addEventListener('load', function() {
            console.log('📡 XHR RESPONSE:', url, 'Status:', this.status);
            
            if (this.getResponseHeader('content-type')?.includes('application/json')) {
                try {
                    JSON.parse(this.responseText);
                    console.log('✅ Valid JSON response from:', url);
                } catch (e) {
                    console.error('❌ JSON PARSE ERROR from:', url);
                    console.error('Response text:', this.responseText.substring(0, 200));
                }
            }
        });
        
        return originalXHR.apply(this, [method, url, ...args]);
    };
    
    console.log('🔧 JSON debugging enabled. All AJAX requests will be logged.');
})();
