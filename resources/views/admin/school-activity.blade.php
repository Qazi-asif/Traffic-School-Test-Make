@extends('layouts.app')

@section('title', 'School Activity Reports')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">School Activity Reports</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <h5>Generate New Report</h5>
            <form id="reportForm">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">School</label>
                        <select class="form-select" id="schoolId" required></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Course Type</label>
                        <select class="form-select" id="courseType" required>
                            <option value="BDI">BDI</option>
                            <option value="ADI">ADI</option>
                            <option value="TLSAE">TLSAE</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Generated Reports</h5>
            <div id="reports"></div>
        </div>
    </div>
</div>

<script>
async function loadSchools() {
    const response = await fetch('/web/florida-schools');
    const schools = await response.json();
    document.getElementById('schoolId').innerHTML = schools.map(s => `<option value="${s.id}">${s.school_name}</option>`).join('');
}

document.getElementById('reportForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const response = await fetch('/web/school-activity-reports/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
            date_range_start: document.getElementById('startDate').value,
            date_range_end: document.getElementById('endDate').value,
            school_id: document.getElementById('schoolId').value,
            course_type: document.getElementById('courseType').value
        })
    });
    if (response.ok) {
        alert('Report generated');
        loadReports();
    }
});

async function loadReports() {
    const response = await fetch('/web/school-activity-reports');
    const reports = await response.json();
    document.getElementById('reports').innerHTML = reports.length ?
        `<table class="table"><thead><tr><th>Date</th><th>School</th><th>Course</th><th>Certificates</th></tr></thead><tbody>
        ${reports.map(r => `<tr><td>${r.report_date}</td><td>${r.school?.school_name}</td><td>${r.course_type}</td><td>${r.certificates_issued}</td></tr>`).join('')}</tbody></table>` :
        '<p>No reports generated yet</p>';
}

loadSchools();
loadReports();
</script>
@endsection
