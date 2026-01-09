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

        Log::info('Starting auto-reject job', [
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'stale_days' => $this->staleDays,
        ]);

        // Find stale applications
        $staleApplications = Application::where('status', ApplicationStatus::APPLIED)
            ->where('applied_at', '<=', $cutoffDate)
            ->with(['user', 'vacancy']) // Eager load for event
            ->get();

        if ($staleApplications->isEmpty()) {
            Log::info('No stale applications found');
            return;
        }

        $rejectedCount = 0;
        $failedCount = 0;

        foreach ($staleApplications as $application) {
            try {
                $oldStatus = $application->status->value;

                // Update status
                $application->update([
                    'status' => ApplicationStatus::REJECTED,
                ]);

                // Fire event (will trigger email notification)
                event(new ApplicationStatusChanged(
                    $application->fresh(),
                    $oldStatus,
                    ApplicationStatus::REJECTED->value
                ));

                $rejectedCount++;

                Log::info('Aplikasi otomatis ditolak', [
                    'application_id' => $application->id,
                    'user_id' => $application->user_id,
                    'vacancy_id' => $application->vacancy_id,
                    'applied_at' => $application->applied_at->toDateTimeString(),
                    'days_old' => $application->applied_at->diffInDays(Carbon::now()),
                ]);
            } catch (\Exception $e) {
                $failedCount++;

                Log::error('Gagal otomatis menolak aplikasi', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Otomatis menolak aplikasi selesai', [
            'total_found' => $staleApplications->count(),
            'successfully_rejected' => $rejectedCount,
            'failed' => $failedCount,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Otomatis menolak aplikasi gagal total', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optional: Notify admin about critical failure
    }
}
