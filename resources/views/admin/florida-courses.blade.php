<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .container-fluid {
            padding: 20px;
        }
        h2 {
            color: var(--text-primary);
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .table {
            color: var(--text-primary);
        }
        .table thead {
            background-color: var(--accent);
            color: white;
        }
        .table tbody tr {
            border-color: var(--border);
        }
        .table tbody tr:hover {
            background-color: var(--hover);
        }
        .form-select, .form-control {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .form-select:focus, .form-control:focus {
            background-color: var(--bg-secondary);
            border-color: var(--accent);
            color: var(--text-primary);
        }
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        .modal-content {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        .modal-header {
            border-bottom-color: var(--border);
        }
        .modal-footer {
            border-top-color: var(--border);
        }
        .table-info {
            background-color: #f8f9fa !important;
            color: #000000 !important;
        }
        .table-info .badge {
            background-color: #e9ecef !important;
            color: #000000 !important;
            border: 1px solid #dee2e6;
        }
        .state-divider {
            background-color: #f8f9fa !important;
            color: #000000 !important;
        }
        .state-divider .badge {
            background-color: #e9ecef !important;
            color: #000000 !important;
            border: 1px solid #dee2e6;
        }
        .btn-group .btn {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .btn-group .btn:hover {
            background-color: var(--hover);
            border-color: var(--accent);
        }
        .btn-outline-secondary {
            color: var(--text-primary);
            border-color: var(--border);
        }
        .btn-outline-secondary:hover {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        .btn-secondary {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Course Management</h2>
            <div class="btn-group">
                <button class="btn btn-outline-secondary" id="toggleGrouping" onclick="toggleStateGrouping()">
                    <i class="fas fa-layer-group"></i> Group by State
                </button>
                <button class="btn btn-primary" onclick="showCreateModal()">
                    <i class="fas fa-plus"></i> Create Course
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <select id="courseTypeFilter" class="form-select">
                    <option value="">All Course Types</option>
                    <option value="BDI">BDI</option>
                    <option value="ADI">ADI</option>
                    <option value="TLSAE">TLSAE</option>
                </select>
            </div>
            <div class="col-md-4">
                <select id="deliveryTypeFilter" class="form-select">
                    <option value="">All Delivery Types</option>
                    <option value="internet">Internet</option>
                    <option value="in_person">In Person</option>
                    <option value="cd_rom">CD ROM</option>
                    <option value="video">Video</option>
                    <option value="dvd">DVD</option>
                </select>
            </div>
            <div class="col-md-4">
                <input id="searchInput" type="text" class="form-control" placeholder="Search courses...">
            </div>
        </div>

        <div id="courses-table" class="table-responsive">
            <p>Loading courses...</p>
        </div>
    </div>

    <!-- Create/Edit Course Modal -->
    <div class="modal fade" id="courseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalTitle">Create Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="courseForm">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Course Type</label>
                                <select id="courseType" class="form-select" required>
                                    <option value="BDI">BDI</option>
                                    <option value="ADI">ADI</option>
                                    <option value="TLSAE">TLSAE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery Type</label>
                                <select id="deliveryType" class="form-select" required>
                                    <option value="internet">Internet</option>
                                    <option value="in_person">In Person</option>
                                    <option value="cd_rom">CD ROM</option>
                                    <option value="video">Video</option>
                                    <option value="dvd">DVD</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input id="courseTitle" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="courseDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Duration (minutes)</label>
                                <input id="courseDuration" type="number" class="form-control" min="240" value="240" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Pass Score (%)</label>
                                <input id="coursePassScore" type="number" class="form-control" min="0" max="100" value="80" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price ($)</label>
                                <input id="coursePrice" type="number" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DICDS Course ID</label>
                            <input id="dicdsId" type="text" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input id="courseActive" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="courseActive">Active</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveCourse()">Save Course</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Course Modal -->
    <div class="modal fade" id="copyCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Copy Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="copyCourseForm">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Copying course: <strong id="sourceCourseTitle"></strong>
                        </div>
                        
                        <h6>New Course Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Course Type</label>
                                <select id="copyCourseType" class="form-select" required>
                                    <option value="BDI">BDI</option>
                                    <option value="ADI">ADI</option>
                                    <option value="TLSAE">TLSAE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery Type</label>
                                <select id="copyDeliveryType" class="form-select" required>
                                    <option value="internet">Internet</option>
                                    <option value="in_person">In Person</option>
                                    <option value="cd_rom">CD ROM</option>
                                    <option value="video">Video</option>
                                    <option value="dvd">DVD</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Course Title</label>
                            <input id="copyCourseTitle" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="copyCourseDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Duration (minutes)</label>
                                <input id="copyCourseDuration" type="number" class="form-control" min="240" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Pass Score (%)</label>
                                <input id="copyCoursePassScore" type="number" class="form-control" min="0" max="100" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price ($)</label>
                                <input id="copyCoursePrice" type="number" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DICDS Course ID</label>
                            <input id="copyDicdsId" type="text" class="form-control" required>
                        </div>
                        
                        <hr>
                        <h6>Copy Options</h6>
                        <div class="form-check mb-2">
                            <input id="copyBasicInfo" type="checkbox" class="form-check-input" checked disabled>
                            <label class="form-check-label" for="copyBasicInfo">
                                Basic Course Information (always copied)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input id="copyChapters" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyChapters">
                                Copy Chapters and Content
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input id="copyQuestions" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyQuestions">
                                Copy Chapter Questions
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input id="copyFinalExam" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyFinalExam">
                                Copy Final Exam Questions
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input id="copyActive" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyActive">
                                Set new course as Active
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="executeCopy()">
                        <i class="fas fa-copy"></i> Copy Course
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Progress Modal -->
    <div class="modal fade" id="copyProgressModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Copying Course</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p id="copyProgressText">Preparing to copy course...</p>
                    <div class="progress">
                        <div id="copyProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let courses = [];
        let editingCourseId = null;
        
        async function loadCourses() {
            try {
                const response = await fetch('/api/florida-courses', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    courses = result.data || result;
                    displayCourses();
                } else {
                    document.getElementById('courses-table').innerHTML = '<p class="text-danger">Error loading courses.</p>';
                }
            } catch (error) {
                console.error('Error loading courses:', error);
                document.getElementById('courses-table').innerHTML = '<p class="text-danger">Error loading courses: ' + error.message + '</p>';
            }
        }
        
        function displayCourses() {
            const container = document.getElementById('courses-table');
            
            if (!courses || !Array.isArray(courses) || courses.length === 0) {
                container.innerHTML = '<p>No courses found.</p>';
                return;
            }
            
            container.innerHTML = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>State</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Passing Score</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${courses.map(course => `
                            <tr>
                                <td>${course.id || 'N/A'}</td>
                                <td>${course.title || 'N/A'}</td>
                                <td><span class="badge bg-info">${course.course_type || 'N/A'}</span></td>
                                <td>${course.state_code || 'N/A'}</td>
                                <td>${course.duration || 0} min</td>
                                <td>$${course.price || '0.00'}</td>
                                <td>${course.passing_score || 80}%</td>
                                <td>
                                    <span class="${course.is_active ? 'badge bg-success' : 'badge bg-danger'}">
                                        ${course.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info" onclick="copyCourse(${course.id})" title="Copy Course">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editCourse(${course.id})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="manageChapters(${course.id})" title="Chapters">
                                            <i class="fas fa-book"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="manageBreaks(${course.id})" title="Chapter Breaks">
                                            <i class="fas fa-pause-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(${course.id})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        // Add state-grouped display function
        function displayCoursesGroupedByState() {
            const container = document.getElementById('courses-table');
            
            if (!courses || !Array.isArray(courses) || courses.length === 0) {
                container.innerHTML = '<p>No courses found.</p>';
                return;
            }
            
            // Group courses by state
            const coursesByState = {};
            courses.forEach(course => {
                const state = course.state_code || 'Unknown';
                if (!coursesByState[state]) {
                    coursesByState[state] = [];
                }
                coursesByState[state].push(course);
            });
            
            // Sort states alphabetically
            const sortedStates = Object.keys(coursesByState).sort();
            
            let tableHTML = '<table class="table table-striped"><thead><tr><th>ID</th><th>Title</th><th>Type</th><th>State</th><th>Duration</th><th>Price</th><th>Passing Score</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            
            sortedStates.forEach(state => {
                // Add state divider
                tableHTML += `
                    <tr class="table-info">
                        <td colspan="9" class="fw-bold text-center py-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            ${state === 'FL' ? 'Florida' : 
                              state === 'CA' ? 'California' : 
                              state === 'TX' ? 'Texas' : 
                              state === 'MO' ? 'Missouri' : 
                              state === 'DE' ? 'Delaware' : state} Courses
                            <span class="badge ms-2">${coursesByState[state].length}</span>
                        </td>
                    </tr>
                `;
                
                // Add courses for this state
                coursesByState[state].forEach(course => {
                    tableHTML += `
                        <tr>
                            <td>${course.id || 'N/A'}</td>
                            <td>${course.title || 'N/A'}</td>
                            <td><span class="badge bg-info">${course.course_type || 'N/A'}</span></td>
                            <td>${course.state_code || 'N/A'}</td>
                            <td>${course.duration || 0} min</td>
                            <td>${course.price || '0.00'}</td>
                            <td>${course.passing_score || 80}%</td>
                            <td>
                                <span class="${course.is_active ? 'badge bg-success' : 'badge bg-danger'}">
                                    ${course.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="copyCourse(${course.id})" title="Copy Course">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editCourse(${course.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="manageChapters(${course.id})" title="Chapters">
                                        <i class="fas fa-book"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="manageBreaks(${course.id})" title="Chapter Breaks">
                                        <i class="fas fa-pause-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(${course.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            });
            
            tableHTML += '</tbody></table>';
            container.innerHTML = tableHTML;
        }
        
        function showCreateModal() {
            editingCourseId = null;
            document.getElementById('courseModalTitle').textContent = 'Create Course';
            clearForm();
            new bootstrap.Modal(document.getElementById('courseModal')).show();
        }
        
        function editCourse(id) {
            editingCourseId = id;
            const course = courses.find(c => c.id === id);
            if (course) {
                document.getElementById('courseModalTitle').textContent = 'Edit Course';
                fillForm(course);
                new bootstrap.Modal(document.getElementById('courseModal')).show();
            }
        }
        
        function fillForm(course) {
            document.getElementById('courseType').value = course.course_type;
            document.getElementById('deliveryType').value = course.delivery_type;
            document.getElementById('courseTitle').value = course.title;
            document.getElementById('courseDescription').value = course.description || '';
            document.getElementById('courseDuration').value = course.total_duration;
            document.getElementById('coursePassScore').value = course.min_pass_score || 80;
            document.getElementById('coursePrice').value = course.price;
            document.getElementById('dicdsId').value = course.dicds_course_id;
            document.getElementById('courseActive').checked = course.is_active;
        }
        
        function clearForm() {
            document.getElementById('courseType').value = 'BDI';
            document.getElementById('deliveryType').value = 'internet';
            document.getElementById('courseTitle').value = '';
            document.getElementById('courseDescription').value = '';
            document.getElementById('courseDuration').value = 240;
            document.getElementById('coursePassScore').value = 80;
            document.getElementById('coursePrice').value = '';
            document.getElementById('dicdsId').value = '';
            document.getElementById('courseActive').checked = true;
        }
        
        async function saveCourse() {
            const formData = {
                course_type: document.getElementById('courseType').value,
                delivery_type: document.getElementById('deliveryType').value,
                title: document.getElementById('courseTitle').value,
                description: document.getElementById('courseDescription').value,
                total_duration: document.getElementById('courseDuration').value,
                min_pass_score: document.getElementById('coursePassScore').value,
                price: document.getElementById('coursePrice').value,
                dicds_course_id: document.getElementById('dicdsId').value,
                is_active: document.getElementById('courseActive').checked
            };
            
            try {
                const url = editingCourseId ? `/api/florida-courses/${editingCourseId}` : '/api/florida-courses';
                const method = editingCourseId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                });
                
                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('courseModal')).hide();
                    loadCourses();
                    alert(editingCourseId ? 'Course updated successfully!' : 'Course created successfully!');
                } else {
                    alert('Error saving course');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function manageChapters(id) {
            window.location.href = `/admin/florida-courses/${id}/chapters`;
        }
        
        function manageBreaks(id) {
            window.location.href = `/admin/florida-courses/${id}/chapter-breaks`;
        }
        
        async function deleteCourse(id) {
            const course = courses.find(c => c.id === id);
            const courseName = course ? course.title : `Course #${id}`;
            
            const confirmMessage = `⚠️ WARNING: This will permanently delete "${courseName}" and ALL related data including:
            
• All student enrollments
• All chapters and content
• All quiz questions and attempts
• All certificates
• All progress data
• All state transmission records

This action CANNOT be undone!

Are you absolutely sure you want to proceed?`;
            
            if (confirm(confirmMessage)) {
                try {
                    const response = await fetch(`/api/florida-courses/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        loadCourses();
                        let successMessage = 'Course deleted successfully!';
                        if (result.deleted_enrollments > 0) {
                            successMessage += `\n\nDeleted ${result.deleted_enrollments} enrollment(s) and ${result.deleted_chapters} chapter(s).`;
                        }
                        alert(successMessage);
                    } else {
                        alert('Error deleting course: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
        
        let copyingCourseId = null;
        
        function copyCourse(id) {
            copyingCourseId = id;
            const course = courses.find(c => c.id === id);
            if (course) {
                // Fill the form with source course data
                document.getElementById('sourceCourseTitle').textContent = course.title;
                document.getElementById('copyCourseType').value = course.course_type;
                document.getElementById('copyDeliveryType').value = course.delivery_type;
                document.getElementById('copyCourseTitle').value = course.title + ' (Copy)';
                document.getElementById('copyCourseDescription').value = course.description || '';
                document.getElementById('copyCourseDuration').value = course.total_duration;
                document.getElementById('copyCoursePassScore').value = course.min_pass_score || 80;
                document.getElementById('copyCoursePrice').value = course.price;
                document.getElementById('copyDicdsId').value = course.dicds_course_id + '_copy';
                
                new bootstrap.Modal(document.getElementById('copyCourseModal')).show();
            }
        }
        
        async function executeCopy() {
            const copyData = {
                source_course_id: copyingCourseId,
                course_type: document.getElementById('copyCourseType').value,
                delivery_type: document.getElementById('copyDeliveryType').value,
                title: document.getElementById('copyCourseTitle').value,
                description: document.getElementById('copyCourseDescription').value,
                total_duration: document.getElementById('copyCourseDuration').value,
                min_pass_score: document.getElementById('copyCoursePassScore').value,
                price: document.getElementById('copyCoursePrice').value,
                dicds_course_id: document.getElementById('copyDicdsId').value,
                is_active: document.getElementById('copyActive').checked,
                copy_options: {
                    chapters: document.getElementById('copyChapters').checked,
                    questions: document.getElementById('copyQuestions').checked,
                    final_exam: document.getElementById('copyFinalExam').checked
                }
            };
            
            // Hide copy modal and show progress modal
            bootstrap.Modal.getInstance(document.getElementById('copyCourseModal')).hide();
            const progressModal = new bootstrap.Modal(document.getElementById('copyProgressModal'));
            progressModal.show();
            
            try {
                updateCopyProgress(20, 'Creating new course...');
                
                const response = await fetch('/api/florida-courses/copy', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(copyData)
                });
                
                if (response.ok) {
                    updateCopyProgress(100, 'Course copied successfully!');
                    setTimeout(() => {
                        progressModal.hide();
                        loadCourses();
                        alert('Course copied successfully!');
                    }, 1500);
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to copy course');
                }
            } catch (error) {
                console.error('Copy error:', error);
                progressModal.hide();
                alert('Error copying course: ' + error.message);
            }
        }
        
        function updateCopyProgress(percent, text) {
            document.getElementById('copyProgressBar').style.width = percent + '%';
            document.getElementById('copyProgressText').textContent = text;
        }



        // State grouping toggle functionality
        let isGroupedByState = false;
        
        function toggleStateGrouping() {
            isGroupedByState = !isGroupedByState;
            const toggleBtn = document.getElementById('toggleGrouping');
            
            if (isGroupedByState) {
                displayCoursesGroupedByState();
                toggleBtn.innerHTML = '<i class="fas fa-list"></i> Show All';
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-secondary');
            } else {
                displayCourses();
                toggleBtn.innerHTML = '<i class="fas fa-layer-group"></i> Group by State';
                toggleBtn.classList.remove('btn-secondary');
                toggleBtn.classList.add('btn-outline-secondary');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadCourses();
        });
    </script>
</body>
</html>
