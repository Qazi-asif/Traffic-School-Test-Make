# Insurance Discount Checkbox Implementation

## Overview
Added an "Insurance Discount Only" checkbox option above the Court Information section in the registration step 2 form. When checked, this option allows users to bypass the court information requirements.

## Features Implemented

### 1. Insurance Discount Section
- **Location**: Added above the existing Court Information section
- **Styling**: Matches existing form styling with subtle background differentiation
- **Heading**: "For Insurance Discount Only"
- **Checkbox Text**: "If this box is selected, court selector, case/citation number and traffic school due date is not required"

### 2. Show/Hide Functionality
- **When Checked**: Court Information section is hidden (`display: none`)
- **When Unchecked**: Court Information section is visible (`display: block`)
- **Smooth Toggle**: Instant show/hide without animation to maintain form flow

### 3. Form Validation Handling
- **Dynamic Required Fields**: 
  - When checkbox is checked: Removes `required` attribute from court fields
  - When checkbox is unchecked: Adds `required` attribute back to court fields
- **Affected Fields**:
  - Court Selected (`court_selected`)
  - Citation Number/Case Number (`citation_number`)
  - Due Date Month (`due_month`)
  - Due Date Day (`due_day`)
  - Due Date Year (`due_year`)

### 4. State Persistence
- **Form Memory**: Checkbox state is preserved using Laravel's `old()` helper and session data
- **Page Reload**: Maintains checkbox state and court section visibility after validation errors

## Technical Implementation

### HTML Structure
```html
<!-- Insurance Discount Option -->
<div class="court-section" style="background: #f8f9fa; border: 1px solid #dee2e6; margin-bottom: 20px;">
    <h3 style="color: #495057; margin-bottom: 20px;">For Insurance Discount Only</h3>
    <div class="form-row">
        <div class="form-group full-width">
            <label style="display: flex; align-items: center; cursor: pointer; font-weight: normal;">
                <input type="checkbox" id="insurance_discount_only" name="insurance_discount_only" value="1" 
                       {{ old('insurance_discount_only', session('registration_step_2.insurance_discount_only')) ? 'checked' : '' }}
                       style="margin-right: 12px; transform: scale(1.2);">
                If this box is selected, court selector, case/citation number and traffic school due date is not required
            </label>
        </div>
    </div>
</div>

<div class="court-section" id="court-information-section">
    <h3>Court Information</h3>
    <!-- Existing court information fields -->
</div>
```

### JavaScript Functionality
```javascript
function toggleCourtInformation() {
    const checkbox = document.getElementById('insurance_discount_only');
    const courtSection = document.getElementById('court-information-section');
    const courtFields = [
        document.getElementById('court_selected'),
        document.getElementById('citation_number'),
        document.querySelector('select[name="due_month"]'),
        document.querySelector('select[name="due_day"]'),
        document.querySelector('select[name="due_year"]')
    ];
    
    if (checkbox.checked) {
        // Hide court information and remove required attributes
        courtSection.style.display = 'none';
        courtFields.forEach(field => {
            if (field) field.removeAttribute('required');
        });
    } else {
        // Show court information and add required attributes
        courtSection.style.display = 'block';
        courtFields.forEach(field => {
            if (field) field.setAttribute('required', 'required');
        });
    }
}
```

## User Experience

### Default State
- Insurance discount checkbox is unchecked
- Court Information section is visible
- All court fields are required

### When Insurance Discount is Selected
- Court Information section disappears
- Form becomes shorter and simpler
- No court-related validation errors
- User can proceed without court details

### When Insurance Discount is Deselected
- Court Information section reappears
- All court fields become required again
- Normal court selection workflow resumes

## Form Validation Impact

### Backend Validation
The backend form validation should be updated to handle the insurance discount option:

```php
// In the registration controller
$rules = [
    'mailing_address' => 'required|string|max:255',
    'city' => 'required|string|max:100',
    'state' => 'required|string|max:50',
    'zip' => 'required|string|max:10',
    // ... other fields
];

// Conditionally add court field requirements
if (!$request->has('insurance_discount_only')) {
    $rules['court_selected'] = 'required|string';
    $rules['citation_number'] = 'required|string';
    $rules['due_month'] = 'required|integer|min:1|max:12';
    $rules['due_day'] = 'required|integer|min:1|max:31';
    $rules['due_year'] = 'required|integer|min:' . date('Y');
}
```

## Benefits

1. **User Choice**: Provides flexibility for users who only need insurance discount
2. **Simplified Flow**: Reduces form complexity when court info isn't needed
3. **Better UX**: Clear indication of when fields are optional
4. **Validation Logic**: Smart form validation based on user selection
5. **Consistent Styling**: Maintains existing design language

## Files Modified

- `resources/views/registration/step2.blade.php`
  - Added insurance discount checkbox section
  - Added JavaScript toggle functionality
  - Enhanced form validation handling

## Testing Checklist

- [ ] Checkbox toggles court section visibility
- [ ] Required attributes are properly managed
- [ ] Form submission works with checkbox checked
- [ ] Form submission works with checkbox unchecked
- [ ] State is preserved after validation errors
- [ ] Styling matches existing form design
- [ ] Court dropdown functionality remains intact when visible