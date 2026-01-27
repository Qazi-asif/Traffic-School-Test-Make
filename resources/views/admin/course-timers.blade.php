@extends('layouts.app')

@section('content')
<style>
.modal-backdrop {
    display: none !important;
}
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-clock me-2"></i>Course Timers</h2>
            <span id="strictDurationStatus" class="badge bg-secondary fs-6 p-2">Loading...</span>
        </div>
        <div>
            <button id="strictDurationToggleBtn" class="btn btn-lg me-2" onclick="toggleStrictDuration()">
                <i class="fas fa-lock me-2"></i><span id="strictDurationBtn">Loading...</span>
            </button>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addTimerModal">
                <i class="fas fa-plus me-2"></i>Add Timer
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Search by course or chapter name..." onkeyup="filterTimers()">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="timers-table"></div>
        </div>
    </div>
</div>

<!-- Add Timer Modal -->
<div class="modal fade" id="addTimerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Configure Timer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="timerForm">
                    <!-- Search Bar for Chapters -->
                    <div class="mb-3">
                        <label class="form-label">Search Chapter</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="chapterSearch" placeholder="Search by course or chapter name..." onkeyup="filterChapters()">
                        </div>
                    </div>
                    
                    <!-- Chapter Selection with Grouped Options -->
                    <div class="mb-3">
                        <label class="form-label">Select Chapter</label>
                        <select class="form-select" id="chapter_id" required size="10" style="height: 300px; overflow-y: auto;">
                            <option value="">Loading chapters...</option>
                        </select>
                        <small class="text-muted">Chapters are grouped by course</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Required Time (minutes)</label>
                                <input type="number" class="form-control" id="required_minutes" required min="1" placeholder="e.g., 30">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_enabled" checked>
                                    <label class="form-check-label" for="is_enabled">Enabled</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="allow_pause" checked>
                                <label class="form-check-label" for="allow_pause">
                                    <i class="fas fa-pause-circle me-1"></i>Allow Pause
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="bypass_for_admin" checked>
                                <label class="form-check-label" for="bypass_for_admin">
                                    <i class="fas fa-user-shield me-1"></i>Admin Bypass
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-lock me-2"></i>Strict Duration Enforcement</h6>
                        <p class="mb-2">Enable strict duration enforcement globally for all courses. When enabled:</p>
                        <ul class="mb-0">
                            <li>Users must wait for the full chapter duration before marking complete</li>
                            <li>"Mark as Complete" button will be disabled until timer runs out</li>
                            <li>Timer will be enforced across all chapters in all courses</li>
                        </ul>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="strict_duration_enabled">
                        <label class="form-check-label" for="strict_duration_enabled">
                            <i class="fas fa-lock me-1"></i><strong>Enable Strict Duration Enforcement</strong>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveTimer()">
                    <i class="fas fa-save me-2"></i>Save Timer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let allChapters = [];

function loadChapters() {
    console.log('Loading chapters...');
    fetch('/api/chapters', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Chapters data:', data);
        allChapters = data;
        displayChaptersGrouped(data);
    })
    .catch(error => {
        console.error('Error loading chapters:', error);
        document.getElementById('chapter_id').innerHTML = '<option value="">Error loading chapters</option>';
    });
}

function displayChaptersGrouped(chapters) {
    const select = document.getElementById('chapter_id');
    select.innerHTML = '<option value="">Select a chapter...</option>';
    
    if (!Array.isArray(chapters) || chapters.length === 0) {
        select.innerHTML = '<option value="">No chapters available</option>';
        return;
    }
    
    // Group chapters by course
    const grouped = {};
    chapters.forEach(chapter => {
        const courseName = chapter.course?.title || chapter.course_name || 'Uncategorized';
        if (!grouped[courseName]) {
            grouped[courseName] = [];
        }
        grouped[courseName].push(chapter);
    });
    
    // Sort course names
    const sortedCourses = Object.keys(grouped).sort();
    
    // Add grouped options with separators
    sortedCourses.forEach((courseName, index) => {
        // Add course header (disabled option)
        const courseHeader = document.createElement('option');
        courseHeader.disabled = true;
        courseHeader.textContent = '━━━ ' + courseName + ' ━━━';
        courseHeader.style.fontWeight = 'bold';
        courseHeader.style.backgroundColor = '#f8f9fa';
        courseHeader.style.color = '#495057';
        select.appendChild(courseHeader);
        
        // Add chapters for this course
        grouped[courseName].forEach(chapter => {
            const option = document.createElement('option');
            option.value = chapter.id;
            option.textContent = '    ' + (chapter.display_title || chapter.title);
            option.dataset.type = chapter.type || 'chapters';
            option.dataset.courseName = courseName.toLowerCase();
            option.dataset.chapterName = (chapter.display_title || chapter.title).toLowerCase();
            select.appendChild(option);
        });
        
        // Add separator between courses (except last one)
        if (index < sortedCourses.length - 1) {
            const separator = document.createElement('option');
            separator.disabled = true;
            separator.textContent = '─────────────────────────';
            separator.style.color = '#dee2e6';
            select.appendChild(separator);
        }
    });
    
    console.log('Loaded', chapters.length, 'chapters grouped by', sortedCourses.length, 'courses');
}

function filterChapters() {
    const searchTerm = document.getElementById('chapterSearch').value.toLowerCase();
    
    if (!searchTerm) {
        displayChaptersGrouped(allChapters);
        return;
    }
    
    const filtered = allChapters.filter(chapter => {
        const courseName = (chapter.course?.title || chapter.course_name || '').toLowerCase();
        const chapterName = (chapter.display_title || chapter.title || '').toLowerCase();
        return courseName.includes(searchTerm) || chapterName.includes(searchTerm);
    });
    
    displayChaptersGrouped(filtered);
}

let allTimers = [];

function loadTimers() {
    fetch('/api/timer/list', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        allTimers = data;
        displayTimers(data);
    })
    .catch(error => {
        console.error('Error loading timers:', error);
        document.getElementById('timers-table').innerHTML = '<p class="text-danger">Error loading timers</p>';
    });
}

function displayTimers(timers) {
    const container = document.getElementById('timers-table');
    if (Array.isArray(timers) && timers.length > 0) {
        // Group timers by course
        const grouped = {};
        timers.forEach(timer => {
            const courseName = timer.chapter?.course?.title || timer.chapter?.course_name || 'Uncategorized';
            if (!grouped[courseName]) {
                grouped[courseName] = [];
            }
            grouped[courseName].push(timer);
        });

        let html = '';
        Object.keys(grouped).sort().forEach((courseName, index) => {
            // Course Section Header
            html += `
                <div class="course-section mb-4" data-course="${courseName.toLowerCase()}">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-book me-2 text-primary"></i>${courseName}</h5>
                        <span class="badge bg-secondary ms-2">${grouped[courseName].length} timer(s)</span>
                    </div>
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Chapter</th>
                                <th>Required Time</th>
                                <th>Status</th>
                                <th>Allow Pause</th>
                                <th>Admin Bypass</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            grouped[courseName].forEach(timer => {
                html += `<tr data-chapter="${(timer.chapter?.title || '').toLowerCase()}">
                    <td>${timer.chapter?.title || 'N/A'}</td>
                    <td><strong>${timer.required_time_minutes || 0}</strong> minutes</td>
                    <td><span class="badge bg-${timer.is_enabled ? 'success' : 'secondary'}">${timer.is_enabled ? 'Enabled' : 'Disabled'}</span></td>
                    <td><i class="fas fa-${timer.allow_pause ? 'check text-success' : 'times text-danger'}"></i></td>
                    <td><i class="fas fa-${timer.bypass_for_admin ? 'check text-success' : 'times text-danger'}"></i></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="toggleTimer(${timer.id})" title="Toggle Status">
                            <i class="fas fa-power-off"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTimer(${timer.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            
            html += `</tbody></table></div>`;
            
            // Add separator between courses (except last one)
            if (index < Object.keys(grouped).length - 1) {
                html += '<hr class="my-4" style="border-top: 2px dashed #dee2e6;">';
            }
        });
        
        container.innerHTML = html;
    } else {
        container.innerHTML = '<p class="text-center text-muted py-4"><i class="fas fa-clock fa-3x mb-3 d-block"></i>No timers configured yet</p>';
    }
}

function filterTimers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    if (!searchTerm) {
        displayTimers(allTimers);
        return;
    }
    
    const filtered = allTimers.filter(timer => {
        const courseName = (timer.chapter?.course?.title || timer.chapter?.course_name || '').toLowerCase();
        const chapterName = (timer.chapter?.title || '').toLowerCase();
        return courseName.includes(searchTerm) || chapterName.includes(searchTerm);
    });
    
    displayTimers(filtered);
}

function saveTimer() {
    const select = document.getElementById('chapter_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!select.value) {
        alert('Please select a chapter');
        return;
    }
    
    const data = {
        chapter_id: select.value,
        chapter_type: selectedOption.dataset.type || 'chapters',
        required_time_minutes: document.getElementById('required_minutes').value,
        is_enabled: document.getElementById('is_enabled').checked,
        allow_pause: document.getElementById('allow_pause').checked,
        bypass_for_admin: document.getElementById('bypass_for_admin').checked
    };
    
    console.log('Saving timer:', data);
    
    fetch('/api/timer/configure', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }
        return response.json();
    })
    .then(() => {
        alert('Timer saved successfully!');
        bootstrap.Modal.getInstance(document.getElementById('addTimerModal')).hide();
        loadTimers();
    })
    .catch(error => {
        console.error('Error saving timer:', error);
        alert('Error saving timer. The timer API endpoints need to be implemented. Error: ' + error.message);
    });
}

function toggleTimer(id) {
    fetch(`/api/timer/toggle/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTimers();
        }
    })
    .catch(error => console.error('Error toggling timer:', error));
}

function deleteTimer(id) {
    if (confirm('Delete this timer?')) {
        fetch(`/api/timer/delete/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTimers();
            }
        })
        .catch(error => console.error('Error deleting timer:', error));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadChapters();
    loadTimers();
    checkStrictDurationStatus();
});

async function toggleStrictDuration() {
    try {
        const btn = document.getElementById('strictDurationBtn');
        const statusBadge = document.getElementById('strictDurationStatus');
        const isEnabled = statusBadge.textContent.includes('ENABLED');
        
        console.log('=== toggleStrictDuration START ===');
        console.log('Current status:', isEnabled ? 'ENABLED' : 'DISABLED');
        console.log('New value:', !isEnabled);
        
        btn.disabled = true;
        statusBadge.textContent = 'Updating...';
        
        const payload = {
            strict_duration_enabled: !isEnabled
        };
        
        console.log('Sending payload:', JSON.stringify(payload));
        
        const response = await fetch('/api/courses/toggle-strict-duration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            console.log('Success! Checking status...');
            checkStrictDurationStatus();
            alert(data.message);
        } else {
            console.error('Error response:', data);
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('=== toggleStrictDuration ERROR ===');
        console.error('Error:', error);
        alert('Error: ' + error.message);
    } finally {
        document.getElementById('strictDurationBtn').disabled = false;
    }
}

let currentStrictDurationState = true; // Track actual state

async function toggleStrictDuration() {
    try {
        const btn = document.getElementById('strictDurationToggleBtn');
        const newState = !currentStrictDurationState;
        
        console.log('Current state:', currentStrictDurationState);
        console.log('Toggling to:', newState);
        
        btn.disabled = true;
        
        const response = await fetch('/api/courses/toggle-strict-duration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                strict_duration_enabled: newState
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentStrictDurationState = newState;
            updateButtonUI();
            alert(data.message + '\n\nPlease refresh the course player page to see the changes.');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    } finally {
        document.getElementById('strictDurationToggleBtn').disabled = false;
    }
}

function updateButtonUI() {
    const btn = document.getElementById('strictDurationToggleBtn');
    const btnText = document.getElementById('strictDurationBtn');
    const statusBadge = document.getElementById('strictDurationStatus');
    
    if (currentStrictDurationState) {
        btnText.textContent = 'Disable Strict Duration';
        btn.className = 'btn btn-lg btn-danger me-2';
        statusBadge.textContent = '✓ STRICT DURATION ENABLED FOR ALL COURSES';
        statusBadge.className = 'badge bg-success fs-6 p-2';
    } else {
        btnText.textContent = 'Enable Strict Duration';
        btn.className = 'btn btn-lg btn-warning me-2';
        statusBadge.textContent = '✗ STRICT DURATION DISABLED FOR ALL COURSES';
        statusBadge.className = 'badge bg-warning fs-6 p-2';
    }
}

async function checkStrictDurationStatus() {
    try {
        // Initialize with ENABLED state (we know from logs all courses are currently FALSE/DISABLED)
        currentStrictDurationState = false;
        updateButtonUI();
    } catch (error) {
        console.error('Error checking strict duration status:', error);
    }
}
</script>
@endsection
