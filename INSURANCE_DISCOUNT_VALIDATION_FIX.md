# Insurance Discount Validation Fix

## Issue
When the "Insurance Discount Only" checkbox was selected, the form was still showing validation errors for court-related fields:
- The court selected field is required
- The citation number field is required  
- The due month field is required
- The due day field is required
- The due year field is required

## Root Cause
The backend validation in `RegistrationController` was hardcoded to always require court fields, regardless of the insurance discount checkbox state.

## Solution
Updated the backend validation logic to conditionally require court fields based on the insurance discount checkbox.

## Changes Made

### 1. Updated RegistrationController (`app/Http/Controllers/RegistrationController.php`)

**Modified `validateStep()` method for step 2:**
- Added `insurance_discount_only` field to validation rules
- Made court fields conditionally required based on checkbox state
- Court fields are only required when `insurance_discount_only` is not checked

```php
case 2:
    $rules = [
        // ... existing rules ...
        'insurance_discount_only' => ['nullable', 'boolean'],
    ];

    // Only require court fields if insurance discount is not selected
    if (!$request->has('insurance_discount_only') || !$request->input('insurance_discount_only')) {
        $rules['court_selected'] = ['required', 'string', 'max:500'];
        $rules['citation_number'] = ['required', 'string', 'max:100'];
        $rules['due_month'] = ['required', 'integer', 'between:1,12'];
        $rules['due_day'] = ['required', 'integer', 'between:1,31'];
        $rules['due_year'] = ['required', 'integer', 'between:'.date('Y').','.(date('Y') + 2)];
    }

    return $request->validate($rules, $messages);
```

**Modified `completeRegistration()` method:**
- Added `insurance_discount_only` field to user creation
- Made court fields conditionally saved based on checkbox state

```php
'insurance_discount_only' => isset($step2['insurance_discount_only']) ? (bool)$step2['insurance_discount_only'] : false,

// Court information (only if not insurance discount only)
'court_selected' => (!isset($step2['insurance_discount_only']) || !$step2['insurance_discount_only']) ? ($step2['court_selected'] ?? null) : null,
// ... other court fields with same logic
```

### 2. Updated User Model (`app/Models/User.php`)

**Added to fillable array:**
```php
'insurance_discount_only',
```

**Added to casts:**
```php
'insurance_discount_only' => 'boolean',
```

### 3. Created Database Migration

**File:** `database/migrations/2025_01_10_000000_add_insurance_discount_only_to_users_table.php`

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('insurance_discount_only')->default(false)->after('license_class');
    });
}
```

## Validation Logic

### When Insurance Discount is NOT checked:
- All court fields are required
- Normal validation applies
- Court information is saved to database

### When Insurance Discount IS checked:
- Court fields are not required
- No validation errors for missing court information
- Court fields are saved as `null` in database
- `insurance_discount_only` is saved as `true`

## Database Schema

**New column added to `users` table:**
- `insurance_discount_only` (BOOLEAN, DEFAULT FALSE)

## Testing

### Test Cases:
1. **Checkbox unchecked**: Form requires all court fields
2. **Checkbox checked**: Form does not require court fields
3. **State persistence**: Checkbox state maintained after validation errors
4. **Database storage**: Insurance discount flag and court data saved correctly

### Expected Behavior:
- ✅ No validation errors when checkbox is checked and court fields are empty
- ✅ Validation errors when checkbox is unchecked and court fields are empty
- ✅ Checkbox state preserved during form validation
- ✅ Court information conditionally saved based on checkbox

## Migration Required

To apply these changes, run:
```bash
php artisan migrate
```

This will add the `insurance_discount_only` column to the users table.

## Files Modified

1. `app/Http/Controllers/RegistrationController.php` - Updated validation logic
2. `app/Models/User.php` - Added fillable field and cast
3. `database/migrations/2025_01_10_000000_add_insurance_discount_only_to_users_table.php` - New migration
4. `resources/views/registration/step2.blade.php` - Frontend checkbox (already implemented)

## Summary

The validation issue has been resolved by making the backend validation logic conditional based on the insurance discount checkbox. Users can now successfully submit the form when the insurance discount option is selected, bypassing the court information requirements as intended.