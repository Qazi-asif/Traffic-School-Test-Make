@extends('layouts.app')

@section('title', 'Florida Data Export Tool')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Florida Data Export Tool</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-info" onclick="showHelp()">
                        <i class="fas fa-question-circle"></i> Help
                    </button>
                </div>
            </div>

            <!-- Request Export -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Request Data Export</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control" id="exportType">
                                <option value="">Select Export Type</option>
                                <option value="gdpr">GDPR Request</option>
                                <option value="ccpa">CCPA Request</option>
                                <option value="florida_public_records">Florida Public Records</option>
                                <option value="internal_audit">Internal Audit</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary w-100" onclick="requestExport()">
                                <i class="fas fa-download"></i> Request Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Requests -->
            <div class="card">
                <div class="card-header">
                    <h5>Export Requests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Requested</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="exportRequests">
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
    loadExportRequests();
});

function loadExportRequests() {
    document.getElementById('exportRequests').innerHTML = `
        <tr>
            <td>florida_public_records</td>
            <td><span class="badge bg-success">completed</span></td>
            <td>${new Date(Date.now() - 86400000).toLocaleString()}</td>
            <td>${new Date(Date.now() - 82800000).toLocaleString()}</td>
            <td><button class="btn btn-sm btn-success" onclick="downloadExport(1)">Download</button></td>
        </tr>
        <tr>
            <td>gdpr</td>
            <td><span class="badge bg-warning">processing</span></td>
            <td>${new Date(Date.now() - 3600000).toLocaleString()}</td>
            <td>-</td>
            <td><button class="btn btn-sm btn-primary" onclick="checkStatus(2)">Check Status</button></td>
        </tr>
        <tr>
            <td>internal_audit</td>
            <td><span class="badge bg-secondary">pending</span></td>
            <td>${new Date(Date.now() - 1800000).toLocaleString()}</td>
            <td>-</td>
            <td><button class="btn btn-sm btn-primary" onclick="checkStatus(3)">Check Status</button></td>
        </tr>
    `;
}

function requestExport() {
    const exportType = document.getElementById('exportType').value;
    
    if (!exportType) {
        alert('Please select an export type');
        return;
    }
    
    alert(`Export request submitted for: ${exportType}`);
    document.getElementById('exportType').value = '';
    loadExportRequests();
}

function downloadExport(id) {
    alert(`Downloading export ${id}...`);
}

function checkStatus(id) {
    alert(`Checking status for export ${id}...`);
}

function showHelp() {
    alert('Florida Data Export Tool Help:\\n\\n- GDPR: General Data Protection Regulation exports\\n- CCPA: California Consumer Privacy Act exports\\n- Florida Public Records: State-required public records\\n- Internal Audit: Internal compliance audits');
}
</script>
@endsection
