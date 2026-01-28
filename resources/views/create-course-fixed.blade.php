<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - Fixed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { margin-bottom: 20px; }
        .result-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        .success { border-color: #28a745; background-color: #d4edda; }
        .error { border-color: #dc3545; background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1><i class="fas fa-graduation-cap"></i> Course Management System</h1>
        <p class="text-muted">Create and manage your traffic school courses</p>
        
        <div class="row">
            <!-- Courses List -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Courses</h5>
                        <button onclick="loadCourses()" class="btn btn-sm btn-outline-primary">Refresh</button>
                    </div>
                    <div class="card-body">
                        <div id="courses-list">
                            <button onclick="loadCourses()" class="btn btn-primary">Load Courses</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Management -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> Course Management</h5>
                    </div>
                    <div class="card-body">
                        <div id="course-details">
                            <p class="text-muted">Select a course to manage chapters and content</p>
                        </div>
                    </div>
                </div>
                
                <!-- Chapters Section -->
                <div class="card" id="chapters-section" style="display: none;">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="fas fa-book"></i> Chapters</h5>
                        <button onclick="showAddChapter()" class="btn btn-sm btn-success">Add Chapter</button>
                    </div>
                    <div class="card-body">
                        <div id="chapters-list"></div>
                    </div>
                </div>
                
                <!-- Add Chapter Form -->
                <div class="card" id="add-chapter-form" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> Add Chapter</h5>
                    </div>
                    <div class="card-body">
                        <form id="chapter-form">
                            <div class="mb-3">
                                <label class="form-label">Chapter Title</label>
                                <input type="text" id="chapter-title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <div class="mb-2">
                                    <input type="file" id="docx-file" accept=".docx" style="display: none;">
                                    <button type="button" onclick="document.getElementById('docx-file').click()" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-word"></i> Import DOCX
                                    </button>
                                </div>
                                <textarea id="chapter-content" class="form-control" rows="10" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Duration (minutes)</label>
                                    <input type="number" id="chapter-duration" class="form-control" value="30" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Video URL (optional)</label>
                                    <input type="text" id="chapter-video" class="form-control">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">Save Chapter</button>
                                <button type="button" onclick="hideAddChapter()" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- DOCX Import Result -->
        <div id="docx-result" class="result-box" style="display: none;"></div>
    </div>

    <script>
        let courses = [];
        let currentCourseId = null;
        
        // Load all courses
        async function loadCourses() {
            try {
                const response = await fetch('/api/no-csrf/courses', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    courses = await response.json();
                    displayCourses();
                } else {
                    document.getElementById('courses-list').innerHTML = '<div class="alert alert-danger">Failed to load courses</div>';
                }
            } catch (error) {
                document.getElementById('courses-list').innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            }
        }
        
        // Display courses list
        function displayCourses() {
            const container = document.getElementById('courses-list');
            
            if (courses.length === 0) {
                container.innerHTML = '<p class="text-muted">No courses found</p>';
                return;
            }
            
            container.innerHTML = courses.map((course, index) => 
                '<div class="border rounded p-2 mb-2">' +
                    '<strong>' + course.title + '</strong><br>' +
                    '<small class="text-muted">' + course.state_code + ' - $' + course.price + '</small><br>' +
                    '<button onclick="manageCourse(' + course.id + ')" class="btn btn-sm btn-primary mt-1">Manage</button>' +
                '</div>'
            ).join('');
        }
        
        // Manage specific course
        async function manageCourse(courseId) {
            currentCourseId = courseId;
            const course = courses.find(c => c.id === courseId);
            
            document.getElementById('course-details').innerHTML = 
                '<h6>' + course.title + '</h6>' +
                '<p class="text-muted">' + course.description + '</p>';
            
            document.getElementById('chapters-section').style.display = 'block';
            await loadChapters(courseId);
        }
        
        // Load chapters for a course
        async function loadChapters(courseId) {
            try {
                const response = await fetch('/api/no-csrf/courses/' + courseId + '/chapters', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const chapters = await response.json();
                    displayChapters(chapters);
                } else {
                    document.getElementById('chapters-list').innerHTML = '<div class="alert alert-danger">Failed to load chapters</div>';
                }
            } catch (error) {
                document.getElementById('chapters-list').innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            }
        }
        
        // Display chapters
        function displayChapters(chapters) {
            const container = document.getElementById('chapters-list');
            
            if (chapters.length === 0) {
                container.innerHTML = '<p class="text-muted">No chapters added yet</p>';
                return;
            }
            
            container.innerHTML = chapters.map((chapter, index) => 
                '<div class="border rounded p-2 mb-2">' +
                    '<strong>' + (index + 1) + '. ' + chapter.title + '</strong><br>' +
                    '<small class="text-muted">' + (chapter.duration || 30) + ' minutes</small><br>' +
                    '<div class="mt-1">' +
                        '<button class="btn btn-sm btn-outline-primary me-1">Edit</button>' +
                        '<button class="btn btn-sm btn-outline-danger">Delete</button>' +
                    '</div>' +
                '</div>'
            ).join('');
        }
        
        // Show add chapter form
        function showAddChapter() {
            document.getElementById('add-chapter-form').style.display = 'block';
        }
        
        // Hide add chapter form
        function hideAddChapter() {
            document.getElementById('add-chapter-form').style.display = 'none';
            document.getElementById('chapter-form').reset();
        }
        
        // Handle DOCX file import
        document.getElementById('docx-file').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const resultDiv = document.getElementById('docx-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Importing DOCX...';
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const response = await fetch('/docx-import-direct.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.html) {
                        document.getElementById('chapter-content').value = data.html;
                        resultDiv.className = 'result-box success';
                        resultDiv.innerHTML = '<strong>✅ SUCCESS!</strong> DOCX imported successfully';
                    } else {
                        throw new Error(data.error || 'Import failed');
                    }
                } else {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
            } catch (error) {
                resultDiv.className = 'result-box error';
                resultDiv.innerHTML = '<strong>❌ ERROR:</strong> ' + error.message;
            }
            
            // Clear file input
            this.value = '';
        });
        
        // Handle chapter form submission
        document.getElementById('chapter-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!currentCourseId) {
                alert('Please select a course first');
                return;
            }
            
            const formData = {
                title: document.getElementById('chapter-title').value,
                content: document.getElementById('chapter-content').value,
                duration: document.getElementById('chapter-duration').value,
                video_url: document.getElementById('chapter-video').value
            };
            
            try {
                const response = await fetch('/api/no-csrf/courses/' + currentCourseId + '/chapters', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                if (response.ok) {
                    alert('Chapter added successfully!');
                    hideAddChapter();
                    loadChapters(currentCourseId);
                } else {
                    const error = await response.json();
                    alert('Error: ' + (error.message || 'Failed to add chapter'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
        
        // Load courses on page load
        loadCourses();
    </script>
</body>
</html>