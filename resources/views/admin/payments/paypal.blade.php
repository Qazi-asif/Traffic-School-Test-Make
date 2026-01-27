@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fab fa-paypal me-2"></i>PayPal Payments</h2>
        <button class="btn btn-primary" onclick="loadPayPalPayments()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="paypal-payments"></div>
        </div>
    </div>
</div>

<script>
function loadPayPalPayments() {
    fetch('/api/payment/paypal/list', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('paypal-payments');
        if (data.length > 0) {
            let html = '<table class="table table-hover"><thead><tr><th>Order ID</th><th>Payer ID</th><th>Amount</th><th>Currency</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            data.forEach(payment => {
                html += `<tr>
                    <td><code>${payment.paypal_order_id}</code></td>
                    <td>${payment.paypal_payer_id || 'N/A'}</td>
                    <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                    <td>${payment.currency}</td>
                    <td><span class="badge bg-${payment.status === 'completed' ? 'success' : 'warning'}">${payment.status}</span></td>
                    <td>${new Date(payment.created_at).toLocaleString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-center">No PayPal payments found</p>';
        }
    })
    .catch(() => {
        document.getElementById('paypal-payments').innerHTML = '<p class="text-danger">Error loading PayPal payments</p>';
    });
}

document.addEventListener('DOMContentLoaded', loadPayPalPayments);
</script>
@endsection
