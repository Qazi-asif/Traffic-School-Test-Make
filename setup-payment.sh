#!/bin/bash

echo "Setting up Payment System..."

# Install Stripe PHP SDK
composer require stripe/stripe-php

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Payment system setup complete!"
echo ""
echo "Next steps:"
echo "1. Update your .env file with real Stripe and PayPal credentials"
echo "2. Set STRIPE_KEY and STRIPE_SECRET from your Stripe dashboard"
echo "3. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET from PayPal developer console"
echo "4. Change PAYPAL_MODE to 'live' for production"
echo ""
echo "Test the payment flow by enrolling in a course with a price > 0"
