@extends('layouts.app')
@section('title', 'Certificate Inventory')
@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Certificate Inventory Management</h1>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>Inventory Status</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#distributeModal">Distribute Certificates</button>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Type</th>
                        <th>Delivery Type</th>
                        <th>Total Ordered</th>
                        <th>Total Used</th>
                        <th>Available</th>
                        <th>Provider Hold</th>
                        <th>School Hold</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody id="inventory-list"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Distribute Modal -->
<div class="modal fade" id="distributeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Distribute Certificates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Certificate distribution functionality will be implemented with school selection and amount tracking.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
async function loadInventory() {
    const response = await fetch('/api/admin/certificate-inventory');
    const data = await response.json();
    
    document.getElementById('inventory-list').innerHTML = data.map(item => `
        <tr>
            <td>${item.course_type}</td>
            <td>${item.delivery_type}</td>
            <td>${item.total_ordered}</td>
            <td>${item.total_used}</td>
            <td>${item.available_count}</td>
            <td>${item.provider_hold}</td>
            <td>${item.school_hold}</td>
            <td>${item.last_updated || 'N/A'}</td>
        </tr>
    `).join('');
}

loadInventory();
</script>
@endsection
