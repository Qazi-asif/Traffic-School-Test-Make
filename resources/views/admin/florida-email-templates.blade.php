@extends('layouts.app')
@section('title', 'Florida Email Templates')
@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Florida Email Templates</h1>
    
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Create Template</button>
    
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>FL Required</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="templates-list"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Email Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="enrollment">Enrollment</option>
                            <option value="payment">Payment</option>
                            <option value="completion">Completion</option>
                            <option value="certificate">Certificate</option>
                            <option value="compliance">Compliance</option>
                            <option value="dicds">DICDS</option>
                            <option value="admin_alert">Admin Alert</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Content (HTML)</label>
                        <textarea name="content" class="form-control" rows="10" required></textarea>
                        <small>Available variables: @{{ florida_certificate_number }}, @{{ florida_assessment_fee }}, @{{ citation_number }}, @{{ court_name }}</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createTemplate()">Create</button>
            </div>
        </div>
    </div>
</div>

<script>
async function createTemplate() {
    const form = document.getElementById('createForm');
    const formData = new FormData(form);
    
    const response = await fetch('/api/florida-email-templates', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(Object.fromEntries(formData))
    });
    
    if (response.ok) {
        bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
        form.reset();
        loadTemplates();
    }
}

async function loadTemplates() {
    const response = await fetch('/api/florida-email-templates');
    const data = await response.json();
    
    document.getElementById('templates-list').innerHTML = data.map(t => `
        <tr>
            <td>${t.name}</td>
            <td><span class="badge bg-info">${t.category}</span></td>
            <td>${t.subject}</td>
            <td>${t.is_florida_required ? '<span class="badge bg-danger">Required</span>' : ''}</td>
            <td><span class="badge bg-${t.is_active ? 'success' : 'secondary'}">${t.is_active ? 'Active' : 'Inactive'}</span></td>
            <td><button class="btn btn-sm btn-primary" onclick="testEmail(${t.id})">Test</button></td>
        </tr>
    `).join('');
}

function testEmail(id) {
    const email = prompt('Enter test email address:');
    if (email) {
        fetch(`/api/florida-email-templates/${id}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ email })
        });
    }
}

loadTemplates();
</script>
@endsection
