@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>User Access Management</h2>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5>Locked User Accounts</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Lock Reason</th>
                        <th>Locked At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lockedUsers as $user)
                        <tr>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->lock_reason ?? 'N/A' }}</td>
                            <td>{{ $user->locked_at ? \Carbon\Carbon::parse($user->locked_at)->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>
                                <form action="{{ route('user-access.unlock', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Unlock this account?')">
                                        <i class="fas fa-unlock"></i> Unlock
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No locked accounts</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($lockedUsers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $lockedUsers->links() }}
        </div>
    @endif
</div>
@endsection
