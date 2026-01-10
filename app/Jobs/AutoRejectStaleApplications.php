<?php

namespace App\Jobs;

use App\Models\Application;
use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusChanged;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to automatically reject stale applications
 * 
 * This job runs on a schedule (daily) to find and reject applications
 * that have been in APPLIED status for more than 7 days without any action.
 * 
 * Business Rule:
 * - Only affects applications with status = APPLIED
 * - Must be older than 7 days (configurable)
 * - Fires ApplicationStatusChanged event (triggers email notification)
 * 
 * Why needed?
 * - Keeps data clean
 * - Provides closure to applicants
 * - Prevents indefinite waiting
 */
class AutoRejectStaleApplications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Number of days before application is considered stale
     *
     * @var int
     */
    private int $staleDays;

    /**
     * Create a new job instance.
     *
     * @param int $staleDays Number of days before auto-reject (default: 7)
     */
    public function __construct(int $staleDays = 7)
    {
        $this->staleDays = $staleDays;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $cutoffDate = Carbon::now()->subDays($this->staleDays);

        $staleApplications = Application::where('status', ApplicationStatus::APPLIED)
            ->where('applied_at', '<=', $cutoffDate)
            ->with(['user', 'vacancy'])
            ->get();

        if ($staleApplications->isEmpty()) {
            return;
        }

        $rejectedCount = 0;
        $failedCount = 0;

        foreach ($staleApplications as $application) {
            try {
                $oldStatus = $application->status->value;

                $application->update([
                    'status' => ApplicationStatus::REJECTED,
                ]);

                event(new ApplicationStatusChanged(
                    $application->fresh(),
                    $oldStatus,
                    ApplicationStatus::REJECTED->value
                ));

                $rejectedCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }
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
        // Optional: Notify admin about critical failure
    }
}
