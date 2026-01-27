<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Dashboard</title>
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
        <h2>Security Dashboard</h2>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title" id="totalEvents">0</h5>
                            <p class="card-text">Total Events</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-warning" id="failedLogins">0</h5>
                            <p class="card-text">Failed Logins Today</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-danger" id="highRiskEvents">0</h5>
                            <p class="card-text">High Risk Events</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-danger" id="criticalEvents">0</h5>
                            <p class="card-text">Critical Events</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Security Events</h5>
                    <button onclick="refreshEvents()" class="btn btn-sm btn-primary">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="eventsTable">
                        <p>Loading security events...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadDashboard() {
            try {
                const response = await fetch('/web/audit/dashboard', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                
                // Update stats
                document.getElementById('totalEvents').textContent = data.stats.total_events;
                document.getElementById('failedLogins').textContent = data.stats.failed_logins_today;
                document.getElementById('highRiskEvents').textContent = data.stats.high_risk_events;
                document.getElementById('criticalEvents').textContent = data.stats.critical_events;
                
                // Update events table
                displayEvents(data.recent_events);
                
            } catch (error) {
                console.error('Failed to load dashboard:', error);
                document.getElementById('eventsTable').innerHTML = '<p class="text-danger">Error loading security events</p>';
            }
        }

        function displayEvents(events) {
            if (!events || events.length === 0) {
                document.getElementById('eventsTable').innerHTML = '<p>No recent events found.</p>';
                return;
            }

            const table = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Event</th>
                                <th>Risk Level</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${events.map(event => `
                                <tr>
                                    <td>${formatDate(event.created_at)}</td>
                                    <td>${event.user?.name || 'System'}</td>
                                    <td>${event.description}</td>
                                    <td><span class="${getRiskClass(event.risk_level)}">${event.risk_level.toUpperCase()}</span></td>
                                    <td>${event.ip_address}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('eventsTable').innerHTML = table;
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }

        function getRiskClass(level) {
            const classes = {
                low: 'badge bg-success',
                medium: 'badge bg-warning',
                high: 'badge bg-danger',
                critical: 'badge bg-dark'
            };
            return classes[level] || 'badge bg-secondary';
        }

        function refreshEvents() {
            loadDashboard();
        }

        // Auto-refresh every 30 seconds
        setInterval(loadDashboard, 30000);
        
        loadDashboard();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
