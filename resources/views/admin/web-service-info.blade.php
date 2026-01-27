@extends('layouts.app')

@section('title', 'Web Service Info')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">DICDS Web Service Info</h1>
    
    <div class="card">
        <div class="card-body">
            <h5>Schools & Course Assignments</h5>
            <div id="schoolInfo"></div>
        </div>
    </div>
</div>

<script>
async function loadInfo() {
    const response = await fetch('/web/dicds-web-service-info');
    const data = await response.json();
    document.getElementById('schoolInfo').innerHTML = data.length ?
        `<table class="table"><thead><tr><th>School</th><th>Courses</th><th>Last Updated</th></tr></thead><tbody>
        ${data.map(d => `<tr><td>${d.school?.school_name}</td><td>${d.course_assignments?.length || 0}</td><td>${d.last_updated}</td></tr>`).join('')}</tbody></table>` :
        '<p>No data available</p>';
}
loadInfo();
</script>
@endsection
