<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid" style="margin-left: 280px; padding: 15px; max-width: calc(100% - 300px); overflow-x: hidden;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Chapter Builder</h2>
            <button class="btn btn-primary" onclick="showCreateModal()">
                <i class="fas fa-plus"></i> Add Chapter
            </button>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div id="chapters-content">
                    <p>Loading chapters...</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Timer Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="timerEnabled" checked>
                            <label class="form-check-label" for="timerEnabled">Enable Chapter Timers</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="enforceMinTime" checked>
                            <label class="form-check-label" for="enforceMinTime">Enforce Minimum Time</label>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="saveTimerSettings()">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Chapter Modal -->
    <div class="modal fade" id="chapterModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chapterModalTitle">Create Chapter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="chapterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input id="chapterTitle" type="text" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input id="chapterDuration" type="number" class="form-control" min="1" value="30" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Min Time (minutes)</label>
                                <input id="chapterMinTime" type="number" class="form-control" min="0" value="15" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Video URL (optional)</label>
                            <input id="chapterVideo" type="url" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea id="chapterContent" class="form-control" rows="10" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Order Index</label>
                                <input id="chapterOrder" type="number" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input id="chapterActive" type="checkbox" class="form-check-input" checked>
                                    <label class="form-check-label" for="chapterActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveChapter()">Save Chapter</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let chapters = [];
        let editingChapterId = null;
        let courseId = window.location.pathname.split('/')[3]; // Extract courseId from URL
        
        async function loadChapters() {
            try {
                const response = await fetch(`/api/florida-courses/${courseId}/chapters`);
                if (response.ok) {
                    const result = await response.json();
                    chapters = result.data || result;
                    displayChapters();
                } else {
                    document.getElementById('chapters-content').innerHTML = '<p class="text-danger">Error loading chapters.</p>';
                }
            } catch (error) {
                console.error('Error loading chapters:', error);
                document.getElementById('chapters-content').innerHTML = '<p class="text-danger">Error loading chapters.</p>';
            }
        }
        
        function displayChapters() {
            const container = document.getElementById('chapters-content');
            
            if (!chapters || chapters.length === 0) {
                container.innerHTML = '<p>No chapters found. <button class="btn btn-primary" onclick="showCreateModal()">Create First Chapter</button></p>';
                return;
            }
            
            let chaptersHtml = chapters.map(chapter => `
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h5>${chapter.title}</h5>
                        <div>
                            <span class="badge bg-info me-2">${chapter.duration} min</span>
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editChapter('${chapter.id}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success me-1" onclick="manageQuestions('${chapter.id}')">
                                <i class="fas fa-question-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteChapter('${chapter.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Duration: ${chapter.duration} minutes</small><br>
                                <small class="text-muted">Min Time: ${chapter.required_min_time || chapter.duration || 0} minutes</small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Order: ${chapter.order_index}</small><br>
                                <small class="${chapter.is_active ? 'text-success' : 'text-danger'}">
                                    ${chapter.is_active ? 'Active' : 'Inactive'}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Add Final Exam section
            chaptersHtml += `
                <div class="card mb-2 border-warning">
                    <div class="card-header d-flex justify-content-between align-items-center py-2 bg-warning bg-opacity-10">
                        <h5><i class="fas fa-graduation-cap me-2"></i>Final Exam</h5>
                        <div>
                            <span class="badge bg-warning text-dark me-2">60 min</span>
                            <button class="btn btn-sm btn-outline-success me-1" onclick="manageQuestions('final-exam')" title="Manage Final Exam Questions">
                                <i class="fas fa-question-circle"></i> Questions
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-12">
                                <small class="text-muted">The final exam is automatically added to all courses. Students must pass this to complete the course.</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.innerHTML = chaptersHtml;
        }
        
        function showCreateModal() {
            editingChapterId = null;
            document.getElementById('chapterModalTitle').textContent = 'Create Chapter';
            clearChapterForm();
            new bootstrap.Modal(document.getElementById('chapterModal')).show();
        }
        
        function editChapter(id) {
            editingChapterId = id;
            const chapter = chapters.find(c => c.id == id);
            if (chapter) {
                document.getElementById('chapterModalTitle').textContent = 'Edit Chapter';
                fillChapterForm(chapter);
                new bootstrap.Modal(document.getElementById('chapterModal')).show();
            } else {
                console.error('Chapter not found with id:', id, 'Available chapters:', chapters);
            }
        }
        
        function fillChapterForm(chapter) {
            document.getElementById('chapterTitle').value = chapter.title;
            document.getElementById('chapterDuration').value = chapter.duration;
            document.getElementById('chapterMinTime').value = chapter.required_min_time || chapter.duration || 0;
            document.getElementById('chapterVideo').value = chapter.video_url || '';
            document.getElementById('chapterContent').value = chapter.content;
            document.getElementById('chapterOrder').value = chapter.order_index;
            document.getElementById('chapterActive').checked = chapter.is_active;
        }
        
        function clearChapterForm() {
            document.getElementById('chapterTitle').value = '';
            document.getElementById('chapterDuration').value = 30;
            document.getElementById('chapterMinTime').value = 15;
            document.getElementById('chapterVideo').value = '';
            document.getElementById('chapterContent').value = '';
            document.getElementById('chapterOrder').value = chapters.length + 1;
            document.getElementById('chapterActive').checked = true;
        }
        
        async function saveChapter() {
            const formData = {
                title: document.getElementById('chapterTitle').value,
                duration: parseInt(document.getElementById('chapterDuration').value),
                required_min_time: parseInt(document.getElementById('chapterMinTime').value),
                video_url: document.getElementById('chapterVideo').value,
                content: document.getElementById('chapterContent').value,
                order_index: parseInt(document.getElementById('chapterOrder').value),
                is_active: document.getElementById('chapterActive').checked
            };
            
            try {
                const url = editingChapterId ? `/api/chapters/${editingChapterId}` : `/api/florida-courses/${courseId}/chapters`;
                const method = editingChapterId ? 'PUT' : 'POST';
                
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
                    bootstrap.Modal.getInstance(document.getElementById('chapterModal')).hide();
                    loadChapters();
                    alert(editingChapterId ? 'Chapter updated successfully!' : 'Chapter created successfully!');
                } else {
                    alert('Error saving chapter');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function manageQuestions(id) {
            let url = `/admin/chapters/${id}/questions`;
            
            // For final exam, add course_id parameter
            if (id === 'final-exam') {
                // Get course_id from current URL path
                const pathParts = window.location.pathname.split('/');
                const courseIdIndex = pathParts.indexOf('florida-courses') + 1;
                const courseId = pathParts[courseIdIndex] || '1';
                url += `?course_id=${courseId}`;
            }
            
            window.location.href = url;
        }
        
        async function deleteChapter(id) {
            if (confirm('Are you sure you want to delete this chapter?')) {
                try {
                    const response = await fetch(`/api/chapters/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        loadChapters();
                        alert('Chapter deleted successfully!');
                    } else {
                        alert('Error deleting chapter');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
        
        function saveTimerSettings() {
            const enabled = document.getElementById('timerEnabled').checked;
            const enforce = document.getElementById('enforceMinTime').checked;
            alert(`Timer settings saved: Enabled=${enabled}, Enforce=${enforce}`);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadChapters();
        });
    </script>
</body>
</html>
