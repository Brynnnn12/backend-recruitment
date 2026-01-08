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

        // User hanya bisa apply maksimal 2 kali di semua lowongan yang statusnya belum rejected
        // Jika ada aplikasi yang rejected, user bisa apply lagi
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
    }

    public function updateStatus(Application $application, ApplicationStatus $status): bool
    {
        // Capture old status before update
        $oldStatus = $application->status->value;

        // Update status
        $updated = $this->applicationRepository->update($application, [
            'status' => $status
        ]);

        // Fire event if update successful
        if ($updated) {
            $application->refresh(); // Reload to get updated data

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
    }

    public function updateCv(Application $application, UploadedFile $cvFile): bool
    {
        $this->validateCanUpdateCv($application);

        $oldPath = $application->getRawOriginal('cv_file');
        if ($oldPath) {
            $this->fileService->delete($oldPath);
        }

        $newPath = $this->fileService->uploadPdf($cvFile);

        return $this->applicationRepository->update($application, [
            'cv_file' => $newPath
        ]);
    }

    public function delete(Application $application): bool
    {
        $path = $application->getRawOriginal('cv_file');

        if ($path) {
            $this->fileService->delete($path);
        }

        return $this->applicationRepository->delete($application);
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
