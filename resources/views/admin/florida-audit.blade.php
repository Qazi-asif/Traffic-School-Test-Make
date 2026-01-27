<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Florida Audit Trail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list"></i> Florida Audit Trail</h2>
            <button class="btn btn-primary" onclick="showReportModal()">
                <i class="fas fa-file-export"></i> Generate Report
            </button>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filters</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="dateFrom" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="dateTo" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Action Type</label>
                        <select class="form-control" id="actionFilter">
                            <option value="">All Actions</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="enrollment">Enrollment</option>
                            <option value="certificate">Certificate</option>
                            <option value="payment">Payment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary d-block" onclick="loadAuditTrail()">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Trail Table -->
        <div class="card">
            <div class="card-header">
                <h5>Audit Trail Records</h5>
            </div>
            <div class="card-body">
                <div id="auditTrailTable">
                    <p>Loading audit trail...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Generation Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Audit Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select class="form-control" id="reportType" required>
                                <option value="full">Full Audit Trail</option>
                                <option value="security">Security Events Only</option>
                                <option value="compliance">Compliance Report</option>
                                <option value="user_activity">User Activity Report</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="reportStartDate" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="reportEndDate" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <select class="form-control" id="reportFormat">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="generateReport()">Generate Report</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadAuditTrail();
            // Set default report dates
            document.getElementById('reportStartDate').value = new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0];
            document.getElementById('reportEndDate').value = new Date().toISOString().split('T')[0];
        });

        async function loadAuditTrail() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const actionFilter = document.getElementById('actionFilter').value;

            try {
                const params = new URLSearchParams({
                    date_from: dateFrom,
                    date_to: dateTo,
                    action: actionFilter
                });

                const response = await fetch(`/api/florida-audit-trail?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    displayAuditTrail(data);
                } else {
                    document.getElementById('auditTrailTable').innerHTML = '<p class="text-danger">Error loading audit trail</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('auditTrailTable').innerHTML = '<p class="text-danger">Error loading audit trail</p>';
            }
        }

        function displayAuditTrail(auditLogs) {
            const container = document.getElementById('auditTrailTable');
            
            if (auditLogs.length === 0) {
                container.innerHTML = '<p>No audit records found for the selected criteria.</p>';
                return;
            }

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Details</th>
                                <th>Risk Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${auditLogs.map(log => `
                                <tr>
                                    <td>${new Date(log.created_at).toLocaleString()}</td>
                                    <td>
                                        <span class="badge bg-${getActionColor(log.action)}">${log.action}</span>
                                    </td>
                                    <td>${log.user_email || 'System'}</td>
                                    <td>${log.ip_address || 'N/A'}</td>
                                    <td>${log.details || 'N/A'}</td>
                                    <td>
                                        <span class="badge bg-${getRiskColor(log.risk_level)}">${(log.risk_level || 'low').toUpperCase()}</span>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function showReportModal() {
            new bootstrap.Modal(document.getElementById('reportModal')).show();
        }

        async function generateReport() {
            const reportType = document.getElementById('reportType').value;
            const startDate = document.getElementById('reportStartDate').value;
            const endDate = document.getElementById('reportEndDate').value;
            const format = document.getElementById('reportFormat').value;

            if (!startDate || !endDate) {
                alert('Please select start and end dates');
                return;
            }

            try {
                const params = new URLSearchParams({
                    type: reportType,
                    start_date: startDate,
                    end_date: endDate,
                    format: format
                });

                const response = await fetch(`/api/florida-audit-report?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `florida-audit-${reportType}-${startDate}-to-${endDate}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                    alert('Report generated successfully!');
                } else {
                    alert('Failed to generate report');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error generating report');
            }
        }

        function getActionColor(action) {
            switch(action.toLowerCase()) {
                case 'login': return 'success';
                case 'logout': return 'secondary';
                case 'failed_login': return 'danger';
                case 'enrollment': return 'info';
                case 'certificate': return 'warning';
                case 'payment': return 'primary';
                default: return 'secondary';
            }
        }

        function getRiskColor(riskLevel) {
            switch(riskLevel.toLowerCase()) {
                case 'high': return 'danger';
                case 'medium': return 'warning';
                case 'low': return 'success';
                default: return 'secondary';
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <x-footer />
</body>
</html>
