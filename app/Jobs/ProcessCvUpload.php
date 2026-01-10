<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\FileUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to delete old CV file asynchronously
 *
 * This job only handles old file deletion after CV update.
 * The DB update is done synchronously before dispatching this job.
 */
class ProcessCvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param Application $application
     * @param string $newCvPath Path to the newly uploaded CV file
     * @param string $oldCvPath Path to the old CV file to delete
     */
    public function __construct(
        public Application $application,
        public string $newCvPath,
        public string $oldCvPath
    ) {
        // Dispatch after database transaction committed
        $this->afterCommit();
    }

    /**
     * Execute the job.
     *
     * @param FileUploadService $fileService
     * @return void
     */
    public function handle(FileUploadService $fileService): void
    {
        try {
            $fileService->delete($this->oldCvPath);
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
        // Optional: Notify user about failed upload
        // You could dispatch another notification here
    }
}
