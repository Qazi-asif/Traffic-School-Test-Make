# Authorize.Net Quick Start

## Setup (3 Steps)

### 1. Add to `.env`
```env
AUTHORIZENET_LOGIN_ID=your_login_id_here
AUTHORIZENET_TRANSACTION_KEY=your_transaction_key_here
AUTHORIZENET_MODE=sandbox
```

### 2. Install Dependencies (Already Done)
```bash
composer install
```

### 3. Test Payment
- Go to any course payment page
- Select "Credit/Debit Card (Authorize.Net)"
- Use test card: **4007000000027**
- Expiry: **12/2025**
- CVV: **123**
- Complete billing info and submit

## What Was Changed

✅ Added Authorize.Net SDK to composer.json  
✅ Added config in `config/payment.php`  
✅ Added `processAuthorizenet()` method in PaymentPageController  
✅ Added `/payment/authorizenet` route  
✅ Added Authorize.Net payment form in checkout view  
✅ Updated `.env.example` with credentials template  

## Payment Methods Available

1. **Authorize.Net** (NEW) - Credit/Debit cards
2. **Stripe** - Credit/Debit cards  
3. **Dummy** - Test payments

## Next Steps

1. Get your Authorize.Net sandbox credentials from https://developer.authorize.net/
2. Add them to your `.env` file
3. Test a payment
4. For production, switch to `AUTHORIZENET_MODE=production` and use live credentials
