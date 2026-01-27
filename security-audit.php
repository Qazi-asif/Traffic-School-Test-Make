<?php

/**
 * Comprehensive Security & Production Readiness Audit
 * Run: php security-audit.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SecurityAudit
{
    private array $issues = [];

    private array $warnings = [];

    private array $passed = [];

    public function run(): void
    {
        echo "🔍 Starting Comprehensive Security & Production Readiness Audit...\n\n";

        $this->checkEnvironmentConfiguration();
        $this->checkDatabaseSecurity();
        $this->checkAuthenticationSecurity();
        $this->checkFilePermissions();
        $this->checkRoutesSecurity();
        $this->checkViewsExist();
        $this->checkControllersExist();
        $this->checkCsrfProtection();
        $this->checkSqlInjectionVulnerabilities();
        $this->checkXssVulnerabilities();
        $this->checkSensitiveDataExposure();
        $this->checkDependenciesVulnerabilities();
        $this->checkProductionSettings();
        $this->checkMissingViews();
        $this->checkOrphanedRoutes();

        $this->printReport();
    }

    private function checkEnvironmentConfiguration(): void
    {
        echo "📋 Checking Environment Configuration...\n";

        // Check APP_DEBUG
        if (config('app.debug') === true) {
            $this->issues[] = '❌ APP_DEBUG is enabled - MUST be false in production';
        } else {
            $this->passed[] = '✅ APP_DEBUG is disabled';
        }

        // Check APP_ENV
        if (config('app.env') !== 'production') {
            $this->warnings[] = "⚠️  APP_ENV is '{config('app.env')}' - should be 'production'";
        } else {
            $this->passed[] = '✅ APP_ENV is set to production';
        }

        // Check APP_KEY
        if (empty(config('app.key'))) {
            $this->issues[] = "❌ APP_KEY is not set - run 'php artisan key:generate'";
        } else {
            $this->passed[] = '✅ APP_KEY is configured';
        }

        // Check HTTPS
        if (! config('app.url') || ! str_starts_with(config('app.url'), 'https://')) {
            $this->warnings[] = '⚠️  APP_URL should use HTTPS in production';
        } else {
            $this->passed[] = '✅ APP_URL uses HTTPS';
        }

        // Check database credentials
        if (config('database.connections.mysql.password') === '' || config('database.connections.mysql.password') === 'password') {
            $this->issues[] = '❌ Database password is weak or default';
        } else {
            $this->passed[] = '✅ Database password is configured';
        }
    }

    private function checkDatabaseSecurity(): void
    {
        echo "🗄️  Checking Database Security...\n";

        try {
            // Check if database connection works
            DB::connection()->getPdo();
            $this->passed[] = '✅ Database connection successful';

            // Check for users without passwords
            $usersWithoutPassword = DB::table('users')->whereNull('password')->orWhere('password', '')->count();
            if ($usersWithoutPassword > 0) {
                $this->issues[] = "❌ Found {$usersWithoutPassword} users without passwords";
            } else {
                $this->passed[] = '✅ All users have passwords';
            }

            // Check for admin users
            $adminCount = DB::table('users')->where('role_id', 1)->count();
            if ($adminCount === 0) {
                $this->warnings[] = '⚠️  No admin users found';
            } else {
                $this->passed[] = "✅ Found {$adminCount} admin user(s)";
            }

        } catch (\Exception $e) {
            $this->issues[] = '❌ Database connection failed: '.$e->getMessage();
        }
    }

    private function checkAuthenticationSecurity(): void
    {
        echo "🔐 Checking Authentication Security...\n";

        // Check JWT secret
        if (empty(config('jwt.secret'))) {
            $this->issues[] = '❌ JWT secret is not configured';
        } else {
            $this->passed[] = '✅ JWT secret is configured';
        }

        // Check session configuration
        if (config('session.secure') !== true) {
            $this->warnings[] = '⚠️  SESSION_SECURE_COOKIE should be true in production';
        } else {
            $this->passed[] = '✅ Secure session cookies enabled';
        }

        if (config('session.http_only') !== true) {
            $this->issues[] = '❌ SESSION_HTTP_ONLY should be true';
        } else {
            $this->passed[] = '✅ HTTP-only session cookies enabled';
        }
    }

    private function checkFilePermissions(): void
    {
        echo "📁 Checking File Permissions...\n";

        $sensitiveFiles = [
            '.env',
            'config/database.php',
            'config/jwt.php',
        ];

        foreach ($sensitiveFiles as $file) {
            if (File::exists(base_path($file))) {
                $perms = substr(sprintf('%o', fileperms(base_path($file))), -4);
                if ($perms !== '0600' && $perms !== '0644') {
                    $this->warnings[] = "⚠️  {$file} has permissions {$perms} - should be 0600 or 0644";
                } else {
                    $this->passed[] = "✅ {$file} has secure permissions";
                }
            }
        }

        // Check storage directory
        if (! is_writable(storage_path())) {
            $this->issues[] = '❌ storage/ directory is not writable';
        } else {
            $this->passed[] = '✅ storage/ directory is writable';
        }
    }

    private function checkRoutesSecurity(): void
    {
        echo "🛣️  Checking Routes Security...\n";

        $routes = Route::getRoutes();
        $unprotectedAdminRoutes = 0;
        $totalAdminRoutes = 0;

        foreach ($routes as $route) {
            $uri = $route->uri();

            if (str_starts_with($uri, 'admin/')) {
                $totalAdminRoutes++;
                $middleware = $route->middleware();

                if (! in_array('auth', $middleware) && ! in_array('role:super-admin,admin', $middleware)) {
                    $unprotectedAdminRoutes++;
                }
            }
        }

        if ($unprotectedAdminRoutes > 0) {
            $this->issues[] = "❌ Found {$unprotectedAdminRoutes} unprotected admin routes out of {$totalAdminRoutes}";
        } else {
            $this->passed[] = "✅ All {$totalAdminRoutes} admin routes are protected";
        }
    }

    private function checkViewsExist(): void
    {
        echo "👁️  Checking Views Exist...\n";

        $routes = Route::getRoutes();
        $missingViews = [];

        foreach ($routes as $route) {
            $action = $route->getAction();

            if (isset($action['uses']) && is_string($action['uses'])) {
                // Skip API routes
                if (str_starts_with($route->uri(), 'api/')) {
                    continue;
                }
            }
        }

        if (count($missingViews) > 0) {
            $this->warnings[] = '⚠️  Found '.count($missingViews).' potentially missing views';
        } else {
            $this->passed[] = '✅ View structure looks good';
        }
    }

    private function checkControllersExist(): void
    {
        echo "🎮 Checking Controllers...\n";

        $routes = Route::getRoutes();
        $missingControllers = [];

        foreach ($routes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                [$controller, $method] = explode('@', $action['controller']);

                if (! class_exists($controller)) {
                    $missingControllers[] = $controller;
                }
            }
        }

        if (count($missingControllers) > 0) {
            $this->issues[] = '❌ Found '.count($missingControllers).' missing controllers';
        } else {
            $this->passed[] = '✅ All controllers exist';
        }
    }

    private function checkCsrfProtection(): void
    {
        echo "🛡️  Checking CSRF Protection...\n";

        $routes = Route::getRoutes();
        $unprotectedPostRoutes = 0;

        foreach ($routes as $route) {
            $methods = $route->methods();

            if (in_array('POST', $methods) || in_array('PUT', $methods) || in_array('DELETE', $methods)) {
                $middleware = $route->middleware();

                if (! in_array('web', $middleware) && ! str_starts_with($route->uri(), 'api/')) {
                    $unprotectedPostRoutes++;
                }
            }
        }

        if ($unprotectedPostRoutes > 0) {
            $this->warnings[] = "⚠️  Found {$unprotectedPostRoutes} POST/PUT/DELETE routes without CSRF protection";
        } else {
            $this->passed[] = '✅ CSRF protection is properly configured';
        }
    }

    private function checkSqlInjectionVulnerabilities(): void
    {
        echo "💉 Checking SQL Injection Vulnerabilities...\n";

        $controllers = File::allFiles(app_path('Http/Controllers'));
        $vulnerableFiles = [];

        foreach ($controllers as $file) {
            $content = File::get($file->getPathname());

            // Check for raw queries with concatenation
            if (preg_match('/DB::raw\([^)]*\$/', $content) ||
                preg_match('/DB::select\([^)]*\$/', $content) ||
                preg_match('/whereRaw\([^)]*\$/', $content)) {
                $vulnerableFiles[] = $file->getRelativePathname();
            }
        }

        if (count($vulnerableFiles) > 0) {
            $this->warnings[] = '⚠️  Found '.count($vulnerableFiles).' files with potential SQL injection risks';
        } else {
            $this->passed[] = '✅ No obvious SQL injection vulnerabilities found';
        }
    }

    private function checkXssVulnerabilities(): void
    {
        echo "🔓 Checking XSS Vulnerabilities...\n";

        $views = File::allFiles(resource_path('views'));
        $vulnerableViews = [];

        foreach ($views as $file) {
            $content = File::get($file->getPathname());

            // Check for unescaped output
            if (preg_match('/\{\!\!.*?\!\!\}/', $content)) {
                $vulnerableViews[] = $file->getRelativePathname();
            }
        }

        if (count($vulnerableViews) > 0) {
            $this->warnings[] = '⚠️  Found '.count($vulnerableViews).' views with unescaped output {!! !!}';
        } else {
            $this->passed[] = '✅ No unescaped output found in views';
        }
    }

    private function checkSensitiveDataExposure(): void
    {
        echo "🔒 Checking Sensitive Data Exposure...\n";

        // Check if .env is in .gitignore
        $gitignore = File::get(base_path('.gitignore'));
        if (! str_contains($gitignore, '.env')) {
            $this->issues[] = '❌ .env is not in .gitignore';
        } else {
            $this->passed[] = '✅ .env is in .gitignore';
        }

        // Check for exposed sensitive files
        $exposedFiles = [];
        $sensitivePatterns = ['.env', 'composer.json', 'composer.lock', '.git'];

        foreach ($sensitivePatterns as $pattern) {
            if (File::exists(public_path($pattern))) {
                $exposedFiles[] = $pattern;
            }
        }

        if (count($exposedFiles) > 0) {
            $this->issues[] = '❌ Sensitive files exposed in public/: '.implode(', ', $exposedFiles);
        } else {
            $this->passed[] = '✅ No sensitive files in public/';
        }
    }

    private function checkDependenciesVulnerabilities(): void
    {
        echo "📦 Checking Dependencies...\n";

        if (! File::exists(base_path('composer.lock'))) {
            $this->warnings[] = "⚠️  composer.lock not found - run 'composer install'";
        } else {
            $this->passed[] = '✅ composer.lock exists';
        }
    }

    private function checkProductionSettings(): void
    {
        echo "⚙️  Checking Production Settings...\n";

        // Check cache configuration
        if (config('cache.default') === 'file') {
            $this->warnings[] = '⚠️  Consider using Redis or Memcached for cache in production';
        } else {
            $this->passed[] = '✅ Using '.config('cache.default').' for cache';
        }

        // Check queue configuration
        if (config('queue.default') === 'sync') {
            $this->warnings[] = "⚠️  Queue driver is 'sync' - use 'database' or 'redis' in production";
        } else {
            $this->passed[] = '✅ Using '.config('queue.default').' for queues';
        }

        // Check logging
        if (config('logging.default') === 'single') {
            $this->warnings[] = "⚠️  Consider using 'daily' or 'stack' logging in production";
        } else {
            $this->passed[] = '✅ Using '.config('logging.default').' for logging';
        }
    }

    private function checkMissingViews(): void
    {
        echo "🔍 Checking for Missing Views...\n";

        $controllers = File::allFiles(app_path('Http/Controllers'));
        $missingViews = [];

        foreach ($controllers as $file) {
            $content = File::get($file->getPathname());

            // Find view() calls
            preg_match_all('/view\([\'"]([^\'"]+)[\'"]\)/', $content, $matches);

            foreach ($matches[1] as $viewName) {
                $viewPath = str_replace('.', '/', $viewName).'.blade.php';

                if (! File::exists(resource_path('views/'.$viewPath))) {
                    $missingViews[] = $viewName;
                }
            }
        }

        if (count($missingViews) > 0) {
            $this->issues[] = '❌ Found '.count($missingViews).' missing views';
            foreach (array_slice($missingViews, 0, 10) as $view) {
                echo "   - {$view}\n";
            }
        } else {
            $this->passed[] = '✅ All referenced views exist';
        }
    }

    private function checkOrphanedRoutes(): void
    {
        echo "🗺️  Checking for Orphaned Routes...\n";

        $routes = Route::getRoutes();
        $orphanedRoutes = [];

        foreach ($routes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                [$controller, $method] = explode('@', $action['controller']);

                if (class_exists($controller)) {
                    if (! method_exists($controller, $method)) {
                        $orphanedRoutes[] = "{$controller}@{$method}";
                    }
                }
            }
        }

        if (count($orphanedRoutes) > 0) {
            $this->issues[] = '❌ Found '.count($orphanedRoutes).' routes with missing controller methods';
        } else {
            $this->passed[] = '✅ All routes have valid controller methods';
        }
    }

    private function printReport(): void
    {
        echo "\n".str_repeat('=', 80)."\n";
        echo "📊 SECURITY AUDIT REPORT\n";
        echo str_repeat('=', 80)."\n\n";

        if (count($this->issues) > 0) {
            echo '🚨 CRITICAL ISSUES ('.count($this->issues)."):\n";
            foreach ($this->issues as $issue) {
                echo "   {$issue}\n";
            }
            echo "\n";
        }

        if (count($this->warnings) > 0) {
            echo '⚠️  WARNINGS ('.count($this->warnings)."):\n";
            foreach ($this->warnings as $warning) {
                echo "   {$warning}\n";
            }
            echo "\n";
        }

        echo '✅ PASSED CHECKS ('.count($this->passed)."):\n";
        foreach (array_slice($this->passed, 0, 10) as $pass) {
            echo "   {$pass}\n";
        }
        if (count($this->passed) > 10) {
            echo '   ... and '.(count($this->passed) - 10)." more\n";
        }

        echo "\n".str_repeat('=', 80)."\n";
        echo "SUMMARY:\n";
        echo '  Critical Issues: '.count($this->issues)."\n";
        echo '  Warnings: '.count($this->warnings)."\n";
        echo '  Passed: '.count($this->passed)."\n";
        echo str_repeat('=', 80)."\n\n";

        if (count($this->issues) === 0 && count($this->warnings) === 0) {
            echo "🎉 Your application is production-ready!\n\n";
        } elseif (count($this->issues) === 0) {
            echo "✅ No critical issues found, but please review warnings.\n\n";
        } else {
            echo "❌ Please fix critical issues before deploying to production.\n\n";
        }
    }
}

// Run the audit
$audit = new SecurityAudit;
$audit->run();
