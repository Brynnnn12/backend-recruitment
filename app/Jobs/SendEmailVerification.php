<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mengirim email verifikasi secara asynchronous
 * 
 * Job ini akan diproses oleh queue worker, sehingga tidak memblokir
 * response registrasi user
 */
class SendEmailVerification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     * Menggunakan exponential backoff: 10, 20, 40, 80 detik
     *
     * @var array
     */
    public $backoff = [10, 20, 40, 80];

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {
        // Delay 10 detik untuk menghindari rate limiting Mailtrap
        $this->delay(now()->addSeconds(10));

        // Dispatch after database transaction committed
        $this->afterCommit();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing email verification job', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);

        // Kirim email verifikasi jika belum terverifikasi
        if (!$this->user->hasVerifiedEmail()) {
            $this->user->sendEmailVerificationNotification();

            Log::info('Email verification sent', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send email verification', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
