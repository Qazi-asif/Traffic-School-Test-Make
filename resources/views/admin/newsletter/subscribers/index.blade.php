<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-envelope"></i> Newsletter Subscribers</h2>
            <div class="btn-group">
                <a href="{{ route('admin.newsletter.subscribers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Subscriber
                </a>
                <a href="{{ route('admin.newsletter.subscribers.import') }}" class="btn btn-success">
                    <i class="fas fa-file-import"></i> Import CSV
                </a>
                <a href="{{ route('admin.newsletter.subscribers.export', request()->query()) }}" class="btn btn-info">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Subscribers</h6>
                        <h2 class="mb-0">{{ $stats['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Active</h6>
                        <h2 class="mb-0 text-success">{{ $stats['active'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Confirmed</h6>
                        <h2 class="mb-0 text-primary">{{ $stats['confirmed'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">This Month</h6>
                        <h2 class="mb-0 text-info">{{ $stats['this_month'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search email or name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="state" class="form-select">
                            <option value="">All States</option>
                            <option value="FL" {{ request('state') === 'FL' ? 'selected' : '' }}>Florida</option>
                            <option value="CA" {{ request('state') === 'CA' ? 'selected' : '' }}>California</option>
                            <option value="TX" {{ request('state') === 'TX' ? 'selected' : '' }}>Texas</option>
                            <option value="MO" {{ request('state') === 'MO' ? 'selected' : '' }}>Missouri</option>
                            <option value="DE" {{ request('state') === 'DE' ? 'selected' : '' }}>Delaware</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="source" class="form-select">
                            <option value="">All Sources</option>
                            <option value="registration" {{ request('source') === 'registration' ? 'selected' : '' }}>Registration</option>
                            <option value="checkout" {{ request('source') === 'checkout' ? 'selected' : '' }}>Checkout</option>
                            <option value="website_form" {{ request('source') === 'website_form' ? 'selected' : '' }}>Website Form</option>
                            <option value="import" {{ request('source') === 'import' ? 'selected' : '' }}>Import</option>
                            <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.newsletter.subscribers.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="bulkForm" method="POST" action="{{ route('admin.newsletter.subscribers.bulk-action') }}">
                    @csrf
                    <div class="d-flex justify-content-between mb-3">
                        <div class="d-flex gap-2">
                            <select name="action" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Bulk Actions</option>
                                <option value="activate">Activate</option>
                                <option value="deactivate">Deactivate</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Apply bulk action?')">Apply</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>State</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Subscribed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                    <tr>
                                        <td><input type="checkbox" name="subscribers[]" value="{{ $subscriber->id }}" class="subscriber-checkbox"></td>
                                        <td>{{ $subscriber->email }}</td>
                                        <td>{{ $subscriber->full_name }}</td>
                                        <td>{{ $subscriber->state_code ?? 'N/A' }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($subscriber->source) }}</span></td>
                                        <td>
                                            @if($subscriber->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $subscriber->subscribed_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.newsletter.subscribers.edit', $subscriber) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.newsletter.subscribers.destroy', $subscriber) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subscriber?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No subscribers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="mt-3">
                    {{ $subscribers->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.subscriber-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    </script>
</body>
</html>
