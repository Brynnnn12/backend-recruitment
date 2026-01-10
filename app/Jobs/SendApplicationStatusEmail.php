<?php

namespace App\Jobs;

use App\Models\Application;
use App\Mail\ApplicationStatusMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Job to send application status email notification
 * 
 * This job is queued and processed asynchronously to avoid blocking
 * the main application flow. It includes retry mechanism and error handling.
 * 
 * Flow:
 * 1. Event fired → Listener catches → Dispatch this job
 * 2. Queue worker picks up job
 * 3. Email sent via Mail facade
 * 4. If failed, retry up to 2 times
 */
class SendApplicationStatusEmail implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 60;

    /**
     * The unique ID of the job.
     * Prevents duplicate jobs for the same application status change.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'send-email-' . $this->application->id . '-' . $this->application->status->value;
    }

    /**
     * Create a new job instance.
     *
     * @param Application $application
     */
    public function __construct(
        public Application $application
    ) {
        // Load relationships to avoid N+1 queries
        $this->application->load(['user', 'vacancy']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Mail::to($this->application->user->email)
                ->send(new ApplicationStatusMail($this->application));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Application status email job failed after all retries', [
            'application_id' => $this->application->id,
            'user_email' => $this->application->user->email,
            'error' => $exception->getMessage(),
            'total_attempts' => $this->tries,
        ]);

        // Optional: Notify admin about failed email
        // You could dispatch another notification here
    }
}
