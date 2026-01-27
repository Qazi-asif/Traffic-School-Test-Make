@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div style="padding: 40px 30px; background: var(--bg-primary); min-height: 100vh;">
    <!-- Header Section -->
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 28px; font-weight: 700; color: var(--text-primary); margin: 0 0 8px 0;">Reports</h1>
        <p style="font-size: 14px; color: var(--text-secondary); margin: 0;">View and generate comprehensive business reports</p>
    </div>

    <!-- Generate Report Section -->
    <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin: 0 0 20px 0;">Generate Report</h3>
        
        <form id="report-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-primary); margin-bottom: 6px;">Report Type</label>
                <select name="report_type" class="form-select" style="border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px; background: var(--bg-primary); color: var(--text-primary); width: 100%;" required>
                    <option value="">Select type</option>
                    <option value="enrollment">Enrollment</option>
                    <option value="revenue">Revenue</option>
                    <option value="completion">Completion</option>
                    <option value="compliance">Compliance</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-primary); margin-bottom: 6px;">Start Date</label>
                <input type="date" name="start_date" class="form-control" style="border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px; background: var(--bg-primary); color: var(--text-primary); width: 100%;" required>
            </div>
            
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-primary); margin-bottom: 6px;">End Date</label>
                <input type="date" name="end_date" class="form-control" style="border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px; background: var(--bg-primary); color: var(--text-primary); width: 100%;" required>
            </div>
            
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-primary); margin-bottom: 6px;">State</label>
                <select name="state_code" class="form-select" style="border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px; background: var(--bg-primary); color: var(--text-primary); width: 100%;">
                    <option value="">All States</option>
                    <option value="FL">Florida</option>
                    <option value="CA">California</option>
                    <option value="TX">Texas</option>
                    <option value="MO">Missouri</option>
                    <option value="NV">Nevada</option>
                    <option value="DE">Delaware</option>
                </select>
            </div>
            
            <button type="submit" style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 10px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; width: 100%;">
                Generate
            </button>
        </form>
    </div>

    <!-- Results Section -->
    <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; margin-bottom: 24px;">
        <div style="padding: 24px; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin: 0;">Results</h3>
        </div>
        <div style="padding: 24px; overflow-x: auto;">
            <div id="report-results">
                <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                    <p style="font-size: 14px; margin: 0;">Select parameters and generate a report</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div id="summary-stats" style="display: none; display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 20px;">
            <div style="font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;" id="stat-total">0</div>
            <div style="font-size: 12px; color: var(--text-secondary);">Total Records</div>
        </div>
        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 20px;">
            <div style="font-size: 24px; font-weight: 700; color: #10B981; margin-bottom: 4px;" id="stat-success">0</div>
            <div style="font-size: 12px; color: var(--text-secondary);">Completed</div>
        </div>
        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 20px;">
            <div style="font-size: 24px; font-weight: 700; color: #F59E0B; margin-bottom: 4px;" id="stat-pending">0</div>
            <div style="font-size: 12px; color: var(--text-secondary);">In Progress</div>
        </div>
        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 20px;">
            <div style="font-size: 24px; font-weight: 700; color: var(--accent); margin-bottom: 4px;" id="stat-revenue">$0</div>
            <div style="font-size: 12px; color: var(--text-secondary);">Total Revenue</div>
        </div>
    </div>

    <!-- Saved Reports Section -->
    <div style="margin-top: 40px;">
        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid var(--border);">
                <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin: 0;">Saved Reports</h3>
            </div>
            <div style="padding: 24px;">
                <div id="saved-reports">
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                        <p style="font-size: 14px; margin: 0;">Loading reports...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-select:focus, .form-control:focus {
        border-color: var(--accent) !important;
        outline: none;
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        min-width: 1000px;
    }
    
    .report-table thead {
        background: var(--bg-primary);
        border-bottom: 1px solid var(--border);
    }
    
    .report-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 12px;
    }
    
    .report-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
    }
    
    .report-table tbody tr:hover {
        background: var(--bg-primary);
    }
    
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
</style>

<script>
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
    }
    
    document.getElementById('report-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        
        try {
            document.getElementById('report-results').innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-secondary);"><p style="font-size: 14px; margin: 0;">Generating report...</p></div>';
            
            const response = await fetch(`/web/admin/reports/generate?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                displayReportResults(data, formData.get('report_type'));
            } else {
                document.getElementById('report-results').innerHTML = '<div style="padding: 16px; background: #FEE2E2; border-radius: 6px; color: #DC2626; font-size: 13px;">Error generating report</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('report-results').innerHTML = '<div style="padding: 16px; background: #FEE2E2; border-radius: 6px; color: #DC2626; font-size: 13px;">Error generating report</div>';
        }
    });
    
    function displayReportResults(response, reportType) {
        const container = document.getElementById('report-results');
        const data = Array.isArray(response) ? response : (response.detailed_data || response.data || response.enrollments || []);
        
        if (!data || data.length === 0) {
            container.innerHTML = '<div style="padding: 16px; background: #DBEAFE; border-radius: 6px; color: #1E40AF; font-size: 13px;">No data found</div>';
            return;
        }
        
        let html = `<div style="margin-bottom: 16px;"><h4 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0 0 4px 0;">${reportType.charAt(0).toUpperCase() + reportType.slice(1)} Report</h4><p style="font-size: 12px; color: var(--text-secondary); margin: 0;">${data.length} records</p></div>`;
        
        if (reportType === 'enrollment') {
            updateStats(data, 'enrollment');
            html += generateEnrollmentTable(data);
        } else if (reportType === 'revenue') {
            updateStats(data, 'revenue');
            html += generateRevenueTable(data);
        } else if (reportType === 'completion') {
            updateStats(data, 'completion');
            html += generateCompletionTable(data);
        } else {
            html += generateGenericTable(data);
        }
        
        container.innerHTML = html;
    }
    
    function generateEnrollmentTable(data) {
        return `
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(item => `
                            <tr>
                                <td>${item.student_name || (item.user?.first_name + ' ' + item.user?.last_name) || 'N/A'}</td>
                                <td>${item.student_email || item.user?.email || 'N/A'}</td>
                                <td>${item.course_title || item.course?.title || 'N/A'}</td>
                                <td>${formatDate(item.enrolled_date || item.enrolled_at)}</td>
                                <td><span class="badge" style="background: ${getStatusBg(item.status)}; color: white;">${item.status || 'N/A'}</span></td>
                                <td><div style="width: 60px; height: 4px; background: var(--border); border-radius: 2px; overflow: hidden;"><div style="width: ${item.progress || 0}%; height: 100%; background: var(--accent);"></div></div></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function generateRevenueTable(data) {
        const totalRevenue = data.reduce((sum, item) => sum + parseFloat(item.amount_paid || item.amount || 0), 0);
        return `
            <div style="background: var(--bg-primary); border: 1px solid var(--border); border-radius: 6px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 20px; font-weight: 700; color: var(--accent);">$${totalRevenue.toFixed(2)}</div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Total Revenue</div>
            </div>
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(item => `
                            <tr>
                                <td>${item.student_name || (item.user?.first_name + ' ' + item.user?.last_name) || 'N/A'}</td>
                                <td>${item.course_title || item.course?.title || 'N/A'}</td>
                                <td style="font-weight: 600; color: var(--accent);">$${(item.amount_paid || item.amount || 0).toFixed(2)}</td>
                                <td>${formatDate(item.enrolled_at)}</td>
                                <td><span class="badge" style="background: #10B981; color: white;">Completed</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function generateCompletionTable(data) {
        return `
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Completion Date</th>
                            <th>Score</th>
                            <th>Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(item => `
                            <tr>
                                <td>${item.student_name || (item.user?.first_name + ' ' + item.user?.last_name) || 'N/A'}</td>
                                <td>${item.course_title || item.course?.title || 'N/A'}</td>
                                <td>${formatDate(item.completed_at || item.enrolled_at)}</td>
                                <td>${item.score || item.progress || 0}%</td>
                                <td><span class="badge" style="background: #10B981; color: white;">Issued</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function generateGenericTable(data) {
        const keys = Object.keys(data[0] || {}).slice(0, 6);
        return `
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            ${keys.map(key => `<th>${formatKey(key)}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(item => `
                            <tr>
                                ${keys.map(key => `<td>${formatValue(item[key])}</td>`).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function updateStats(data, type) {
        const statsDiv = document.getElementById('summary-stats');
        statsDiv.style.display = 'grid';
        
        document.getElementById('stat-total').textContent = data.length;
        
        if (type === 'enrollment') {
            const completed = data.filter(d => d.status === 'completed').length;
            const pending = data.filter(d => d.status !== 'completed').length;
            document.getElementById('stat-success').textContent = completed;
            document.getElementById('stat-pending').textContent = pending;
        } else if (type === 'revenue') {
            const total = data.reduce((sum, item) => sum + parseFloat(item.amount_paid || item.amount || 0), 0);
            document.getElementById('stat-revenue').textContent = '$' + total.toFixed(2);
        }
    }
    
    function formatDate(date) {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
    
    function formatKey(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    function formatValue(value) {
        if (value === null || value === undefined) return 'N/A';
        if (typeof value === 'object') return JSON.stringify(value).substring(0, 50);
        return String(value).substring(0, 100);
    }
    
    function getStatusBg(status) {
        const colors = {
            'completed': '#10B981',
            'in_progress': '#F59E0B',
            'pending': '#3B82F6',
            'failed': '#EF4444'
        };
        return colors[status] || '#6B7280';
    }
    
    async function loadSavedReports() {
        try {
            const response = await fetch('/web/admin/reports', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                const reports = Array.isArray(data) ? data : (data.data || []);
                const container = document.getElementById('saved-reports');
                
                if (reports.length === 0) {
                    container.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-secondary);"><p style="font-size: 14px; margin: 0;">No saved reports</p></div>';
                    return;
                }
                
                container.innerHTML = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                        ${reports.map(report => `
                            <div style="background: var(--bg-primary); border: 1px solid var(--border); border-radius: 6px; padding: 16px;">
                                <h4 style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin: 0 0 8px 0;">${report.name || 'Unnamed'}</h4>
                                <p style="font-size: 12px; color: var(--text-secondary); margin: 0 0 4px 0;">Type: ${report.type || 'N/A'}</p>
                                <p style="font-size: 12px; color: var(--text-secondary); margin: 0;">Created: ${formatDate(report.created_at)}</p>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                document.getElementById('saved-reports').innerHTML = '<div style="padding: 16px; background: #FEE2E2; border-radius: 6px; color: #DC2626; font-size: 13px;">Error loading reports</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('saved-reports').innerHTML = '<div style="padding: 16px; background: #FEE2E2; border-radius: 6px; color: #DC2626; font-size: 13px;">Error loading reports</div>';
        }
    }
    
    loadSavedReports();
</script>
@endsection
