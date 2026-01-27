@extends('layouts.dicds')
@section('title', 'Certificate Reports')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Certificate Reports Menu</h1>

    <div class="provider-menu">
        <div class="menu-section">
            <div class="menu-header">Reports</div>
            <div class="menu-item"><a href="{{ route('dicds.reports.certificate-lookup') }}">Certificate Lookup/Reprint</a></div>
            <div class="menu-item"><a href="{{ route('dicds.reports.school-activity') }}">School Activity Report</a></div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('dicds.provider-menu') }}" class="btn">Return to Provider Menu</a>
    </div>
</div>
@endsection
