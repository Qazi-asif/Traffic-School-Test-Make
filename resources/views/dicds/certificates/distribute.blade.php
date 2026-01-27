@extends('layouts.dicds')
@section('title', 'Distribute Certificates')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Distribute Certificates</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('dicds.certificates.store-distribution') }}">
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
            <label>Select Course *</label>
            <select name="course_id" class="form-control" required>
                <option value="">-- Select Course --</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Amount to Distribute *</label>
            <input type="number" name="amount" class="form-control" min="1" required>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Distribute</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
