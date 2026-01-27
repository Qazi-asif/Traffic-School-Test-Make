@extends('layouts.app')
@section('title', 'Florida Admin Dashboard')
@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Florida Admin Dashboard</h1>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Available Certificates</h5>
                    <h2 id="available-count">0</h2>
                    <small>Ready for distribution</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Used This Month</h5>
                    <h2 id="used-count">0</h2>
                    <small>Certificates issued</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Submissions</h5>
                    <h2 id="pending-count">0</h2>
                    <small>Awaiting DICDS</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Failed Submissions</h5>
                    <h2 id="failed-count">0</h2>
                    <small>Requires attention</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Certificate Inventory by Course Type</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Type</th>
                                <th>Ordered</th>
                                <th>Used</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent DICDS Submissions</h5>
                </div>
                <div class="card-body">
                    <div id="recent-submissions"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadDashboard() {
    try {
        const response = await fetch('/api/admin/florida-dashboard/stats');
        const data = await response.json();
        
        document.getElementById('available-count').textContent = data.available || 0;
        document.getElementById('used-count').textContent = data.used_this_month || 0;
        document.getElementById('pending-count').textContent = data.pending || 0;
        document.getElementById('failed-count').textContent = data.failed || 0;
        
        if (data.inventory) {
            const tbody = document.getElementById('inventory-table');
            tbody.innerHTML = data.inventory.map(item => `
                <tr>
                    <td>${item.course_type}</td>
                    <td>${item.total_ordered}</td>
                    <td>${item.total_used}</td>
                    <td>${item.available_count}</td>
                </tr>
            `).join('');
        }
        
        if (data.recent_submissions) {
            const div = document.getElementById('recent-submissions');
            div.innerHTML = data.recent_submissions.map(sub => `
                <div class="mb-2 p-2 border-bottom">
                    <strong>${sub.student_name}</strong> - ${sub.course_name}<br>
                    <small class="text-muted">${sub.submitted_at}</small>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

loadDashboard();
</script>
@endsection
