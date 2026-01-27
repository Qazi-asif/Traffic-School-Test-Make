@extends('layouts.app')
@section('title', 'Florida Payments')
@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
    <h1 class="mb-4">Florida Payment Management</h1>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <h2 id="total-revenue">$0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Florida Fees Collected</h5>
                    <h2 id="florida-fees">$0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div-body">
                    <h5>Pending Remittance</h5>
                    <h2 id="pending-remittance">$0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Payments</h5>
                    <h2 id="total-payments">0</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Recent Payments</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Base Price</th>
                        <th>FL Fee</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="payments-list"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function loadPayments() {
    const response = await fetch('/api/florida-payments');
    const data = await response.json();
    
    document.getElementById('total-revenue').textContent = '$' + (data.total_revenue || 0);
    document.getElementById('florida-fees').textContent = '$' + (data.florida_fees || 0);
    document.getElementById('pending-remittance').textContent = '$' + (data.pending_remittance || 0);
    document.getElementById('total-payments').textContent = data.payments?.length || 0;
    
    document.getElementById('payments-list').innerHTML = (data.payments || []).map(p => `
        <tr>
            <td>${p.id}</td>
            <td>${p.billing_name}</td>
            <td>${p.course_type}</td>
            <td>$${p.base_course_price}</td>
            <td>$${p.florida_assessment_fee}</td>
            <td>$${p.total_amount}</td>
            <td><span class="badge bg-${p.payment_status === 'completed' ? 'success' : 'warning'}">${p.payment_status}</span></td>
            <td>${new Date(p.created_at).toLocaleDateString()}</td>
        </tr>
    `).join('');
}

loadPayments();
</script>
@endsection
