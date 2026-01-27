@extends('layouts.app')

@section('title', 'DICDS Submissions')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Florida DICDS Submissions</h1>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Submission ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Certificate Number</th>
                            <th>Submitted Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="submissionsBody">
                        <tr>
                            <td colspan="7" class="text-center">Loading submissions...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadSubmissions() {
    try {
        const response = await fetch('/api/dicds-submissions');
        const submissions = await response.json();
        displaySubmissions(submissions);
    } catch (error) {
        console.error('Error loading submissions:', error);
        document.getElementById('submissionsBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load submissions</td></tr>';
    }
}

function displaySubmissions(submissions) {
    const tbody = document.getElementById('submissionsBody');
    
    if (submissions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No submissions found</td></tr>';
        return;
    }
    
    tbody.innerHTML = submissions.map(sub => `
        <tr>
            <td>${sub.id}</td>
            <td>${sub.student_name || 'N/A'}</td>
            <td>${sub.course_name || 'N/A'}</td>
            <td>${sub.certificate_number || 'N/A'}</td>
            <td>${sub.submitted_at ? new Date(sub.submitted_at).toLocaleDateString() : 'N/A'}</td>
            <td><span class="badge bg-${sub.status === 'success' ? 'success' : sub.status === 'pending' ? 'warning' : 'danger'}">${sub.status || 'pending'}</span></td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewDetails(${sub.id})">View</button>
            </td>
        </tr>
    `).join('');
}

async function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();
    
    try {
        const response = await fetch(`/web/admin/certificates/${id}`);
        const cert = await response.json();
        
        document.getElementById('detailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Certificate Number:</strong><br>
                    ${cert.dicds_certificate_number || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Student Name:</strong><br>
                    ${cert.student_name}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Course Name:</strong><br>
                    ${cert.course_name}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>State:</strong><br>
                    ${cert.state || 'FL'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Completion Date:</strong><br>
                    ${new Date(cert.completion_date).toLocaleDateString()}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Final Exam Score:</strong><br>
                    ${cert.final_exam_score || 'N/A'}%
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Driver License:</strong><br>
                    ${cert.driver_license_number || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Citation Number:</strong><br>
                    ${cert.citation_number || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Citation County:</strong><br>
                    ${cert.citation_county || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Court Name:</strong><br>
                    ${cert.court_name || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Traffic School Due Date:</strong><br>
                    ${cert.traffic_school_due_date ? new Date(cert.traffic_school_due_date).toLocaleDateString() : 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Submitted At:</strong><br>
                    ${cert.sent_at ? new Date(cert.sent_at).toLocaleString() : 'N/A'}
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Student Address:</strong><br>
                    ${cert.student_address || 'N/A'}
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Verification Hash:</strong><br>
                    <code>${cert.verification_hash}</code>
                </div>
            </div>
        `;
    } catch (error) {
        document.getElementById('detailsContent').innerHTML = '<div class="alert alert-danger">Failed to load details</div>';
    }
}

loadSubmissions();
</script>
@endsection
