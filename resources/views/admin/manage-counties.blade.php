<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Counties & Courts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
        }

        .card-header {
            background: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
        }

        .btn-outline-primary {
            color: white !important;
            border-color: var(--accent) !important;
        }

        .btn-outline-primary:hover {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: white !important;
        }

        .btn-outline-primary.active {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: white !important;
        }

        .btn-outline-secondary {
            color: var(--text-secondary) !important;
            border-color: var(--border) !important;
        }

        .btn-outline-secondary:hover {
            background: var(--hover) !important;
            border-color: var(--border) !important;
        }

        .btn-outline-secondary.active {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: var(--bg-primary) !important;
        }

        .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        .btn-primary:hover {
            background: var(--hover) !important;
            border-color: var(--hover) !important;
        }

        .btn-warning {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: var(--bg-primary) !important;
        }

        .btn-warning:hover {
            background: var(--hover) !important;
            border-color: var(--hover) !important;
        }

        .btn-danger {
            background: #dc2626 !important;
            border-color: #dc2626 !important;
        }

        .btn-danger:hover {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .border-bottom {
            border-bottom: 1px solid var(--border) !important;
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        .modal-content {
            background: var(--bg-card) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
        }

        .modal-header {
            background: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border) !important;
        }

        .modal-body {
            background: var(--bg-card) !important;
        }

        .modal-footer {
            background: var(--bg-secondary) !important;
            border-top: 1px solid var(--border) !important;
        }

        .form-control {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
        }

        .form-control:focus {
            background: var(--bg-secondary) !important;
            border-color: var(--accent) !important;
            color: var(--text-primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
        }

        .form-label {
            color: var(--text-primary) !important;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2 class="mb-4">Manage Counties & Courts</h2>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>States</h5>
                        <button onclick="showStateModal()" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <div id="states-list"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Counties</h5>
                        <button onclick="showCountyModal()" class="btn btn-sm btn-primary" id="addCountyBtn" style="display:none;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <div id="counties-list"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Courts</h5>
                        <button onclick="showCourtModal()" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Court
                        </button>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <div id="courts-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Court Modal -->
    <div class="modal fade" id="courtModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Court</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="courtForm">
                        <input type="hidden" id="courtId">
                        <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" id="courtState" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">County</label>
                            <input type="text" class="form-control" id="courtCounty" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Court Name</label>
                            <input type="text" class="form-control" id="courtName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button onclick="saveCourt()" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- State Modal -->
    <div class="modal fade" id="stateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add State</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="stateForm">
                        <div class="mb-3">
                            <label class="form-label">State Name</label>
                            <input type="text" class="form-control" id="stateName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button onclick="saveState()" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- County Modal -->
    <div class="modal fade" id="countyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add County</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="countyForm">
                        <div class="mb-3">
                            <label class="form-label">County Name</label>
                            <input type="text" class="form-control" id="countyName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button onclick="saveCounty()" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedState = null;
        let selectedCounty = null;

        async function loadStates() {
            const response = await fetch('/web/admin/courts/states');
            const states = await response.json();
            
            const html = states.map(state => `
                <button class="btn btn-outline-primary w-100 mb-2" onclick="selectState('${state}')">
                    ${state}
                </button>
            `).join('');
            
            document.getElementById('states-list').innerHTML = html;
        }

        async function selectState(state) {
            selectedState = state;
            selectedCounty = null;
            document.querySelectorAll('#states-list button.btn-outline-primary').forEach(b => b.classList.remove('active'));
            event.target.closest('.btn-outline-primary').classList.add('active');
            document.getElementById('addCountyBtn').style.display = 'block';
            
            const response = await fetch(`/web/admin/courts/${state}/counties`);
            const counties = await response.json();
            
            const html = counties.map(county => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <button class="btn btn-outline-secondary flex-grow-1 text-start" onclick="selectCounty('${county}')">
                        ${county}
                    </button>
                    <button onclick="deleteCounty('${county}')" class="btn btn-sm btn-danger ms-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
            
            document.getElementById('counties-list').innerHTML = html;
            document.getElementById('courts-list').innerHTML = '';
        }

        async function selectCounty(county) {
            selectedCounty = county;
            document.querySelectorAll('#counties-list button.btn-outline-secondary').forEach(b => b.classList.remove('active'));
            event.target.closest('.btn-outline-secondary').classList.add('active');
            
            const response = await fetch(`/web/admin/courts/${selectedState}/${county}`);
            const courts = await response.json();
            
            const html = courts.map(court => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <span>${court.court}</span>
                    <div>
                        <button onclick="editCourt(${court.id}, '${court.court}')" class="btn btn-sm btn-warning">Edit</button>
                        <button onclick="deleteCourt(${court.id})" class="btn btn-sm btn-danger">Delete</button>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('courts-list').innerHTML = html || '<p class="text-muted">No courts</p>';
        }

        function showStateModal() {
            document.getElementById('stateName').value = '';
            new bootstrap.Modal(document.getElementById('stateModal')).show();
        }

        function showCountyModal() {
            if (!selectedState) {
                alert('Please select a state first');
                return;
            }
            document.getElementById('countyName').value = '';
            new bootstrap.Modal(document.getElementById('countyModal')).show();
        }

        function showCourtModal() {
            if (!selectedState || !selectedCounty) {
                alert('Please select a state and county first');
                return;
            }
            document.getElementById('courtId').value = '';
            document.getElementById('courtName').value = '';
            document.getElementById('courtState').value = selectedState;
            document.getElementById('courtCounty').value = selectedCounty;
            new bootstrap.Modal(document.getElementById('courtModal')).show();
        }

        async function saveState() {
            const stateName = document.getElementById('stateName').value;
            const response = await fetch('/web/admin/courts/states', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ state: stateName })
            });

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('stateModal')).hide();
                loadStates();
            }
        }

        async function saveCounty() {
            const countyName = document.getElementById('countyName').value;
            const response = await fetch(`/web/admin/courts/${selectedState}/counties`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ state: selectedState, county: countyName })
            });

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('countyModal')).hide();
                selectState(selectedState);
            }
        }

        async function deleteState(state) {
            if (!confirm('Delete this state and all its data?')) return;
            
            await fetch(`/web/admin/courts/states/${state}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            loadStates();
        }

        async function deleteCounty(county) {
            if (!confirm('Delete this county and all its courts?')) return;
            
            await fetch(`/web/admin/courts/${selectedState}/counties/${county}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            selectState(selectedState);
        }

        function editCourt(id, name) {
            document.getElementById('courtId').value = id;
            document.getElementById('courtName').value = name;
            document.getElementById('courtState').value = selectedState;
            document.getElementById('courtCounty').value = selectedCounty;
            new bootstrap.Modal(document.getElementById('courtModal')).show();
        }

        async function saveCourt() {
            const id = document.getElementById('courtId').value;
            const data = {
                state: selectedState,
                county: selectedCounty,
                court: document.getElementById('courtName').value
            };

            const url = id ? `/web/admin/courts/${id}` : '/web/admin/courts';
            const method = id ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('courtModal')).hide();
                selectCounty(selectedCounty);
            }
        }

        async function deleteCourt(id) {
            if (!confirm('Delete this court?')) return;
            
            await fetch(`/web/admin/courts/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            selectCounty(selectedCounty);
        }

        loadStates();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
