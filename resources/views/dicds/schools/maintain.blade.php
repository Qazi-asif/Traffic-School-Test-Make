@extends('layouts.dicds')
@section('title', 'Maintain School')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Maintain School</h1>

    @if(session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('dicds.schools.maintain') }}" style="margin-bottom: 20px;">
        <div class="form-group">
            <label>Search School Name</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}">
        </div>
        <div style="text-align: center;">
            <button type="submit" class="btn">Search</button>
            <a href="{{ route('dicds.schools.maintain') }}" class="btn">Clear</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>School Name</th>
                <th>City</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schools as $school)
            <tr>
                <td>{{ $school->school_name }}</td>
                <td>{{ $school->city }}</td>
                <td>{{ $school->phone }}</td>
                <td>{{ $school->email }}</td>
                <td>{{ $school->status }}</td>
                <td>
                    <a href="{{ route('dicds.schools.edit', $school->id) }}" class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                    <form method="POST" action="{{ route('dicds.schools.destroy', $school->id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 12px; background: #dc2626;" onclick="return confirm('Delete this school?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">No schools found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('dicds.provider-menu') }}" class="btn">Return to Provider Menu</a>
    </div>
</div>
@endsection
