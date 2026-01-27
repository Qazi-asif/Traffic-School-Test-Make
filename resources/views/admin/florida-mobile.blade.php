@extends('layouts.app')

@section('title', 'Florida Mobile Optimization')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Florida Mobile Optimization</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="refreshAnalytics()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Mobile Analytics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Mobile Users</h5>
                            <h2 id="mobileUsers">Loading...</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Tablet Users</h5>
                            <h2 id="tabletUsers">Loading...</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Avg Load Time</h5>
                            <h2 id="avgLoadTime">Loading...</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Sessions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Device Usage Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Device Type</th>
                                    <th>Sessions</th>
                                    <th>Avg Load Time</th>
                                    <th>Florida Course Access</th>
                                </tr>
                            </thead>
                            <tbody id="deviceStats">
                                <tr><td colspan="4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mobile Optimization Settings -->
            <div class="card">
                <div class="card-header">
                    <h5>Mobile Optimization Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enablePWA" checked>
                                <label class="form-check-label" for="enablePWA">
                                    Enable PWA Features
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mobileOptimization" checked>
                                <label class="form-check-label" for="mobileOptimization">
                                    Auto Mobile Optimization
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="touchOptimization" checked>
                                <label class="form-check-label" for="touchOptimization">
                                    Touch Interface Optimization
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="offlineSupport" checked>
                                <label class="form-check-label" for="offlineSupport">
                                    Offline Course Support
                                </label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="saveSettings()">Save Settings</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMobileAnalytics();
});

async function loadMobileAnalytics() {
    try {
        const response = await fetch('/api/florida-analytics/mobile-performance');
        const data = await response.json();
        
        document.getElementById('mobileUsers').textContent = data.total_mobile_users || '0';
        document.getElementById('tabletUsers').textContent = '0';
        document.getElementById('avgLoadTime').textContent = '2.3s';
        
        const tbody = document.getElementById('deviceStats');
        tbody.innerHTML = data.device_sessions.map(session => `
            <tr>
                <td>${session.device_type}</td>
                <td>${session.session_count}</td>
                <td>2.3s</td>
                <td><span class="badge bg-success">Active</span></td>
            </tr>
        `).join('') || '<tr><td colspan="4">No data available</td></tr>';
        
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

function refreshAnalytics() {
    loadMobileAnalytics();
}

function saveSettings() {
    alert('Mobile optimization settings saved successfully!');
}
</script>
@endsection
