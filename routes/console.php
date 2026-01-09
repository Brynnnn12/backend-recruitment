<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AutoRejectStaleApplications;

/**
 * Define the application's command schedule.
 * 
 * Laravel Scheduler provides a fluent API for defining scheduled tasks.
 * In production, add this cron entry to your server:
 * 
 * * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 * 
 * This single cron entry will check and run all scheduled tasks.
 */

// Auto-reject stale applications daily at midnight
Schedule::job(new AutoRejectStaleApplications())
    ->daily()                           // Run once per day
    ->at('00:00')                       // At midnight
    ->timezone('Asia/Jakarta')          // Your timezone
    ->name('auto-reject-stale-apps')    // Job name in logs
    ->withoutOverlapping()              // Prevent concurrent runs
    ->onSuccess(function () {
        Log::info('Auto-reject scheduler executed successfully');
    })
    ->onFailure(function () {
        Log::error('Auto-reject scheduler failed');
    });

// Optional: You can add more scheduled tasks here
// Example: Clean old logs weekly
Schedule::command('logs:clean')->weekly()->sundays()->at('02:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
