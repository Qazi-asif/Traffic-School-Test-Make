<?php

// Add this to config/auth.php in the 'guards' array:
/*
'api' => [
    'driver' => 'jwt',
    'provider' => 'users',
],
*/

// Add this to app/Http/Kernel.php in the $routeMiddleware array:
/*
'role' => \App\Http\Middleware\RoleMiddleware::class,
*/

// PostgreSQL Database Configuration for .env:
/*
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=elearning
DB_USERNAME=your_username
DB_PASSWORD=your_password
*/
