@extends('layouts.dicds')
@section('title', 'Edit Instructor')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Edit Instructor</h1>

    <form method="POST" action="{{ route('dicds.instructors.update', $instructor->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label>Select School *</label>
            <select name="school_id" class="form-control" required>
                <option value="">-- Select School --</option>
                @foreach($schools as $school)
                <option value="{{ $school->id }}" {{ $instructor->school_id == $school->id ? 'selected' : '' }}>{{ $school->school_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" class="form-control" value="{{ $instructor->first_name }}" required>
        </div>

        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" class="form-control" value="{{ $instructor->last_name }}" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="form-control" value="{{ $instructor->email }}" required>
        </div>

        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" value="{{ $instructor->phone }}" required>
        </div>

        <div class="form-group">
            <label>Address *</label>
            <input type="text" name="address" class="form-control" value="{{ $instructor->address }}" required>
        </div>

        <div class="form-group">
            <label>City *</label>
            <input type="text" name="city" class="form-control" value="{{ $instructor->city }}" required>
        </div>

        <div class="form-group">
            <label>State *</label>
            <input type="text" name="state" class="form-control" value="{{ $instructor->state }}" required>
        </div>

        <div class="form-group">
            <label>Zip Code *</label>
            <input type="text" name="zip_code" class="form-control" value="{{ $instructor->zip_code }}" required>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Update Instructor</button>
            <a href="{{ route('dicds.instructors.manage') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
