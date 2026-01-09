<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Jobs\SendApplicationStatusEmail;
use App\Enums\ApplicationStatus;
use Illuminate\Support\Facades\Log;

/**
 * Listener for ApplicationStatusChanged event
 * 
 * This listener is triggered when an application status changes.
 * It dispatches an email job ONLY when the status changes to HIRED or REJECTED.
 * 
 * Why only HIRED/REJECTED?
 * - These are final statuses that require user notification
 * - APPLIED, REVIEWED, INTERVIEW are internal process statuses
 */
class SendApplicationStatusNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ApplicationStatusChanged $event
     * @return void
     */
    public function handle(ApplicationStatusChanged $event): void
    {
        // Only send email for final statuses
        if ($this->shouldSendEmail($event->newStatus)) {
            Log::info('Dispatching email job for application status change', [
                'application_id' => $event->application->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);

            //ini mengirim job ke queue
            SendApplicationStatusEmail::dispatch($event->application);
        }
    }

    /**
     * Determine if email should be sent for this status
     *
     * @param string $status
     * @return bool
     */
    private function shouldSendEmail(string $status): bool
    {
        return in_array($status, [
            ApplicationStatus::HIRED->value,
            ApplicationStatus::REJECTED->value,
        ]);
    }
}
