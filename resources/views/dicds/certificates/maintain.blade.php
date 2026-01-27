@extends('layouts.dicds')
@section('title', 'Maintain Certificates')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Maintain Certificates</h1>

    <div style="background: white; padding: 20px; border: 2px solid #003366; margin: 20px 0;">
        <h3>All Certificates</h3>
        <div id="certificates-list">
            <p>Loading certificates...</p>
        </div>
    </div>

    <h3>Search Certificates</h3>
    <form method="GET" action="{{ route('dicds.certificates.maintain') }}" id="searchForm">
        <div class="form-group">
            <label>Order Number</label>
            <input type="text" name="order_number" class="form-control">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">-- All --</option>
                <option value="Pending">Pending</option>
                <option value="Active">Active</option>
                <option value="Issued">Issued</option>
            </select>
        </div>

        <div class="form-group">
            <label>Date From</label>
            <input type="date" name="date_from" class="form-control">
        </div>

        <div class="form-group">
            <label>Date To</label>
            <input type="date" name="date_to" class="form-control">
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Search</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Return to Provider Menu</a>
        </div>
    </form>
</div>

<script>
async function loadCertificates() {
    try {
        const response = await fetch('/api/florida-certificates');
        const certificates = await response.json();
        displayCertificates(certificates);
    } catch (error) {
        console.error('Error loading certificates:', error);
        document.getElementById('certificates-list').innerHTML = '<p style="color: red;">Failed to load certificates</p>';
    }
}

function displayCertificates(certificates) {
    const container = document.getElementById('certificates-list');
    
    if (certificates.length === 0) {
        container.innerHTML = '<p>No certificates found.</p>';
        return;
    }
    
    container.innerHTML = `
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #003366; color: white;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Certificate #</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Student</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Course</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                ${certificates.map(cert => `
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">${cert.dicds_certificate_number || 'N/A'}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">${cert.student_name}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">${cert.course_name}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">${new Date(cert.completion_date).toLocaleDateString()}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">${cert.is_sent_to_student ? 'Issued' : 'Pending'}</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <a href="/api/florida-certificates/${cert.id}/view" target="_blank" class="btn" style="padding: 5px 10px; font-size: 12px;">View</a>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

loadCertificates();
</script>
@endsection
