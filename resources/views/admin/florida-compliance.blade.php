@extends('layouts.app')

@section('title', 'Florida Compliance Manager')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Florida Compliance Manager</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-success" onclick="runAllChecks()">
                        <i class="fas fa-play"></i> Run All Checks
                    </button>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Run New Check -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Run Compliance Check</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <select class="form-control" id="checkType">
                                    <option value="">Select Check Type</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="annual">Annual</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="Check Name" id="checkName">
                            </div>
                            <button class="btn btn-primary w-100" onclick="runCheck()">Run Check</button>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Checks -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Upcoming Due Checks</h5>
                        </div>
                        <div class="card-body">
                            <div id="upcomingChecks">
                                <div class="alert alert-warning">
                                    <strong>Certificate Inventory Audit</strong><br>
                                    Due: Tomorrow
                                </div>
                                <div class="alert alert-info">
                                    <strong>DICDS Submission Review</strong><br>
                                    Due: In 3 days
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compliance History -->
            <div class="card">
                <div class="card-header">
                    <h5>Compliance Check History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Check Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Performed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="complianceHistory">
                                <tr><td colspan="5">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadComplianceHistory();
});

function loadComplianceHistory() {
    document.getElementById('complianceHistory').innerHTML = `
        <tr>
            <td>Certificate Inventory Audit</td>
            <td>daily</td>
            <td><span class="badge bg-success">passed</span></td>
            <td>Admin User</td>
            <td>${new Date().toLocaleDateString()}</td>
        </tr>
        <tr>
            <td>Security Log Review</td>
            <td>weekly</td>
            <td><span class="badge bg-warning">warning</span></td>
            <td>System</td>
            <td>${new Date(Date.now() - 86400000).toLocaleDateString()}</td>
        </tr>
        <tr>
            <td>DICDS Connectivity Test</td>
            <td>daily</td>
            <td><span class="badge bg-danger">failed</span></td>
            <td>System</td>
            <td>${new Date(Date.now() - 172800000).toLocaleDateString()}</td>
        </tr>
    `;
}

function runCheck() {
    const type = document.getElementById('checkType').value;
    const name = document.getElementById('checkName').value;
    
    if (!type || !name) {
        alert('Please select check type and enter check name');
        return;
    }
    
    alert(`Running ${type} check: ${name}`);
    document.getElementById('checkType').value = '';
    document.getElementById('checkName').value = '';
    loadComplianceHistory();
}

function runAllChecks() {
    alert('Running all compliance checks...');
    loadComplianceHistory();
}
</script>
@endsection
