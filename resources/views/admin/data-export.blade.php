<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Export Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
        <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
            <h2>Data Export Manager</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Request Data Export</h5>
                        </div>
                        <div class="card-body">
                            <form id="exportForm">
                                <div class="mb-3">
                                    <label for="requestType" class="form-label">Request Type</label>
                                    <select class="form-control" id="requestType" required>
                                        <option value="">Select Type</option>
                                        <option value="user_data">User Data</option>
                                        <option value="enrollments">Enrollments</option>
                                        <option value="certificates">Certificates</option>
                                        <option value="payments">Payments</option>
                                        <option value="full_report">Full Report</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="exportFormat" class="form-label">Export Format</label>
                                    <select class="form-control" id="exportFormat" required>
                                        <option value="pdf">PDF</option>
                                        <option value="html">HTML</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        Your data export will include all information based on the selected type.
                                    </small>
                                </div>
                                <button type="button" onclick="requestExport()" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Generate & Download Export
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Export Information</h5>
                        </div>
                        <div class="card-body">
                            <h6>What's included in your export:</h6>
                            <ul class="small">
                                <li>Profile information</li>
                                <li>Course enrollments and progress</li>
                                <li>Payment history</li>
                                <li>Login activity</li>
                                <li>Certificates earned</li>
                                <li>Support communications</li>
                            </ul>
                            <h6 class="mt-3">Processing time:</h6>
                            <p class="small text-muted">
                                Export requests are typically processed within 24-48 hours. 
                                You'll receive an email notification when your export is ready.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Export History</h5>
                        </div>
                        <div class="card-body">
                            <div id="exportHistory">
                                <p>Loading export history...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let exportRequests = [];

        async function requestExport() {
            const requestType = document.getElementById('requestType').value;
            const exportFormat = document.getElementById('exportFormat').value;
            
            if (!requestType || !exportFormat) {
                alert('Please select both request type and format');
                return;
            }

            try {
                // Create download link
                const url = `/web/data-export/download?type=${requestType}&format=${exportFormat}`;
                window.open(url, '_blank');
                
                alert('Export generated! Download should start automatically.');
                loadExportHistory();
                
            } catch (error) {
                console.error('Export request error:', error);
                alert(`Error: ${error.message}`);
            }
        }

        async function loadExportHistory() {
            // Since we don't have a specific endpoint for user's export history,
            // we'll simulate it for now
            const mockHistory = [
                {
                    id: 1,
                    request_type: 'gdpr',
                    status: 'completed',
                    requested_at: new Date().toISOString(),
                    completed_at: new Date().toISOString()
                }
            ];

            displayExportHistory(mockHistory);
        }

        function displayExportHistory(history) {
            if (!history || history.length === 0) {
                document.getElementById('exportHistory').innerHTML = '<p>No export requests found.</p>';
                return;
            }

            const table = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Request Type</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Completed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${history.map(request => `
                                <tr>
                                    <td>${request.request_type.toUpperCase()}</td>
                                    <td>
                                        <span class="badge ${getStatusClass(request.status)}">
                                            ${request.status.toUpperCase()}
                                        </span>
                                    </td>
                                    <td>${formatDate(request.requested_at)}</td>
                                    <td>${request.completed_at ? formatDate(request.completed_at) : '-'}</td>
                                    <td>
                                        ${request.status === 'completed' ? 
                                            `<button onclick="downloadExport(${request.id})" class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i> Download
                                            </button>` : 
                                            '<span class="text-muted">Processing...</span>'
                                        }
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('exportHistory').innerHTML = table;
        }

        async function downloadExport(id) {
            try {
                const response = await fetch(`/api/data-export/download/${id}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Download failed');

                const result = await response.json();
                
                // Create a temporary link to download the file
                const link = document.createElement('a');
                link.href = result.download_url;
                link.download = `data-export-${id}.zip`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
            } catch (error) {
                console.error('Download error:', error);
                alert('Error downloading export file');
            }
        }

        function getStatusClass(status) {
            const classes = {
                pending: 'bg-warning',
                processing: 'bg-info',
                completed: 'bg-success',
                failed: 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }

        loadExportHistory();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
