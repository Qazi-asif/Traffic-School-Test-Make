<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Template Management</title>
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
        <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Email Template Management</h2>
            <button onclick="showCreateModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Template
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="templates-table">
                    <p>Loading templates...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Email Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="templateForm">
                        <div class="mb-3">
                            <label class="form-label">Template Name</label>
                            <input type="text" class="form-control" id="templateName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-control" id="category" required>
                                <option value="">Select Category</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="system">System</option>
                                <option value="marketing">Marketing</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" id="content" rows="10" required></textarea>
                            <small class="text-muted">Use variables like @{{user_name}}, @{{course_title}}, etc.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button onclick="saveTemplate()" class="btn btn-primary">Save Template</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadTemplates() {
            try {
                const response = await fetch('/web/admin/email-templates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                const templates = await response.json();
                displayTemplates(templates);
            } catch (error) {
                document.getElementById('templates-table').innerHTML = '<p class="text-danger">Error loading templates</p>';
            }
        }

        function displayTemplates(templates) {
            const container = document.getElementById('templates-table');
            
            if (templates.length === 0) {
                container.innerHTML = '<p>No templates found.</p>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${templates.map(template => `
                                <tr>
                                    <td><strong>${template.name}</strong></td>
                                    <td><span class="badge bg-secondary">${template.category.toUpperCase()}</span></td>
                                    <td>${template.subject}</td>
                                    <td><span class="badge ${template.is_active ? 'bg-success' : 'bg-secondary'}">${template.is_active ? 'Active' : 'Inactive'}</span></td>
                                    <td>
                                        <button onclick="testTemplate(${template.id})" class="btn btn-sm btn-outline-info">Test</button>
                                        <button onclick="editTemplate(${template.id})" class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button onclick="deleteTemplate(${template.id})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function showCreateModal() {
            document.getElementById('templateForm').reset();
            new bootstrap.Modal(document.getElementById('templateModal')).show();
        }

        async function saveTemplate() {
            const form = document.getElementById('templateForm');
            const templateId = form.dataset.templateId;
            
            const data = {
                name: document.getElementById('templateName').value,
                category: document.getElementById('category').value,
                subject: document.getElementById('subject').value,
                content: document.getElementById('content').value
            };

            try {
                const url = templateId ? `/web/admin/email-templates/${templateId}` : '/web/admin/email-templates';
                const method = templateId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
                    delete form.dataset.templateId;
                    loadTemplates();
                    alert('Template saved successfully!');
                }
            } catch (error) {
                alert('Error saving template');
            }
        }

        async function editTemplate(id) {
            try {
                const response = await fetch(`/web/admin/email-templates/${id}`);
                const template = await response.json();
                
                document.getElementById('templateName').value = template.name;
                document.getElementById('category').value = template.category;
                document.getElementById('subject').value = template.subject;
                document.getElementById('content').value = template.content;
                
                // Store template ID for update
                document.getElementById('templateForm').dataset.templateId = id;
                
                new bootstrap.Modal(document.getElementById('templateModal')).show();
            } catch (error) {
                console.error('Error loading template:', error);
                alert('Failed to load template');
            }
        }

        async function testTemplate(id) {
            const email = prompt('Enter test email address:');
            if (email) {
                try {
                    await fetch(`/api/email-templates/${id}/test`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email })
                    });
                    alert('Test email sent!');
                } catch (error) {
                    alert('Error sending test email');
                }
            }
        }

        async function deleteTemplate(id) {
            if (confirm('Are you sure?')) {
                try {
                    await fetch(`/api/email-templates/${id}`, { method: 'DELETE' });
                    loadTemplates();
                    alert('Template deleted');
                } catch (error) {
                    alert('Error deleting template');
                }
            }
        }

        loadTemplates();
    </script>
    </div> <!-- Close main content div from navbar -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
