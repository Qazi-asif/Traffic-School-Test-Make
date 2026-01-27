# Registration System Setup Instructions

## Files Copied

### 1. Controller
- `app/Http/Controllers/RegistrationController.php` - Handles 4-step registration process

### 2. Views
- `resources/views/registration/step1.blade.php` - Basic account info
- `resources/views/registration/step2.blade.php` - Personal & court information  
- `resources/views/registration/step3.blade.php` - Security questions
- `resources/views/registration/step4.blade.php` - Review & confirmation

### 3. Database Migration
- `database/migrations/2025_11_11_222552_add_missing_registration_fields_to_users_table.php`

### 4. Model Updates
- Updated `app/Models/User.php` with registration fields in fillable array

### 5. Routes
- Added registration routes to `routes/web.php`

## Setup Steps Required

### 1. Run Database Migration
```bash
php artisan migrate
```

### 2. Ensure Required Database Fields Exist
The migration adds these fields to users table:
- license_state, license_class
- court_selected, citation_number  
- due_month, due_day, due_year
- security_q1 through security_q10
- agreement_name, terms_agreement
- registration_completed_at

### 3. Test Registration Flow
Visit: `/register` or `/register/1`

The registration process:
1. **Step 1**: Basic account info (name, email, password)
2. **Step 2**: Personal details, address, court info
3. **Step 3**: 10 security questions for identity verification
4. **Step 4**: Review all info and accept terms

### 4. Registration Routes Available
- `GET /register/{step?}` - Show registration step
- `POST /register/{step}` - Process registration step

### 5. Session Management
- Each step stores data in session with key `registration_step_{number}`
- Final step creates user and clears session data
- Users are created with role_id = 4 (Student role)

### 6. Dependencies Required
Make sure these are installed:
- Laravel session support
- User model with JWT support (already present)
- Role model/table for role_id = 4

## Registration Features

- **Multi-step process**: 4 steps with session persistence
- **Data validation**: Required fields on each step
- **Security questions**: 10 questions for identity verification
- **Court integration**: Court selection and citation tracking
- **Terms acceptance**: Legal agreement with name confirmation
- **Review step**: Complete data review before submission

## Notes

- Registration creates users with status 'active'
- Default role_id is 4 (Student)
- Security questions are stored for course verification checkpoints
- All personal data is collected for compliance requirements
- Session data is cleared after successful registration
