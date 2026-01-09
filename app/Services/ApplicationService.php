<?php

namespace App\Services;

use App\Models\User;
use App\Models\Application;
use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusChanged;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Repositories\ApplicationRepository;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(
        protected ApplicationRepository $applicationRepository,
        protected FileUploadService $fileService
    ) {}

    public function list(User $user)
    {
        if ($user->hasRole(['admin', 'hr'])) {
            return $this->applicationRepository->getAll();
        }

        return $this->applicationRepository->getByUser($user->id);
    }

    public function apply(array $data, User $user): Application
    {
        try {
            if ($this->applicationRepository->countActiveByUser($user->id) >= 2) {
                throw ValidationException::withMessages([
                    'user_id' => ['Anda telah mencapai batas maksimal aplikasi aktif (2). Tunggu aplikasi yang ditolak untuk melamar lagi.'],
                ]);
            }

            $this->validateNotDuplicate($user->id, $data['vacancy_id']);

            if (isset($data['cv_file']) && $data['cv_file'] instanceof UploadedFile) {
                $data['cv_file'] = $this->fileService->uploadPdf($data['cv_file']);
            }

            $applicationData = array_merge($data, [
                'user_id'    => $user->id,
                'status'     => ApplicationStatus::APPLIED,
                'applied_at' => now(),
            ]);

            return $this->applicationRepository->create($applicationData);
        } catch (\Exception $e) {
            Log::error('Failed to apply for vacancy', [
                'user_id' => $user->id,
                'vacancy_id' => $data['vacancy_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw to let ApiExceptionHandler handle response
        }
    }

    public function updateStatus(Application $application, ApplicationStatus $status): bool
    {
        try {
            $oldStatus = $application->status->value;

            // Prevent update if status is already final (REJECTED or HIRED)
            if (in_array($application->status, [ApplicationStatus::REJECTED, ApplicationStatus::HIRED])) {
                throw ValidationException::withMessages([
                    'status' => ['Status aplikasi sudah final (' . $application->status->value . ') dan tidak dapat diubah lagi.'],
                ]);
            }

            // Prevent update if status is the same
            if ($oldStatus === $status->value) {
                Log::info('Status unchanged, skipping update', [
                    'application_id' => $application->id,
                    'status' => $status->value,
                ]);
                return true;
            }

            $updated = $this->applicationRepository->update($application, [
                'status' => $status
            ]);

            if ($updated) {
                $application->refresh();

                // Only fire event if status actually changed
                event(new ApplicationStatusChanged(
                    $application,
                    $oldStatus,
                    $status->value
                ));

                Log::info('Application status updated and event fired', [
                    'application_id' => $application->id,
                    'old_status' => $oldStatus,
                    'new_status' => $status->value,
                ]);
            }

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to update application status', [
                'application_id' => $application->id,
                'old_status' => $oldStatus ?? null,
                'new_status' => $status->value ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function updateCv(Application $application, UploadedFile $cvFile): Application
    {
        try {
            $this->validateCanUpdateCv($application);

            // Upload file synchronously
            $newCvPath = $this->fileService->uploadPdf($cvFile, 'cv');
            $oldCvPath = $application->getRawOriginal('cv_file');

            // Update DB synchronously
            $this->applicationRepository->update($application, [
                'cv_file' => $newCvPath
            ]);

            $application->refresh();

            // Dispatch job to delete old file asynchronously (if exists)
            if ($oldCvPath) {
                \App\Jobs\ProcessCvUpload::dispatch($application, $newCvPath, $oldCvPath);
            }

            Log::info('CV uploaded and updated successfully', [
                'application_id' => $application->id,
                'file_name' => $cvFile->getClientOriginalName(),
                'new_path' => $newCvPath,
            ]);

            return $application;
        } catch (\Exception $e) {
            Log::error('Failed to upload and update CV', [
                'application_id' => $application->id,
                'file_name' => $cvFile->getClientOriginalName() ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function delete(Application $application): bool
    {
        try {
            $path = $application->getRawOriginal('cv_file');

            if ($path) {
                $this->fileService->delete($path);
            }

            $deleted = $this->applicationRepository->delete($application);

            if ($deleted) {
                Log::info('Application deleted successfully', [
                    'application_id' => $application->id,
                    'user_id' => $application->user_id,
                ]);
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate user has not applied for this vacancy before.
     */
    private function validateNotDuplicate(int $userId, int $vacancyId): void
    {
        if ($this->applicationRepository->existsForUserAndVacancy($userId, $vacancyId)) {
            throw ValidationException::withMessages([
                'vacancy_id' => ['Anda sudah pernah melamar lowongan ini.'],
            ]);
        }
    }

    /**
     * Validate CV can be updated (only if status is still APPLIED).
     * Note: Policy authorization should be checked before calling this method.
     */
    private function validateCanUpdateCv(Application $application): void
    {
        if ($application->status !== ApplicationStatus::APPLIED) {
            throw ValidationException::withMessages([
                'status' => ['Tidak dapat mengupdate CV setelah aplikasi telah direview. Status saat ini: ' . $application->status->value],
            ]);
        }
    }
}
