@extends('layouts.dicds')
@section('title', 'Manage Instructors')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Manage Instructors</h1>

    @if(session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    <div style="text-align: right; margin-bottom: 20px;">
        <a href="{{ route('dicds.instructors.add') }}" class="btn">Add New Instructor</a>
    </div>

    <form method="GET" action="{{ route('dicds.instructors.manage') }}" style="margin-bottom: 20px;">
        <div class="form-group">
            <label>Search Instructor Name</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}">
        </div>
        <div style="text-align: center;">
            <button type="submit" class="btn">Search</button>
            <a href="{{ route('dicds.instructors.manage') }}" class="btn">Clear</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>School</th>
                <th>City</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($instructors as $instructor)
            <tr>
                <td>{{ $instructor->first_name }} {{ $instructor->last_name }}</td>
                <td>{{ $instructor->email }}</td>
                <td>{{ $instructor->phone }}</td>
                <td>{{ $instructor->school->school_name ?? 'N/A' }}</td>
                <td>{{ $instructor->city }}</td>
                <td>
                    <a href="{{ route('dicds.instructors.edit', $instructor->id) }}" class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                    <form method="POST" action="{{ route('dicds.instructors.destroy', $instructor->id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 12px; background: #dc2626;" onclick="return confirm('Delete this instructor?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">No instructors found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('dicds.provider-menu') }}" class="btn">Return to Provider Menu</a>
    </div>
</div>
@endsection
