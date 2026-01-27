@extends('layouts.app')

@section('title', 'User Consents')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">User Legal Consents</h1>
    
    <div class="card">
        <div class="card-body">
            <div id="consents"></div>
        </div>
    </div>
</div>

<script>
async function loadConsents() {
    const response = await fetch('/web/user-consents');
    const consents = await response.json();
    document.getElementById('consents').innerHTML = consents.length ?
        `<table class="table"><thead><tr><th>User</th><th>Document</th><th>Consent Given</th><th>Date</th></tr></thead><tbody>
        ${consents.map(c => `<tr><td>${c.user?.name}</td><td>${c.document?.title}</td>
        <td><span class="badge bg-${c.consent_given ? 'success' : 'danger'}">${c.consent_given ? 'Yes' : 'No'}</span></td>
        <td>${c.consented_at}</td></tr>`).join('')}</tbody></table>` :
        '<p>No consents recorded</p>';
}
loadConsents();
</script>
@endsection
