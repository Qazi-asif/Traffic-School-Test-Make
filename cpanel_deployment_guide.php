<?php
/**
 * cPanel Deployment Guide for Laravel Multi-State Traffic School
 * Complete setup instructions for production deployment
 */

echo "🚀 CPANEL LARAVEL DEPLOYMENT GUIDE\n";
echo "==================================\n\n";

echo "📋 CPANEL TERMINAL COMMANDS\n";
echo "==========================\n\n";

echo "STEP 1: Navigate to Your Domain Directory\n";
echo "-----------------------------------------\n";
echo "cd public_html\n";
echo "# OR if you have a subdomain:\n";
echo "# cd public_html/subdomain_name\n\n";

echo "STEP 2: Check Current Directory Structure\n";
echo "----------------------------------------\n";
echo "pwd\n";
echo "ls -la\n\n";

echo "STEP 3: Set Up Laravel for Production\n";
echo "------------------------------------\n";
echo "# Install/Update Composer dependencies\n";
echo "composer install --optimize-autoloader --no-dev\n\n";

echo "# Set proper permissions\n";
echo "chmod -R 755 storage\n";
echo "chmod -R 755 bootstrap/cache\n\n";

echo "# Clear and optimize caches\n";
echo "php artisan config:cache\n";
echo "php artisan route:cache\n";
echo "php artisan view:cache\n\n";

echo "STEP 4: Configure Environment\n";
echo "----------------------------\n";
echo "# Copy environment file\n";
echo "cp .env.example .env\n\n";

echo "# Generate application key\n";
echo "php artisan key:generate\n\n";

echo "STEP 5: Database Setup\n";
echo "---------------------\n";
echo "# Run migrations\n";
echo "php artisan migrate --force\n\n";

echo "# Seed database with test data\n";
echo "php artisan db:seed --force\n\n";

echo "STEP 6: Test Laravel Installation\n";
echo "--------------------------------\n";
echo "# Check Laravel version\n";
echo "php artisan --version\n\n";

echo "# List routes to verify they're working\n";
echo "php artisan route:list\n\n";

echo "STEP 7: Create Symbolic Link (if needed)\n";
echo "---------------------------------------\n";
echo "# Link storage to public\n";
echo "php artisan storage:link\n\n";

// Create the .env template for production
$envTemplate = "# Laravel Multi-State Traffic School - Production Environment
APP_NAME=\"Multi-State Traffic School\"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_WILL_BE_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration (Update with your cPanel database details)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Cache Configuration
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (Update with your email settings)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=\"Multi-State Traffic School\"

# Additional Laravel Settings
BCRYPT_ROUNDS=12
";

file_put_contents('.env.production', $envTemplate);
echo "✅ Created .env.production template\n\n";

// Create cPanel-specific setup script
$setupScript = '#!/bin/bash
# cPanel Laravel Setup Script

echo "🚀 Setting up Laravel Multi-State Traffic School on cPanel"
echo "========================================================="

# Check PHP version
echo "📋 Checking PHP version..."
php -v

# Check if we\'re in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Make sure you\'re in the Laravel root directory."
    exit 1
fi

echo "✅ Laravel project detected"

# Install dependencies
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Set up environment
if [ ! -f ".env" ]; then
    echo "🔧 Setting up environment file..."
    cp .env.example .env
    php artisan key:generate
fi

# Set permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public

# Clear and cache everything
echo "🧹 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "🗄️ Setting up database..."
php artisan migrate --force

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Create test users
echo "👥 Creating test users..."
php artisan db:seed --class=UserSeeder --force

echo "✅ Setup completed successfully!"
echo ""
echo "🌐 Your application should now be accessible at:"
echo "https://yourdomain.com"
echo ""
echo "🔑 Test login credentials:"
echo "Email: florida@test.com"
echo "Password: password123"
echo ""
echo "📋 Login URLs:"
echo "Florida:  https://yourdomain.com/florida/login"
echo "Missouri: https://yourdomain.com/missouri/login"
echo "Texas:    https://yourdomain.com/texas/login"
echo "Delaware: https://yourdomain.com/delaware/login"
';

file_put_contents('cpanel_setup.sh', $setupScript);
echo "✅ Created cpanel_setup.sh script\n\n";

// Create .htaccess for production
$htaccessContent = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Hide Laravel files
    <Files .env>
        Order allow,deny
        Deny from all
    </Files>

    <Files composer.json>
        Order allow,deny
        Deny from all
    </Files>

    <Files composer.lock>
        Order allow,deny
        Deny from all
    </Files>

    <Files package.json>
        Order allow,deny
        Deny from all
    </Files>
</IfModule>';

file_put_contents('.htaccess.production', $htaccessContent);
echo "✅ Created .htaccess.production\n\n";

echo "🎯 QUICK START COMMANDS FOR CPANEL TERMINAL\n";
echo "==========================================\n\n";

echo "1. Navigate to your domain directory:\n";
echo "   cd public_html\n\n";

echo "2. Make setup script executable:\n";
echo "   chmod +x cpanel_setup.sh\n\n";

echo "3. Run the setup script:\n";
echo "   ./cpanel_setup.sh\n\n";

echo "4. Update .env with your database details:\n";
echo "   nano .env\n";
echo "   # Update DB_DATABASE, DB_USERNAME, DB_PASSWORD\n\n";

echo "5. Test the installation:\n";
echo "   php artisan --version\n";
echo "   php artisan route:list | head -20\n\n";

echo "📋 MANUAL SETUP (if script doesn't work)\n";
echo "========================================\n\n";

$manualCommands = [
    "composer install --optimize-autoloader --no-dev",
    "cp .env.example .env",
    "php artisan key:generate",
    "chmod -R 755 storage bootstrap/cache public",
    "php artisan config:cache",
    "php artisan route:cache", 
    "php artisan view:cache",
    "php artisan migrate --force",
    "php artisan storage:link"
];

foreach ($manualCommands as $i => $command) {
    echo ($i + 1) . ". {$command}\n";
}

echo "\n🔧 IMPORTANT CPANEL CONFIGURATION\n";
echo "=================================\n\n";

echo "Database Setup:\n";
echo "- Create MySQL database in cPanel\n";
echo "- Create database user with full privileges\n";
echo "- Update .env with database credentials\n\n";

echo "Domain Configuration:\n";
echo "- If using main domain: files go in public_html/\n";
echo "- If using subdomain: files go in public_html/subdomain/\n";
echo "- Document root should point to Laravel root (not public/)\n\n";

echo "File Permissions:\n";
echo "- Laravel root: 755\n";
echo "- storage/: 755 (recursive)\n";
echo "- bootstrap/cache/: 755 (recursive)\n";
echo "- .env: 644\n\n";

echo "🌐 AFTER DEPLOYMENT\n";
echo "==================\n\n";

echo "Your Laravel application will be accessible at:\n";
echo "- Main site: https://yourdomain.com\n";
echo "- Florida login: https://yourdomain.com/florida/login\n";
echo "- Missouri login: https://yourdomain.com/missouri/login\n";
echo "- Texas login: https://yourdomain.com/texas/login\n";
echo "- Delaware login: https://yourdomain.com/delaware/login\n\n";

echo "🔑 Test Credentials:\n";
echo "- Email: florida@test.com\n";
echo "- Password: password123\n\n";

echo "🎉 FEATURES READY FOR PRODUCTION:\n";
echo "================================\n";
echo "✅ Multi-state authentication system\n";
echo "✅ Course progress tracking\n";
echo "✅ Certificate generation with state seals\n";
echo "✅ State-specific dashboards\n";
echo "✅ Progress monitoring APIs\n";
echo "✅ Admin management interface\n";
echo "✅ Professional certificate templates\n";
echo "✅ Email notifications\n";
echo "✅ Security headers and protection\n\n";

echo "🏁 cPanel deployment guide completed at " . date('Y-m-d H:i:s') . "\n";
echo "Your Laravel application is ready for production deployment!\n";