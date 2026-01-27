@extends('layouts.dicds')
@section('title', 'DICDS Provider Menu')
@section('content')
<style>
.provider-menu {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 30px 0;
}
.menu-section {
    background: white;
    border: 2px solid #003366;
    border-radius: 8px;
    padding: 20px;
}
.menu-header {
    background: #003366;
    color: white;
    padding: 10px;
    margin: -20px -20px 15px -20px;
    font-weight: bold;
    font-size: 18px;
    border-radius: 6px 6px 0 0;
}
.menu-item {
    padding: 10px;
    margin: 5px 0;
    background: #f0f0f0;
    border-left: 4px solid #003366;
}
.menu-item a {
    color: #003366;
    text-decoration: none;
    font-weight: 500;
}
.menu-item:hover {
    background: #e0e0e0;
}
</style>
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Provider Menu</h1>
    <p>This screen is the main menu for users logging in as DRS_Provider_Admin. The menu is divided into three functional areas: Schools, Certificates, and Inquiry Menu.</p>

    <div class="provider-menu">
        <!-- Schools Section -->
        <div class="menu-section">
            <div class="menu-header">Schools</div>
            <div class="menu-item"><a href="{{ route('dicds.schools.add') }}">New School</a></div>
            <div class="menu-item"><a href="{{ route('dicds.schools.maintain') }}">Maintain School</a></div>
            <div class="menu-item"><a href="{{ route('dicds.courses.add') }}">Add Course to School</a></div>
            <div class="menu-item"><a href="{{ route('dicds.instructors.add') }}">Add Instructor</a></div>
            <div class="menu-item"><a href="{{ route('dicds.instructors.manage') }}">Update Instructor</a></div>
        </div>

        <!-- Certificates Section -->
        <div class="menu-section">
            <div class="menu-header">Certificates</div>
            <div class="menu-item"><a href="{{ route('dicds.certificates.order') }}">Order Certificates</a></div>
            <div class="menu-item"><a href="{{ route('dicds.certificates.distribute') }}">Distribute Certificates</a></div>
            <div class="menu-item"><a href="{{ route('dicds.certificates.reclaim') }}">Reclaim Certificates</a></div>
            <div class="menu-item"><a href="{{ route('dicds.certificates.maintain') }}">Maintain Certificates</a></div>
        </div>

        <!-- Inquiry Menu Section -->
        <div class="menu-section">
            <div class="menu-header">Inquiry Menu</div>
            <div class="menu-item"><a href="{{ route('dicds.web-service-info') }}">Web Service Info</a></div>
            <div class="menu-item"><a href="{{ route('dicds.reports.schools-certificates') }}">Schools' Certificates</a></div>
            <div class="menu-item"><a href="{{ route('dicds.reports.menu') }}">Certificate Reports</a></div>
            <div class="menu-item"><a href="{{ route('dicds.contact') }}">Contact Us</a></div>
        </div>
    </div>

    <div class="instructions">
        <p><strong>The course provider must enter the basic information in the DICDS for each contracted school (licensee) and for each approved instructor upon implementation of the system. As schools and instructors are added or terminated the course provider must update the data in the system.</strong></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('dicds.main-menu') }}" class="btn">Return to Main Menu</a>
    </div>
</div>
@endsection
