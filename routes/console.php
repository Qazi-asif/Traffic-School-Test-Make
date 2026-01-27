<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Send pending state transmissions nightly
Schedule::command('transmissions:send-pending --state=FL')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.admin_email'));

// Schedule: Clean up old successful transmissions (optional)
Schedule::command('model:prune', ['--model' => 'App\\Models\\StateTransmission'])
    ->daily()
    ->when(fn () => config('app.env') === 'production');
