@extends('layouts.app')
@section('title', 'Florida Fee Remittances')
@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Florida Fee Remittances</h1>
    
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Create New Remittance</button>
    
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Fees</th>
                        <th>Courses</th>
                        <th>Method</th>
                        <th>FL Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="remittances-list"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Remittance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label>Remittance Date</label>
                        <input type="date" name="remittance_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Total Assessment Fees</label>
                        <input type="number" step="0.01" name="total_assessment_fees" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Total Courses</label>
                        <input type="number" name="total_courses" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="check">Check</option>
                            <option value="electronic">Electronic</option>
                            <option value="money_order">Money Order</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createRemittance()">Create</button>
            </div>
        </div>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit to Florida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Florida Reference Number</label>
                    <input type="text" id="florida_reference" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRemittanceId = null;

async function createRemittance() {
    const form = document.getElementById('createForm');
    const formData = new FormData(form);
    
    const response = await fetch('/api/florida-remittances', {
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
        loadRemittances();
    }
}

function submitRemittance(id) {
    currentRemittanceId = id;
    new bootstrap.Modal(document.getElementById('submitModal')).show();
}

async function confirmSubmit() {
    const reference = document.getElementById('florida_reference').value;
    if (!reference) return;
    
    const response = await fetch(`/api/florida-remittances/${currentRemittanceId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ florida_reference_number: reference })
    });
    
    if (response.ok) {
        bootstrap.Modal.getInstance(document.getElementById('submitModal')).hide();
        document.getElementById('florida_reference').value = '';
        loadRemittances();
    }
}

async function loadRemittances() {
    const response = await fetch('/api/florida-remittances');
    const data = await response.json();
    
    document.getElementById('remittances-list').innerHTML = data.map(r => `
        <tr>
            <td>${r.remittance_date}</td>
            <td>$${r.total_assessment_fees}</td>
            <td>${r.total_courses}</td>
            <td>${r.payment_method}</td>
            <td>${r.florida_reference_number || 'N/A'}</td>
            <td><span class="badge bg-${r.processed_by_florida ? 'success' : 'warning'}">${r.processed_by_florida ? 'Processed' : 'Pending'}</span></td>
            <td>${!r.processed_by_florida ? `<button class="btn btn-sm btn-primary" onclick="submitRemittance(${r.id})">Submit</button>` : ''}</td>
        </tr>
    `).join('');
}

loadRemittances();
</script>
@endsection
