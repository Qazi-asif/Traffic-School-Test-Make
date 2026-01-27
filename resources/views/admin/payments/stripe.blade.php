@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fab fa-stripe me-2"></i>Stripe Payments</h2>
        <button class="btn btn-primary" onclick="loadStripePayments()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="stripe-payments"></div>
        </div>
    </div>
</div>

<script>
function loadStripePayments() {
    fetch('/api/payment/stripe/list', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('stripe-payments');
        if (data.length > 0) {
            let html = '<table class="table table-hover"><thead><tr><th>Payment Intent ID</th><th>Amount</th><th>Currency</th><th>Status</th><th>Customer</th><th>Date</th></tr></thead><tbody>';
            data.forEach(payment => {
                html += `<tr>
                    <td><code>${payment.stripe_payment_intent_id}</code></td>
                    <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                    <td>${payment.currency.toUpperCase()}</td>
                    <td><span class="badge bg-${payment.status === 'succeeded' ? 'success' : 'warning'}">${payment.status}</span></td>
                    <td>${payment.stripe_customer_id || 'Guest'}</td>
                    <td>${new Date(payment.created_at).toLocaleString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-center">No Stripe payments found</p>';
        }
    })
    .catch(() => {
        document.getElementById('stripe-payments').innerHTML = '<p class="text-danger">Error loading Stripe payments</p>';
    });
}

document.addEventListener('DOMContentLoaded', loadStripePayments);
</script>
@endsection
