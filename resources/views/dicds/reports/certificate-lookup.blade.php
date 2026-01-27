@extends('layouts.dicds')
@section('title', 'Certificate Lookup')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Certificate Lookup/Reprint</h1>

    <form method="GET" action="{{ route('dicds.reports.certificate-lookup') }}">
        <div class="form-group">
            <label>Certificate Number</label>
            <input type="text" name="certificate_number" class="form-control">
        </div>

        <p style="text-align: center; margin: 20px 0;">-- OR --</p>

        <div class="form-group">
            <label>Student Last Name</label>
            <input type="text" name="last_name" class="form-control">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="reprint" value="1">
                Reprint Certificate
            </label>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Search</button>
            <a href="{{ route('dicds.reports.menu') }}" class="btn">Return to Reports Menu</a>
        </div>
    </form>
</div>
@endsection
