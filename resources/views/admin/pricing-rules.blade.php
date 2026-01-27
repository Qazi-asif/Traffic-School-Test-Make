@extends('layouts.app')
@section('title', 'Florida Pricing Rules')
@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Florida Pricing Rules</h1>
    
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Add Pricing Rule</button>
    
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Type</th>
                        <th>Delivery Type</th>
                        <th>Base Price</th>
                        <th>FL Assessment Fee</th>
                        <th>Total</th>
                        <th>Effective Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="rules-list"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Pricing Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label>Course Type</label>
                        <select name="course_type" class="form-control" required>
                            <option value="BDI">BDI</option>
                            <option value="ADI">ADI</option>
                            <option value="TLSAE">TLSAE</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Delivery Type</label>
                        <select name="delivery_type" class="form-control" required>
                            <option value="internet">Internet</option>
                            <option value="in_person">In Person</option>
                            <option value="cd_rom">CD ROM</option>
                            <option value="video">Video</option>
                            <option value="dvd">DVD</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Base Price</label>
                        <input type="number" step="0.01" name="base_price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Florida Assessment Fee</label>
                        <input type="number" step="0.01" name="florida_assessment_fee" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Effective Date</label>
                        <input type="date" name="effective_date" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createRule()">Create</button>
            </div>
        </div>
    </div>
</div>

<script>
async function createRule() {
    const form = document.getElementById('createForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.is_active = true;
    
    const response = await fetch('/api/pricing-rules', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    });
    
    if (response.ok) {
        bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
        form.reset();
        loadRules();
    }
}

async function loadRules() {
    const response = await fetch('/api/pricing-rules');
    const data = await response.json();
    
    document.getElementById('rules-list').innerHTML = data.map(r => `
        <tr>
            <td>${r.course_type}</td>
            <td>${r.delivery_type}</td>
            <td>$${r.base_price}</td>
            <td>$${r.florida_assessment_fee}</td>
            <td>$${(parseFloat(r.base_price) + parseFloat(r.florida_assessment_fee)).toFixed(2)}</td>
            <td>${r.effective_date}</td>
            <td><span class="badge bg-${r.is_active ? 'success' : 'secondary'}">${r.is_active ? 'Active' : 'Inactive'}</span></td>
        </tr>
    `).join('');
}

loadRules();
</script>
@endsection
