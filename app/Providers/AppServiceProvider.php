<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\UserCourseEnrollment::observe(\App\Observers\EnrollmentObserver::class);
        
        // Custom Blade directive for safe date formatting
        \Blade::directive('safeDate', function ($expression) {
            return "<?php 
                \$value = $expression;
                try {
                    echo is_string(\$value) 
                        ? \Carbon\Carbon::parse(\$value)->format('M d, Y')
                        : \$value->format('M d, Y');
                } catch (\Exception \$e) {
                    echo \$value;
                }
            ?>";
        });
        
        \Blade::directive('safeDatetime', function ($expression) {
            return "<?php 
                \$value = $expression;
                try {
                    echo is_string(\$value) 
                        ? \Carbon\Carbon::parse(\$value)->format('M d, Y H:i')
                        : \$value->format('M d, Y H:i');
                } catch (\Exception \$e) {
                    echo \$value;
                }
            ?>";
        });
    }
}
