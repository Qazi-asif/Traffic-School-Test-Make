@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Certificate Management</h3>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="state-filter" class="form-select" onchange="loadCertificates()">
                                <option value="">All States</option>
                                <option value="FL">Florida</option>
                                <option value="CA">California</option>
                                <option value="TX">Texas</option>
                                <option value="MO">Missouri</option>
                                <option value="DE">Delaware</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="search-input" class="form-control" placeholder="Search certificates..." onkeyup="searchCertificates()">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="loadCertificates()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Certificates Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Certificate #</th>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>State</th>
                                    <th>Generated Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="certificates-table">
                                <tr>
                                    <td colspan="7" class="text-center">Loading certificates...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentState = '';
let currentSearch = '';

function loadCertificates(page = 1) {
    currentPage = page;
    currentState = document.getElementById('state-filter').value;
    
    const params = new URLSearchParams({
        page: currentPage,
        state: currentState,
        search: currentSearch
    });
    
    fetch(`/api/certificates?${params}`)
        .then(response => response.json())
        .then(data => {
            displayCertificates(data.data || data);
            displayPagination(data);
        })
        .catch(error => {
            console.error('Error loading certificates:', error);
            document.getElementById('certificates-table').innerHTML = 
                '<tr><td colspan="7" class="text-center text-danger">Error loading certificates</td></tr>';
        });
}

function displayCertificates(certificates) {
    const tbody = document.getElementById('certificates-table');
    
    if (!certificates || certificates.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No certificates found</td></tr>';
        return;
    }
    
    tbody.innerHTML = certificates.map(cert => `
        <tr>
            <td>${cert.certificate_number || 'N/A'}</td>
            <td>${cert.first_name} ${cert.last_name}</td>
            <td>${cert.email}</td>
            <td>${cert.course_title || 'N/A'}</td>
            <td>${cert.state_code || 'N/A'}</td>
            <td>${cert.certificate_generated_at ? new Date(cert.certificate_generated_at).toLocaleDateString() : 'N/A'}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="downloadCertificate(${cert.enrollment_id})">
                    <i class="fas fa-download"></i> Download
                </button>
                <button class="btn btn-sm btn-info" onclick="viewCertificate(${cert.enrollment_id})">
                    <i class="fas fa-eye"></i> View
                </button>
            </td>
        </tr>
    `).join('');
}

function displayPagination(data) {
    const container = document.getElementById('pagination-container');
    
    if (!data.last_page || data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let pagination = '<nav><ul class="pagination">';
    
    for (let i = 1; i <= data.last_page; i++) {
        const active = i === data.current_page ? 'active' : '';
        pagination += `<li class="page-item ${active}">
            <a class="page-link" href="#" onclick="loadCertificates(${i})">${i}</a>
        </li>`;
    }
    
    pagination += '</ul></nav>';
    container.innerHTML = pagination;
}

function searchCertificates() {
    currentSearch = document.getElementById('search-input').value;
    loadCertificates(1);
}

function downloadCertificate(enrollmentId) {
    window.open(`/admin/certificates/${enrollmentId}/download`, '_blank');
}

function viewCertificate(enrollmentId) {
    fetch(`/admin/certificates/${enrollmentId}`)
        .then(response => response.json())
        .then(data => {
            alert(`Certificate Details:
Certificate Number: ${data.certificate_number}
Student: ${data.first_name} ${data.last_name}
Course: ${data.course_title}
Generated: ${new Date(data.certificate_generated_at).toLocaleString()}`);
        })
        .catch(error => {
            console.error('Error viewing certificate:', error);
            alert('Error loading certificate details');
        });
}

// Load certificates on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCertificates();
});
</script>
@endsection