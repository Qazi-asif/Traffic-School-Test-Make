<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Subscribers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-import"></i> Import Subscribers</h2>
            <a href="{{ route('admin.newsletter.subscribers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.newsletter.subscribers.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">CSV File *</label>
                                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required accept=".csv,.txt">
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum file size: 10MB</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Source *</label>
                                <select name="source" class="form-select" required>
                                    <option value="import">Import</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>

                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle"></i> CSV Format:</strong>
                                <p class="mb-0">Your CSV file should have the following columns:</p>
                                <code>email, first_name, last_name, state_code</code>
                                <p class="mt-2 mb-0">Example:</p>
                                <code>john@example.com, John, Doe, FL</code>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Subscribers
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Import Instructions</h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Prepare your CSV file with subscriber data</li>
                            <li>Ensure email column is present</li>
                            <li>Optional columns: first_name, last_name, state_code</li>
                            <li>Duplicate emails will be updated</li>
                            <li>Invalid emails will be skipped</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
