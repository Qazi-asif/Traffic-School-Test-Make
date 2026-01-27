@extends('layouts.app')

@section('title', 'Florida DICDS - Main Menu')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="dicds-main-menu">
                <div class="menu-header text-center mb-4">
                    <h2>Florida DICDS - Main Menu</h2>
                    <p>Select an option from the menu below:</p>
                </div>

                <div class="menu-sections">
                    <div class="row">
                        <!-- Schools Menu -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white text-center">
                                    <h3>Schools</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button onclick="navigateToItem('new_school')" class="btn btn-outline-primary">
                                            <div class="fw-bold">New School</div>
                                            <small>Add new contracted school</small>
                                        </button>
                                        <button onclick="navigateToItem('maintain_school')" class="btn btn-outline-primary">
                                            <div class="fw-bold">Maintain School</div>
                                            <small>Edit existing schools</small>
                                        </button>
                                        <button onclick="navigateToItem('add_instructor')" class="btn btn-outline-primary">
                                            <div class="fw-bold">Add Instructor</div>
                                            <small>Add approved instructors</small>
                                        </button>
                                        <button onclick="navigateToItem('update_instructor')" class="btn btn-outline-primary">
                                            <div class="fw-bold">Update Instructor</div>
                                            <small>Edit existing instructors</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certificates Menu -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white text-center">
                                    <h3>Certificates</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button onclick="navigateToItem('order_certificates')" class="btn btn-outline-success">
                                            <div class="fw-bold">Order Certificates</div>
                                            <small>Order from Florida DHSMV</small>
                                        </button>
                                        <button onclick="navigateToItem('distribute_certificates')" class="btn btn-outline-success">
                                            <div class="fw-bold">Distribute Certificates</div>
                                            <small>Distribute to schools</small>
                                        </button>
                                        <button onclick="navigateToItem('reclaim_certificates')" class="btn btn-outline-success">
                                            <div class="fw-bold">Reclaim Certificates</div>
                                            <small>Reclaim from schools</small>
                                        </button>
                                        <button onclick="navigateToItem('maintain_certificates')" class="btn btn-outline-success">
                                            <div class="fw-bold">Maintain Certificates</div>
                                            <small>View order status</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inquiry Menu -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white text-center">
                                    <h3>Inquiry Menu</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button onclick="navigateToItem('web_service_info')" class="btn btn-outline-info">
                                            <div class="fw-bold">Web Service Info</div>
                                            <small>School and instructor reference</small>
                                        </button>
                                        <button onclick="navigateToItem('school_certificates')" class="btn btn-outline-info">
                                            <div class="fw-bold">School's Certificates</div>
                                            <small>Certificate counts by school</small>
                                        </button>
                                        <button onclick="navigateToItem('reports')" class="btn btn-outline-info">
                                            <div class="fw-bold">Reports</div>
                                            <small>Certificate lookup, school activity reports</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="menu-footer text-center mt-4 pt-3" style="border-top: 1px solid #dee2e6;">
                    <button onclick="logout()" class="btn btn-outline-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const navigationMap = {
    'new_school': '/dicds/schools/add',
    'maintain_school': '/dicds/schools/maintain',
    'add_instructor': '/dicds/instructors/add',
    'update_instructor': '/dicds/instructors/manage',
    'order_certificates': '/dicds/certificates/order',
    'distribute_certificates': '/dicds/certificates/distribute',
    'reclaim_certificates': '/dicds/certificates/reclaim',
    'maintain_certificates': '/dicds/certificates/maintain',
    'web_service_info': '/dicds/reports/web-service-info',
    'school_certificates': '/dicds/reports/schools-certificates',
    'reports': '/dicds/reports/menu'
};

function navigateToItem(itemId) {
    const url = navigationMap[itemId];
    if (url) {
        window.location.href = url;
    } else {
        alert(`Navigation for ${itemId} not configured`);
    }
}

function logout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
}
</script>

<style>
.dicds-main-menu {
    max-width: 1200px;
    margin: 0 auto;
}

.btn {
    min-height: 44px;
    text-align: left;
}

.btn small {
    display: block;
    margin-top: 0.25rem;
}
</style>
@endsection
