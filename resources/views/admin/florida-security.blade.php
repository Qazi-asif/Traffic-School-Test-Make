@extends('layouts.app')

@section('title', 'Florida Security Dashboard')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Florida Security Dashboard</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Security Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Security Events (24h)</h5>
                            <h2 id="events24h">Loading...</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5>Failed Logins</h5>
                            <h2 id="failedLogins">Loading...</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>High Risk Events</h5>
                            <h2 id="highRisk">Loading...</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="card">
                <div class="card-header">
                    <h5>Recent Security Events</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>Risk Level</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="securityLogs">
                                <tr><td colspan="4">Loading...</td></tr>
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
    loadSecurityData();
});

async function loadSecurityData() {
    try {
        const response = await fetch('/api/florida-security-data', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const data = await response.json();
            
            // Update stats
            document.getElementById('events24h').textContent = data.stats.events24h;
            document.getElementById('failedLogins').textContent = data.stats.failedLogins;
            document.getElementById('highRisk').textContent = data.stats.highRisk;
            
            // Update recent events table
            const tbody = document.getElementById('securityLogs');
            if (data.recentEvents.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No recent security events</td></tr>';
            } else {
                tbody.innerHTML = data.recentEvents.map(event => `
                    <tr>
                        <td>
                            <span class="badge bg-${getBadgeColor(event.event)}">${event.event}</span>
                        </td>
                        <td>${event.user}</td>
                        <td>
                            <span class="badge bg-${getRiskColor(event.risk_level)}">${event.risk_level.toUpperCase()}</span>
                        </td>
                        <td>${event.time}</td>
                    </tr>
                `).join('');
            }
        } else {
            console.error('Failed to load security data');
        }
    } catch (error) {
        console.error('Error loading security data:', error);
        // Show error state
        document.getElementById('events24h').textContent = 'Error';
        document.getElementById('failedLogins').textContent = 'Error';
        document.getElementById('highRisk').textContent = 'Error';
    }
}

function getBadgeColor(eventType) {
    switch(eventType.toLowerCase()) {
        case 'login success': return 'success';
        case 'failed login': return 'danger';
        case 'password reset': return 'warning';
        case 'account locked': return 'danger';
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
@endsection
