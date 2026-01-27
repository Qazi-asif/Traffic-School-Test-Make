@extends('layouts.app')

@section('title', 'Certificate Lookup')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Certificate Lookup & Reprint</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Search Type</label>
                        <select class="form-select" id="searchType">
                            <option value="certificate_number">Certificate Number</option>
                            <option value="student_name">Student Name</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Search Term</label>
                        <input type="text" class="form-control" id="searchTerm" placeholder="Enter search term...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="results"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const searchType = document.getElementById('searchType').value;
    const searchTerm = document.getElementById('searchTerm').value;
    
    const response = await fetch('/web/certificate-lookup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ search_type: searchType, search_term: searchTerm })
    });
    
    const results = await response.json();
    document.getElementById('results').innerHTML = results.length ? 
        `<table class="table"><thead><tr><th>Certificate #</th><th>Student</th><th>Course</th><th>Issue Date</th><th>Action</th></tr></thead><tbody>
        ${results.map(r => `<tr><td>${r.certificate_number}</td><td>${r.student_name}</td><td>${r.course_type}</td><td>${r.issue_date}</td>
        <td><button class="btn btn-sm btn-info" onclick="reprint(${r.id})">Reprint</button></td></tr>`).join('')}</tbody></table>` :
        '<p>No results found</p>';
});

async function reprint(id) {
    await fetch(`/web/certificate-lookup/${id}/reprint`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }});
    alert('Certificate reprinted');
}
</script>
@endsection
