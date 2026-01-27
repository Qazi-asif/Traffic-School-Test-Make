@extends('layouts.dicds')
@section('title', 'Add Course to School')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Add Course to School</h1>

    <form method="POST" action="{{ route('dicds.courses.store') }}">
        @csrf
        <div class="form-group">
            <label>Select School *</label>
            <select name="school_id" class="form-control" required>
                <option value="">-- Select School --</option>
                @foreach($schools as $school)
                <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Select Course Type *</label>
            <select name="course_id" class="form-control" required>
                <option value="">-- Select Course --</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->course_type }} - {{ $course->delivery_type }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Status *</label>
            <select name="status" class="form-control" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status Date *</label>
            <input type="date" name="status_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Add Course</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
