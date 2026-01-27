@extends('layouts.dicds')
@section('title', 'Schools Certificates')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Schools' Certificates</h1>

    <p>Certificate count by course for each school.</p>

    <table>
        <thead>
            <tr>
                <th>School Name</th>
                <th>Course Type</th>
                <th>Certificate Count</th>
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
