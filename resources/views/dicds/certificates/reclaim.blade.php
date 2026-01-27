@extends('layouts.dicds')
@section('title', 'Reclaim Certificates')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Reclaim Certificates</h1>

    <form method="POST" action="{{ route('dicds.certificates.reclaim') }}">
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
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Amount to Reclaim *</label>
            <input type="number" name="amount" class="form-control" min="1" required>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Reclaim</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
