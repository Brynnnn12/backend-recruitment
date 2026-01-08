<?php

namespace App\Providers;

use App\Events\ApplicationStatusChanged;
use App\Listeners\SendApplicationStatusNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider
 * 
 * Register all event-listener mappings for the application.
 * This tells Laravel which listeners should be triggered when specific events are fired.
 * 
 * Flow:
 * 1. Event fired → Laravel checks this mapping
 * 2. Find corresponding listeners
 * 3. Execute listener's handle() method
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * Structure:
     * Event::class => [
     *     Listener1::class,
     *     Listener2::class, // Multiple listeners per event allowed
     * ]
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ApplicationStatusChanged::class => [
            SendApplicationStatusNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     * 
     * Auto-discovery scans Listeners directory for classes.
     * Set to false for explicit control and better performance.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
