# Authorize.Net Payment Integration

## Overview

Authorize.Net has been integrated as a payment gateway option alongside Stripe. Users can now pay for courses using credit/debit cards processed through Authorize.Net.

## Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```env
AUTHORIZENET_LOGIN_ID=your_login_id_here
AUTHORIZENET_TRANSACTION_KEY=your_transaction_key_here
AUTHORIZENET_MODE=sandbox
```

**Modes:**
- `sandbox` - For testing (default)
- `production` - For live transactions

### 2. Get Authorize.Net Credentials

**Sandbox (Testing):**
1. Sign up at https://developer.authorize.net/hello_world/sandbox/
2. Get your API Login ID and Transaction Key from the sandbox account

**Production:**
1. Sign up for a merchant account at https://www.authorize.net/
2. Get your API credentials from the merchant interface

### 3. Test Cards (Sandbox Mode)

Use these test card numbers in sandbox mode:

- **Visa:** 4007000000027
- **Mastercard:** 5424000000000015
- **Amex:** 370000000000002
- **Discover:** 6011000000000012

**Expiry:** Any future date (e.g., 12/2025)  
**CVV:** Any 3-4 digits (e.g., 123)

## Features

- Secure credit/debit card processing
- Real-time transaction validation
- Automatic payment receipt generation
- Email notifications on successful payment
- Full billing address capture
- Transaction logging

## Payment Flow

1. User selects "Credit/Debit Card (Authorize.Net)" on checkout page
2. User enters billing information and card details
3. Payment is processed through Authorize.Net API
4. On success:
   - Enrollment status updated to "paid"
   - Payment record created
   - Invoice generated automatically (via PaymentObserver)
   - Receipt emailed to user
   - User redirected to course player

## Routes

- `POST /payment/authorizenet` - Process Authorize.Net payment

## Files Modified

- `composer.json` - Added authorizenet/authorizenet package
- `config/payment.php` - Added Authorize.Net configuration
- `.env.example` - Added Authorize.Net environment variables
- `app/Http/Controllers/PaymentPageController.php` - Added processAuthorizenet() method
- `routes/web.php` - Added Authorize.Net payment route
- `resources/views/payment/checkout.blade.php` - Added Authorize.Net payment form

## Testing

1. Set `AUTHORIZENET_MODE=sandbox` in `.env`
2. Add your sandbox credentials
3. Navigate to course payment page
4. Select "Credit/Debit Card (Authorize.Net)"
5. Use test card: 4007000000027
6. Complete the payment

## Troubleshooting

**Error: "No response from payment gateway"**
- Check your API credentials are correct
- Verify the mode is set correctly (sandbox/production)

**Error: "Transaction Failed"**
- Check card details are valid
- Ensure billing address is complete
- Review logs in `storage/logs/laravel.log`

**Error: "Payment processing failed"**
- Check Authorize.Net service status
- Verify your account is active
- Check API credentials

## Security Notes

- Card details are sent directly to Authorize.Net (not stored on server)
- All transactions use HTTPS
- CVV is never stored
- Transaction IDs are logged for reference
- Failed transactions are logged with error details
