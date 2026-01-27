@extends('layouts.app')

@section('title', 'Legal Documents')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Legal Documents Management</h1>
    
    <button class="btn btn-primary mb-3" onclick="showCreateModal()">Create New Document</button>
    
    <div class="card">
        <div class="card-body">
            <div id="documents"></div>
        </div>
    </div>
</div>

<!-- Create Document Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Legal Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select class="form-select" id="documentType" required>
                            <option value="privacy_policy">Privacy Policy</option>
                            <option value="terms_of_service">Terms of Service</option>
                            <option value="copyright_notice">Copyright Notice</option>
                            <option value="disclaimer">Disclaimer</option>
                            <option value="refund_policy">Refund Policy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea class="form-control" id="content" rows="10" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Version</label>
                            <input type="text" class="form-control" id="version" placeholder="e.g., 1.0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effective Date</label>
                            <input type="date" class="form-control" id="effectiveDate" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requiresConsent">
                            <label class="form-check-label" for="requiresConsent">
                                Requires User Consent
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitDocument()">Create Document</button>
            </div>
        </div>
    </div>
</div>

<script>
async function loadDocuments() {
    const response = await fetch('/web/legal-documents');
    const docs = await response.json();
    document.getElementById('documents').innerHTML = docs.length ?
        `<table class="table"><thead><tr><th>Type</th><th>Title</th><th>Version</th><th>Effective Date</th><th>Status</th><th>Consent Required</th></tr></thead><tbody>
        ${docs.map(d => `<tr><td>${d.document_type}</td><td>${d.title}</td><td>${d.version}</td><td>${d.effective_date}</td>
        <td><span class="badge bg-${d.is_active ? 'success' : 'secondary'}">${d.is_active ? 'Active' : 'Inactive'}</span></td>
        <td><span class="badge bg-${d.requires_consent ? 'warning' : 'info'}">${d.requires_consent ? 'Yes' : 'No'}</span></td></tr>`).join('')}</tbody></table>` :
        '<p>No documents found</p>';
}

function showCreateModal() {
    document.getElementById('createForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('createModal'));
    modal.show();
}

async function submitDocument() {
    const data = {
        document_type: document.getElementById('documentType').value,
        title: document.getElementById('title').value,
        content: document.getElementById('content').value,
        version: document.getElementById('version').value,
        effective_date: document.getElementById('effectiveDate').value,
        requires_consent: document.getElementById('requiresConsent').checked
    };
    
    try {
        const response = await fetch('/web/legal-documents', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
            loadDocuments();
            alert('Document created successfully');
        } else {
            alert('Error creating document');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating document');
    }
}

loadDocuments();
</script>
@endsection
