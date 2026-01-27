#!/bin/bash

# Deployment script for timer fix
# This script clears all caches and ensures the updated files are deployed

echo "🚀 Deploying Timer Fix..."
echo ""

# Step 1: Clear Laravel caches
echo "📦 Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✅ Laravel caches cleared"
echo ""

# Step 2: Clear OPcache (if available)
echo "🔄 Clearing OPcache..."
if command -v php &> /dev/null; then
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared'; } else { echo 'OPcache not available'; }"
fi
echo ""

# Step 3: Touch files to update modification time
echo "📝 Updating file timestamps..."
touch public/js/strict-timer.js
touch resources/views/course-player.blade.php
echo "✅ File timestamps updated"
echo ""

# Step 4: Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Production optimization complete"
echo ""

echo "✨ Deployment complete!"
echo ""
echo "📋 Next steps:"
echo "1. Clear your browser cache (Ctrl+Shift+Delete)"
echo "2. Do a hard refresh (Ctrl+F5 or Cmd+Shift+R)"
echo "3. Test the timer functionality"
echo ""
echo "If issues persist, check:"
echo "- Browser console for JavaScript errors (F12)"
echo "- Server error logs: storage/logs/laravel.log"
echo "- Verify files were uploaded correctly"
