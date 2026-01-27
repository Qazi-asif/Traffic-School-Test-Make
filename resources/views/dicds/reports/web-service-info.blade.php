@extends('layouts.dicds')
@section('title', 'Web Service Info')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Web Service Info</h1>

    <p>Quick reference guide showing schools, approved courses, and assigned instructors.</p>

    <table>
        <thead>
            <tr>
                <th>School Name</th>
                <th>Approved Courses</th>
                <th>Assigned Instructors</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="text-align: center;">No data available</td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('dicds.provider-menu') }}" class="btn">Return to Provider Menu</a>
    </div>
</div>
@endsection
