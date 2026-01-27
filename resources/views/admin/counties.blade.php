@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-map-marker-alt me-2"></i>Florida Counties</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCountyModal">
            <i class="fas fa-plus me-2"></i>Add County
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="counties-table"></div>
        </div>
    </div>
</div>

<!-- Add County Modal -->
<div class="modal fade" id="addCountyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add County</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="countyForm">
                    <div class="mb-3">
                        <label class="form-label">County Name</label>
                        <input type="text" class="form-control" id="county_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">County Code</label>
                        <input type="text" class="form-control" id="county_code" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveCounty()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadCounties() {
    fetch('/api/counties', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('counties-table');
        if (data.length > 0) {
            let html = '<table class="table table-hover"><thead><tr><th>Name</th><th>Code</th><th>State</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            data.forEach(county => {
                html += `<tr>
                    <td>${county.name}</td>
                    <td><code>${county.code}</code></td>
                    <td>${county.state_code}</td>
                    <td><span class="badge bg-${county.is_active ? 'success' : 'secondary'}">${county.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editCounty(${county.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCounty(${county.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-center">No counties found</p>';
        }
    })
    .catch(() => {
        document.getElementById('counties-table').innerHTML = '<p class="text-danger">Error loading counties</p>';
    });
}

function saveCounty() {
    const editId = document.getElementById('countyForm').dataset.editId;
    const data = {
        name: document.getElementById('county_name').value,
        code: document.getElementById('county_code').value,
        is_active: document.getElementById('is_active').checked
    };
    
    const url = editId ? `/api/counties/${editId}` : '/api/counties';
    const method = editId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById('addCountyModal')).hide();
        document.getElementById('countyForm').reset();
        delete document.getElementById('countyForm').dataset.editId;
        document.querySelector('#addCountyModal .modal-title').textContent = 'Add County';
        loadCounties();
    });
}

function editCounty(id) {
    fetch(`/api/counties/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(county => {
        document.getElementById('county_name').value = county.name;
        document.getElementById('county_code').value = county.code;
        document.getElementById('is_active').checked = county.is_active;
        document.getElementById('countyForm').dataset.editId = id;
        document.querySelector('#addCountyModal .modal-title').textContent = 'Edit County';
        new bootstrap.Modal(document.getElementById('addCountyModal')).show();
    });
}

function deleteCounty(id) {
    if (confirm('Delete this county?')) {
        fetch(`/api/counties/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(() => loadCounties())
        .catch(error => console.error('Error deleting county:', error));
    }
}

document.addEventListener('DOMContentLoaded', loadCounties);
</script>
@endsection
