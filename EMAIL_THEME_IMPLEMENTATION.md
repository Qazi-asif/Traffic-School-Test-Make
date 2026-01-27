# Email Theme Implementation - Global Theme Switcher

## Overview
All email templates have been updated to use a consistent global theme with olive, gold, and green colors. This ensures a professional and cohesive brand experience across all user communications.

## Color Palette
- **Primary Olive**: `#556B2F` - Used for headers, main text, and primary elements
- **Secondary Olive**: `#6B8E23` - Used for accents and secondary elements
- **Gold/Accent**: `#DAA520` - Used for highlights and secondary buttons
- **Green/Success**: `#228B22` - Used for success states and positive actions
- **Dark Green**: `#1a6b1a` - Used for hover states

## Architecture

### Shared Layout Template
**File**: `resources/views/emails/layout.blade.php`

This is the master layout that all email templates extend from. It includes:
- Consistent styling and color scheme
- Responsive design
- Reusable CSS classes
- Footer with copyright information

### CSS Classes Available

#### Buttons
```blade
<a href="#" class="button">Primary Button</a>
<a href="#" class="button button-secondary">Secondary Button (Gold)</a>
<a href="#" class="button button-success">Success Button (Green)</a>
```

#### Alerts
```blade
<div class="alert alert-info">Info message</div>
<div class="alert alert-warning">Warning message</div>
<div class="alert alert-success">Success message</div>
```

#### Text Styling
```blade
<span class="highlight">Highlighted text (Olive)</span>
<span class="accent-gold">Gold accent text</span>
<span class="accent-green">Green accent text</span>
<p class="text-muted">Muted text</p>
```

#### Components
```blade
<div class="details">Details box with left border</div>
<div class="achievement-box">Achievement/certificate box</div>
<div class="stats">Statistics container</div>
```

## Updated Email Templates

### 1. Welcome Email
**File**: `resources/views/emails/welcome.blade.php`
- Sent on user registration
- Introduces the platform
- Provides getting started tips

### 2. Enrollment Confirmation
**File**: `resources/views/emails/enrollment-confirmation.blade.php`
- Sent when user enrolls in a course
- Shows enrollment details
- Provides course access link

### 3. Course Enrolled
**File**: `resources/views/emails/courses/enrolled.blade.php`
- Sent on course enrollment
- Highlights course features
- Provides learning tips

### 4. Course Completed
**File**: `resources/views/emails/courses/completed.blade.php`
- Sent when user completes a course
- Shows completion statistics
- Provides certificate access

### 5. Certificate Generated
**File**: `resources/views/emails/certificates/generated.blade.php`
- Sent when certificate is generated
- Shows certificate details
- Provides download and sharing options

### 6. Certificate Generated (Alternative)
**File**: `resources/views/emails/certificate-generated.blade.php`
- Alternative certificate notification
- Celebrates achievement
- Provides sharing guidance

### 7. Certificate Delivery
**File**: `resources/views/emails/certificate-delivery.blade.php`
- Sent when certificate is ready for download
- Explains certificate usage
- Provides download link

### 8. Payment Approved
**File**: `resources/views/emails/payments/approved.blade.php`
- Sent when payment is processed
- Shows payment details
- Provides course access confirmation

### 9. Invoice
**File**: `resources/views/emails/invoice.blade.php`
- Sent with invoice details
- Shows itemized charges
- Provides download option

### 10. Payment Receipt
**File**: `resources/views/emails/payment-receipt.blade.php`
- Sent as payment confirmation
- Shows transaction details
- Provides receipt download

### 11. Support Ticket Notification
**File**: `resources/views/emails/ticket-notification.blade.php`
- Sent when support ticket is created
- Shows ticket details
- Provides tracking information

## Usage Example

To create a new email template using the theme:

```blade
@extends('emails.layout')

@section('content')
<div class="header">
    <h1>Your Title Here</h1>
    <p>Subtitle or description</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Your email content here.</p>
    
    <div class="details">
        <h3>Details Section</h3>
        <p><strong>Key:</strong> Value</p>
    </div>
    
    <div style="text-align: center;">
        <a href="#" class="button">Primary Action</a>
        <a href="#" class="button button-secondary">Secondary Action</a>
    </div>
    
    <div class="alert alert-success">
        <strong>Success:</strong> Your message here.
    </div>
    
    <p>Closing message.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
```

## Customization

### Changing Colors
To modify the color scheme, edit `resources/views/emails/layout.blade.php` and update the CSS variables:

```css
/* Primary colors */
background: linear-gradient(135deg, #556B2F 0%, #6B8E23 100%);
color: #556B2F;

/* Accent colors */
color: #DAA520;  /* Gold */
color: #228B22;  /* Green */
```

### Adding New Styles
Add new CSS classes to the `<style>` section in `layout.blade.php`:

```css
.custom-class {
    /* Your styles */
}
```

## Best Practices

1. **Always extend the layout**: Use `@extends('emails.layout')` in all email templates
2. **Use semantic HTML**: Use proper heading tags (h1, h2, h3) for structure
3. **Highlight user names**: Wrap user names in `<span class="highlight">` for consistency
4. **Use appropriate buttons**: Choose button styles based on action importance
5. **Include alerts**: Use alert boxes for important information
6. **Responsive design**: The layout is mobile-responsive by default
7. **Test emails**: Always test emails in different email clients

## Testing

To test email templates:

1. Use Laravel's email preview feature
2. Send test emails to your email address
3. Check rendering in different email clients (Gmail, Outlook, Apple Mail, etc.)
4. Verify all links work correctly
5. Check that colors display properly

## Maintenance

When updating email templates:
1. Keep the layout structure consistent
2. Use the predefined CSS classes
3. Test in multiple email clients
4. Update this documentation if adding new templates
5. Maintain the color scheme across all emails

## Support

For questions or issues with email templates:
1. Check the layout.blade.php for available classes
2. Review existing templates for examples
3. Test in email client before deploying
4. Ensure all variables are properly passed from controllers
