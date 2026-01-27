@extends('layouts.dicds')
@section('title', 'Add School')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Add a New School</h1>

    <form method="POST" action="{{ route('dicds.schools.store') }}">
        @csrf
        <div class="form-group">
            <label>School Name *</label>
            <input type="text" name="school_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Address *</label>
            <textarea name="address" class="form-control" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label>City *</label>
            <input type="text" name="city" class="form-control" required>
        </div>

        <div class="form-group">
            <label>State *</label>
            <input type="text" name="state" class="form-control" value="FL" required>
        </div>

        <div class="form-group">
            <label>Zip Code *</label>
            <input type="text" name="zip_code" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Fax</label>
            <input type="text" name="fax" class="form-control">
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Contact Person</label>
            <input type="text" name="contact_person" class="form-control">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="disable_certificates" value="1">
                Disable School's ability to print certificates
            </label>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Save School</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
