@extends('layouts.dicds')
@section('title', 'DICDS Login')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <div class="login-section">
        <h2>Online Data Exchange</h2>
        
        <div class="login-form-container">
            <div class="case-sensitive-notice">
                <strong>Login ID and Password are case sensitive</strong>
            </div>
            
            <form method="POST" action="{{ route('dicds.login') }}">
                @csrf
                <div class="form-group">
                    <label>Login ID</label>
                    <input type="text" name="login_id" value="{{ old('login_id') }}" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <input type="checkbox" name="change_password" id="change_password">
                    <label for="change_password">Change Password?</label>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
            </form>
            
            @if($errors->any())
                <div class="error-message">{{ $errors->first() }}</div>
            @endif
        </div>
        
        <div class="login-links">
            <a href="{{ route('dicds.register') }}">New User?</a> |
            <a href="#">Forgot your Login ID?</a> |
            <a href="#">Forgot your Password?</a>
        </div>
        
        <div class="instructions">
            <h3>If you already have a User ID and Password do the following:</h3>
            <ul>
                <li>✓ Type in your User ID and Password exactly as you entered it as a new user</li>
                <li>These fields are case sensitive. For your protection, passwords are displayed as astericks.</li>
                <li>You will be locked out of further login attempts after 5 unsuccessful tries.</li>
                <li>✓ Click the <strong>Login</strong> button</li>
                <li>✓ DO NOT give your password to anyone else!</li>
            </ul>
            
            <p><strong>Your electronic password is considered to be the same as your signature.</strong> 
            <em>It is your responsibility to protect your authorization for information entered into this system.</em></p>
        </div>
    </div>
    
    <div class="new-user-notice">
        <strong>If you Do Not have a User ID and Password, Click on the 'New User?' Link.</strong>
    </div>
</div>
@endsection
