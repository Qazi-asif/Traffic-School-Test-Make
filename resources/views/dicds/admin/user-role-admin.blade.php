@extends('layouts.dicds')
@section('title', 'User Role Administration')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>User Role Administration</h1>

    <form method="GET" action="{{ route('dicds.admin.search-users') }}">
        <div class="form-group">
            <label>Search by Status</label>
            <select name="status" class="form-control">
                <option value="">-- All --</option>
                <option value="Pending">Pending</option>
                <option value="Active">Active</option>
                <option value="Denied">Denied</option>
                <option value="Revoked">Revoked</option>
            </select>
        </div>

        <div class="form-group">
            <label>Search by Name</label>
            <input type="text" name="name" class="form-control">
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Search</button>
            <a href="{{ route('dicds.main-menu') }}" class="btn">Return to Main Menu</a>
        </div>
    </form>
</div>
@endsection
