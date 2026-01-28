// Course Creation Debug Script
// Add this to your course creation pages to debug form submission issues

console.log('🔧 Course Creation Debug Script Loaded');

// Override the original form submission to add debugging
function debugCourseSubmission() {
    // Debug for main course creation form (/create-course)
    const mainForm = document.getElementById('course-form');
    if (mainForm) {
        console.log('📝 Found main course form');
        
        mainForm.addEventListener('submit', function(e) {
            console.log('🚀 Main form submission intercepted');
            console.log('Form data:', new FormData(this));
            console.log('CSRF token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
        });
    }
    
    // Debug for Florida courses modal form
    const floridaForm = document.getElementById('courseForm');
    if (floridaForm) {
        console.log('📝 Found Florida course form');
    }
    
    // Override saveCourse function if it exists
    if (typeof saveCourse === 'function') {
        const originalSaveCourse = saveCourse;
        window.saveCourse = async function() {
            console.log('🚀 Florida saveCourse function called');
            
            const formData = {
                course_type: document.getElementById('courseType')?.value,
                delivery_type: document.getElementById('deliveryType')?.value,
                title: document.getElementById('courseTitle')?.value,
                description: document.getElementById('courseDescription')?.value,
                total_duration: document.getElementById('courseDuration')?.value,
                min_pass_score: document.getElementById('coursePassScore')?.value,
                price: document.getElementById('coursePrice')?.value,
                dicds_course_id: document.getElementById('dicdsId')?.value,
                is_active: document.getElementById('courseActive')?.checked
            };
            
            console.log('📊 Form data being sent:', formData);
            console.log('🔑 CSRF token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
            
            // Validate required fields
            const requiredFields = ['course_type', 'delivery_type', 'title', 'total_duration', 'min_pass_score', 'price', 'dicds_course_id'];
            const missingFields = requiredFields.filter(field => !formData[field]);
            
            if (missingFields.length > 0) {
                console.error('❌ Missing required fields:', missingFields);
                alert('Missing required fields: ' + missingFields.join(', '));
                return;
            }
            
            try {
                const url = editingCourseId ? `/api/florida-courses/${editingCourseId}` : '/api/florida-courses';
                const method = editingCourseId ? 'PUT' : 'POST';
                
                console.log('🌐 Making request:', { url, method });
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });
                
                console.log('📡 Response status:', response.status);
                console.log('📡 Response headers:', [...response.headers.entries()]);
                
                const responseText = await response.text();
                console.log('📡 Response body:', responseText);
                
                let responseData;
                try {
                    responseData = JSON.parse(responseText);
                } catch (e) {
                    console.error('❌ Failed to parse response as JSON:', e);
                    responseData = { error: 'Invalid JSON response', raw: responseText };
                }
                
                if (response.ok) {
                    console.log('✅ Course saved successfully:', responseData);
                    if (typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(document.getElementById('courseModal'))?.hide();
                    }
                    if (typeof loadCourses === 'function') {
                        loadCourses();
                    }
                    alert(editingCourseId ? 'Course updated successfully!' : 'Course created successfully!');
                } else {
                    console.error('❌ Server error:', responseData);
                    
                    let errorMessage = 'Error saving course';
                    if (responseData.message) {
                        errorMessage += ': ' + responseData.message;
                    } else if (responseData.error) {
                        errorMessage += ': ' + responseData.error;
                    } else if (responseData.validation_errors) {
                        errorMessage += ': ' + Object.values(responseData.validation_errors).flat().join(', ');
                    }
                    
                    alert(errorMessage);
                }
            } catch (error) {
                console.error('❌ Network/JavaScript error:', error);
                alert('Error: ' + error.message);
            }
        };
        
        console.log('🔄 saveCourse function wrapped with debugging');
    }
    
    // Add network request monitoring
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const [url, options] = args;
        
        if (url.includes('courses') || url.includes('florida-courses')) {
            console.log('🌐 Course-related fetch request:', { url, options });
        }
        
        return originalFetch.apply(this, args).then(response => {
            if (url.includes('courses') || url.includes('florida-courses')) {
                console.log('📡 Course-related fetch response:', {
                    url,
                    status: response.status,
                    statusText: response.statusText,
                    headers: [...response.headers.entries()]
                });
            }
            return response;
        }).catch(error => {
            if (url.includes('courses') || url.includes('florida-courses')) {
                console.error('❌ Course-related fetch error:', { url, error });
            }
            throw error;
        });
    };
    
    console.log('🔄 Fetch function wrapped with debugging');
}

// Run debugging setup when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', debugCourseSubmission);
} else {
    debugCourseSubmission();
}

// Add some helper functions for manual testing
window.testCourseCreation = function() {
    console.log('🧪 Testing course creation endpoints...');
    
    const testData = {
        title: 'Debug Test Course',
        description: 'Test course created via debug script',
        state_code: 'FL',
        min_pass_score: 80,
        total_duration: 240,
        price: 29.99,
        is_active: true
    };
    
    // Test main endpoint
    fetch('/web/courses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(testData)
    })
    .then(response => {
        console.log('📡 /web/courses response:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📡 /web/courses body:', text);
    })
    .catch(error => {
        console.error('❌ /web/courses error:', error);
    });
    
    // Test Florida endpoint
    const floridaData = {
        course_type: 'BDI',
        delivery_type: 'Online',
        title: 'Debug Test Florida Course',
        description: 'Test Florida course created via debug script',
        total_duration: 240,
        min_pass_score: 80,
        price: 29.99,
        dicds_course_id: 'DEBUG_' + Date.now(),
        is_active: true
    };
    
    fetch('/api/florida-courses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(floridaData)
    })
    .then(response => {
        console.log('📡 /api/florida-courses response:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📡 /api/florida-courses body:', text);
    })
    .catch(error => {
        console.error('❌ /api/florida-courses error:', error);
    });
};

console.log('🎯 Debug script ready! Run testCourseCreation() in console to test endpoints manually.');