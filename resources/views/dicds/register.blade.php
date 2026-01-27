@extends('layouts.dicds')
@section('title', 'DICDS New User Registration')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>New User Registration</h1>
    <p>Clicking on the 'New User' link will bring you to the following screen to register.</p>

    <div class="login-section">
        <h2>User Registration Page</h2>
        
        <form method="POST" action="{{ route('dicds.register') }}">
            @csrf
            
            <div class="form-group">
                <label>*USER LAST NAME</label>
                <input type="text" name="user_last_name" value="{{ old('user_last_name') }}" required>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>*FIRST NAME</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>MIDDLE</label>
                    <input type="text" name="middle" value="{{ old('middle') }}">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>SUFFIX (JR/SR/ETC.)</label>
                    <input type="text" name="suffix" value="{{ old('suffix') }}">
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>*CONTACT EMAIL ADDRESS</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>*RETYPE EMAIL ADDRESS</label>
                    <input type="email" name="contact_email_confirmation" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>*PHONE NUMBER</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>PHONE EXTENSION</label>
                    <input type="text" name="phone_extension" value="{{ old('phone_extension') }}">
                </div>
            </div>
            
            <p><strong>** Key Phone Number without the dash (-) **</strong></p>
            
            <div class="form-group">
                <label>*Login ID</label>
                <input type="text" name="login_id" value="{{ old('login_id') }}" required>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>*New Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>*Retype New Password</label>
                    <input type="password" name="password_confirmation" required>
                </div>
            </div>
            
            <div class="password-guidelines">
                <h4>Password Guidelines</h4>
                <p><strong>Note: Your Password must meet the following criteria:</strong></p>
                <ul>
                    <li>at least eight characters in length</li>
                    <li>contain upper and lower case characters</li>
                    <li>contain at least one numeric character</li>
                    <li>contain at least one special character.</li>
                    <li>Acceptable special characters are: ! @ # $ % * ( )</li>
                </ul>
                <p><strong>It is recommended that your Password meet the following criteria:</strong></p>
                <ul>
                    <li>does not contain words found in a dictionary</li>
                    <li>does not contain names of pets, family, etc.</li>
                    <li>does not match a previous password</li>
                </ul>
            </div>
            
            <div class="case-sensitive-notice">
                <strong>Note: The Login ID and Password are BOTH Case Sensitive</strong>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <button type="submit" class="btn">Continue to Access Selection</button>
                <button type="button" class="btn" onclick="window.location.href='{{ route('dicds.login') }}'">Cancel</button>
            </div>
            
            @if($errors->any())
                <div class="error-message">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
        
        <div class="instructions">
            <h3>Instructions:</h3>
            <p><strong>1</strong> Enter the information requested in the appropriate spaces.</p>
            <p>The User ID and password fields are <strong>case sensitive</strong>. Each time you log in, you will need to enter the information exactly as you did the first time. Failure to do so will result in the denial of your access to the system.</p>
            <p><strong>2</strong> Click the <strong>Continue to Access Selection</strong> button when you have entered all information.</p>
        </div>
    </div>
</div>
@endsection
