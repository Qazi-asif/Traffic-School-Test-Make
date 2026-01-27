@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 ">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-stamp me-2"></i>State Stamps Management</h2>
        <button class="btn btn-primary" onclick="resetForm()" data-bs-toggle="modal" data-bs-target="#addStampModal">
            <i class="fas fa-plus me-2"></i>Add State Stamp
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>State Code</th>
                            <th>State Name</th>
                            <th>Logo</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stamps as $stamp)
                        <tr>
                            <td><strong>{{ $stamp->state_code }}</strong></td>
                            <td>{{ $stamp->state_name }}</td>
                            <td>
                                @if($stamp->logo_path)
                                    <img src="{{ asset('storage/' . $stamp->logo_path) }}" alt="{{ $stamp->state_name }}" style="max-height: 50px;">
                                @else
                                    <span class="text-muted">No logo</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $stamp->is_active ? 'success' : 'secondary' }}">
                                    {{ $stamp->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick='editStamp(@json($stamp))'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteStamp({{ $stamp->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addStampModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add State Stamp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stampForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="stamp_id" name="stamp_id">
                <input type="hidden" id="form_method" name="_method" value="POST">
                <div class="modal-body">
                    <div id="currentLogo" style="display: none; text-align: center; margin-bottom: 15px;">
                        <img id="currentLogoImg" src="" alt="Current Logo" style="max-height: 100px;">
                        <div class="text-muted small">Current Logo</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State Code</label>
                        <select class="form-select" id="state_code" name="state_code" required>
                            <option value="">Select a State</option>
                            @foreach($states as $state)
                                <option value="{{ $state->code }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State Name</label>
                        <input type="text" class="form-control" id="state_name" name="state_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo Image</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted">Recommended: PNG with transparent background, 200x200px</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked style="width: 3em; height: 1.5em; cursor: pointer;">
                            <label class="form-check-label" for="is_active" style="cursor: pointer; margin-left: 0.5em;">
                                <span id="activeLabel">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update label when checkbox changes
document.getElementById('is_active').addEventListener('change', function() {
    document.getElementById('activeLabel').textContent = this.checked ? 'Active' : 'Inactive';
});

// Auto-populate state name when state code is selected
document.getElementById('state_code').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const stateName = selectedOption.text; // Now just the state name
        document.getElementById('state_name').value = stateName;
    } else {
        document.getElementById('state_name').value = '';
    }
});

document.getElementById('stampForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const stampId = document.getElementById('stamp_id').value;
    const method = document.getElementById('form_method').value;
    
    // Handle checkbox - send 1 or 0
    const isActiveCheckbox = document.getElementById('is_active');
    formData.delete('is_active'); // Remove the default value
    formData.append('is_active', isActiveCheckbox.checked ? '1' : '0');
    
    // Handle disabled state_code dropdown during edit
    const stateCodeSelect = document.getElementById('state_code');
    if (stateCodeSelect.disabled && stampId) {
        formData.append('state_code', stateCodeSelect.value);
    }
    
    // Determine URL and method
    let url = '/admin/state-stamps';
    if (stampId && method === 'PUT') {
        url = `/admin/state-stamps/${stampId}`;
        formData.append('_method', 'PUT');
    }
    
    console.log('Submitting to:', url);
    console.log('Method:', method);
    console.log('Stamp ID:', stampId);
    console.log('Is Active:', isActiveCheckbox.checked);
    console.log('State Code:', stateCodeSelect.value);
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            const text = await response.text();
            console.error('Server response:', text);
            alert('Server error: ' + response.status + '\nCheck console for details');
            return;
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            alert('Server returned non-JSON response. Check console for details.');
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            alert('State stamp saved successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save'));
        }
    } catch (error) {
        alert('Error saving state stamp: ' + error.message);
        console.error('Full error:', error);
    }
});

function resetForm() {
    // Reset modal title
    document.getElementById('modalTitle').textContent = 'Add State Stamp';
    
    // Clear form fields
    document.getElementById('stampForm').reset();
    document.getElementById('stamp_id').value = '';
    document.getElementById('form_method').value = 'POST';
    
    // Hide current logo
    document.getElementById('currentLogo').style.display = 'none';
    
    // Reset checkbox and label
    document.getElementById('is_active').checked = true;
    document.getElementById('activeLabel').textContent = 'Active';
    
    // Make state_code dropdown enabled and logo required for new stamps
    document.getElementById('state_code').removeAttribute('disabled');
    document.getElementById('logo').setAttribute('required', 'required');
}

function editStamp(stamp) {
    // Change modal title
    document.getElementById('modalTitle').textContent = 'Edit State Stamp';
    
    // Populate form fields
    document.getElementById('stamp_id').value = stamp.id;
    document.getElementById('state_code').value = stamp.state_code;
    document.getElementById('state_code').setAttribute('disabled', 'disabled');
    document.getElementById('state_name').value = stamp.state_name;
    document.getElementById('description').value = stamp.description || '';
    
    // Set checkbox and update label
    const isActiveCheckbox = document.getElementById('is_active');
    isActiveCheckbox.checked = stamp.is_active == 1 || stamp.is_active === true;
    document.getElementById('activeLabel').textContent = isActiveCheckbox.checked ? 'Active' : 'Inactive';
    
    // Show current logo if exists
    if (stamp.logo_path) {
        document.getElementById('currentLogoImg').src = '/storage/' + stamp.logo_path;
        document.getElementById('currentLogo').style.display = 'block';
    } else {
        document.getElementById('currentLogo').style.display = 'none';
    }
    
    // Change form method to PUT for update
    document.getElementById('form_method').value = 'PUT';
    
    // Make logo optional for edit
    document.getElementById('logo').removeAttribute('required');
    
    // Open the modal
    const modal = new bootstrap.Modal(document.getElementById('addStampModal'));
    modal.show();
}

function deleteStamp(id) {
    if (confirm('Delete this state stamp?')) {
        fetch(`/admin/state-stamps/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection
