@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2>FLHSMV Submissions</h2>
    <div id="flhsmv-submissions"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/flhsmv/submissions', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('flhsmv-submissions');
        if (data.data && data.data.length > 0) {
            let html = '<table class="table"><thead><tr><th>ID</th><th>User</th><th>Certificate</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            data.data.forEach(sub => {
                html += `<tr>
                    <td>${sub.id}</td>
                    <td>${sub.user ? sub.user.name : 'N/A'}</td>
                    <td>${sub.florida_certificate_id}</td>
                    <td><span class="badge bg-${sub.status === 'completed' ? 'success' : 'warning'}">${sub.status}</span></td>
                    <td>${new Date(sub.created_at).toLocaleDateString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>No submissions found.</p>';
        }
    })
    .catch(error => {
        document.getElementById('flhsmv-submissions').innerHTML = '<p class="text-danger">Error loading submissions</p>';
    });
});
</script>
@endsection
