# Technology Stack

## Backend

- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: MySQL/SQLite
- **PDF Generation**: DomPDF (barryvdh/laravel-dompdf)
- **Document Processing**: PHPWord (phpoffice/phpword)
- **Payment**: Stripe PHP SDK

## Frontend

- **Build Tool**: Vite 7.0
- **CSS Framework**: Tailwind CSS 4.0
- **JavaScript Framework**: Vue 3.5
- **Templating**: Blade (Laravel)

## Development Tools

- **Testing**: PHPUnit 11.5
- **Code Style**: Laravel Pint
- **Local Dev**: Laravel Sail
- **Logging**: Laravel Pail

## Common Commands

### Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

### Development
```bash
# Start all services (server, queue, logs, vite)
composer dev

# Or individually:
php artisan serve
php artisan queue:work
npm run dev
```

### Database
```bash
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed  # Reset and seed
```

### Testing
```bash
composer test
php artisan test
```

### Cache Management
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Queue Management
```bash
php artisan queue:work
php artisan queue:restart
php artisan queue:failed  # View failed jobs
```

## Configuration Files

- `config/flhsmv.php`: Florida DICDS/FLHSMV integration
- `config/payment.php`: Payment gateway settings
- `config/mail.php`: Email configuration
- `config/jwt.php`: JWT authentication settings
