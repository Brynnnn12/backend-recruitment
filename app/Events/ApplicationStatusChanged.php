<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when application status changes
 * 
 * This event is triggered whenever an application's status is updated.
 * It carries the application instance along with old and new status values
 * for listeners to determine what actions to take.
 */
class ApplicationStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Application $application The application whose status changed
     * @param string $oldStatus Previous status value
     * @param string $newStatus New status value
     */
    public function __construct(
        public Application $application,
        public string $oldStatus,
        public string $newStatus
    ) {}
}
