@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-credit-card me-2"></i>Payment Transactions</h2>
        <button class="btn btn-primary" onclick="refreshTransactions()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Transactions</h6>
                    <h3 id="total-count">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Amount</h6>
                    <h3 id="total-amount">$0.00</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Successful</h6>
                    <h3 id="success-count" class="text-success">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Failed</h6>
                    <h3 id="failed-count" class="text-danger">0</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="gateway-filter">
                        <option value="">All Gateways</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="status-filter">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="transactions-table"></div>
        </div>
    </div>
</div>

<script>
function loadTransactions() {
    const gateway = document.getElementById('gateway-filter').value;
    const status = document.getElementById('status-filter').value;
    
    fetch(`/api/payment/transactions?gateway=${gateway}&status=${status}`, {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('transactions-table');
        if (data.length > 0) {
            let total = 0, success = 0, failed = 0;
            let html = '<table class="table table-hover"><thead><tr><th>ID</th><th>User</th><th>Gateway</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            data.forEach(txn => {
                total += parseFloat(txn.amount);
                if (txn.status === 'completed') success++;
                if (txn.status === 'failed') failed++;
                html += `<tr>
                    <td>${txn.transaction_id}</td>
                    <td>${txn.user?.name || 'N/A'}</td>
                    <td><span class="badge bg-info">${txn.gateway}</span></td>
                    <td>$${parseFloat(txn.amount).toFixed(2)}</td>
                    <td><span class="badge bg-${txn.status === 'completed' ? 'success' : txn.status === 'pending' ? 'warning' : 'danger'}">${txn.status}</span></td>
                    <td>${new Date(txn.created_at).toLocaleString()}</td>
                    <td><button class="btn btn-sm btn-primary" onclick="viewTransaction(${txn.id})">View</button></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
            document.getElementById('total-count').textContent = data.length;
            document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
            document.getElementById('success-count').textContent = success;
            document.getElementById('failed-count').textContent = failed;
        } else {
            container.innerHTML = '<p class="text-center">No transactions found</p>';
        }
    })
    .catch(() => {
        document.getElementById('transactions-table').innerHTML = '<p class="text-danger">Error loading transactions</p>';
    });
}

function refreshTransactions() {
    loadTransactions();
}

function viewTransaction(id) {
    alert('View transaction details: ' + id);
}

document.addEventListener('DOMContentLoaded', loadTransactions);
document.getElementById('gateway-filter').addEventListener('change', loadTransactions);
document.getElementById('status-filter').addEventListener('change', loadTransactions);
</script>
@endsection
