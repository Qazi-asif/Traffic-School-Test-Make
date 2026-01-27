@extends('layouts.app')

@section('title', 'Copyright Protection')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Copyright Protection Logs</h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5>Protection Statistics</h5>
                    <div id="stats"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadStats() {
    const response = await fetch('/web/copyright-protection/stats');
    const stats = await response.json();
    document.getElementById('stats').innerHTML = stats.length ?
        `<table class="table"><thead><tr><th>Action</th><th>Count</th></tr></thead><tbody>
        ${stats.map(s => `<tr><td>${s.action}</td><td>${s.count}</td></tr>`).join('')}</tbody></table>` :
        '<p>No protection events logged</p>';
}
loadStats();
</script>
@endsection
