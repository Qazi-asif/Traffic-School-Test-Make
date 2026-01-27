<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Available Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        .course-list .card {
            transition: transform 0.2s ease-in-out;
        }
        .course-list .card:hover {
            transform: translateY(-5px);
        }
        .course-details p {
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div id="app">
            <course-list></course-list>
        </div>
        
        <!-- Fallback content -->
        <div id="fallback-content" style="display: none;">
            <h2>Available Courses</h2>
            <div class="row">
                <div class="col-md-12">
                    <div id="loading-indicator" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading courses...</p>
                    </div>
                    <div id="courses-container" class="row"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Fallback course loading
        async function loadCourses() {
            try {
                const loadingIndicator = document.getElementById('loading-indicator');
                const container = document.getElementById('courses-container');
                
                const response = await fetch('/web/courses', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error('Failed to load courses');
                }
                
                const courses = await response.json();
                
                // Hide loading indicator
                loadingIndicator.style.display = 'none';
                
                if (courses.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center"><p>No courses available.</p></div>';
                    return;
                }
                
                container.innerHTML = courses.map(course => `
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${course.title}</h5>
                                <p class="card-text flex-grow-1">${course.description}</p>
                                <div class="course-details mb-3">
                                    <p class="mb-1"><strong>State:</strong> ${course.state_code}</p>
                                    <p class="mb-1"><strong>Duration:</strong> ${course.duration} minutes</p>
                                    <p class="mb-1"><strong>Price:</strong> $${course.price}</p>
                                </div>
                                <div class="d-flex gap-2 mt-auto">
                                    <button onclick="viewDetails('${course.table}', '${course.id}')" class="btn btn-info flex-grow-1">View Details</button>

                                    <button onclick="enrollCourse('${course.id}', '${course.table}')" class="btn btn-primary flex-grow-1">Enroll</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading courses:', error);
                document.getElementById('loading-indicator').style.display = 'none';
                document.getElementById('courses-container').innerHTML = '<div class="col-12 text-center"><p>Error loading courses.</p></div>';
            }
        }
        
        async function enrollCourse(courseId, table) {
            try {
                // Check if already enrolled
                const checkResponse = await fetch('/api/check-enrollment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        table: table
                    })
                });
                
                const checkResult = await checkResponse.json();
                
                if (checkResult.already_enrolled) {
                    alert('You are already enrolled in this course. Please check your enrollments.');
                    return;
                }
                
                // Proceed to payment if not enrolled
                window.location.href = `/payment?course_id=${courseId}&table=${table}`;
            } catch (error) {
                console.error('Error checking enrollment:', error);
                // Fallback to payment page
                window.location.href = `/payment?course_id=${courseId}&table=${table}`;
            }
        }
        
      function viewDetails(table, courseId) {
    window.location.href = `/course-details/${table}/${courseId}`;
}

        
        // Show fallback and load courses if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app course-list');
            const fallbackContent = document.getElementById('fallback-content');
            
            // Check if Vue component loaded successfully
            if (!vueApp || !vueApp.innerHTML.trim()) {
                fallbackContent.style.display = 'block';
                loadCourses();
            }
        }, 2000); // Increased timeout to 2 seconds
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
