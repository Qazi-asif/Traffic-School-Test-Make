@extends('layouts.dicds')
@section('title', 'Contact Us')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Contact Us - Questions and Comments</h1>

    <form method="POST" action="{{ route('dicds.contact.submit') }}">
        @csrf
        <div class="form-group">
            <label>Your Email Address *</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Subject *</label>
            <input type="text" name="subject" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Description of Question or Issue *</label>
            <textarea name="message" class="form-control" rows="6" required></textarea>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Submit</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
