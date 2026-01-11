<?php

namespace App\Services;

use App\Models\User;
use App\Models\Application;
use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusChanged;
use Illuminate\Http\UploadedFile;
use App\Repositories\ApplicationRepository;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(
        protected ApplicationRepository $applicationRepository,
        protected FileUploadService $fileService
    ) {}

    public function list(User $user, array $filters = [])
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        if ($user->hasRole(['admin', 'hr'])) {
            return $this->applicationRepository->getAll($search, $status, $perPage);
        }

        return $this->applicationRepository->getByUser($user->id, $search, $status, $perPage);
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
            throw $e;
        }
    }

    public function updateStatus(Application $application, ApplicationStatus $status): bool
    {
        try {
            $oldStatus = $application->status->value;


            if (in_array($application->status, [ApplicationStatus::REJECTED, ApplicationStatus::HIRED])) {
                throw ValidationException::withMessages([
                    'status' => ['Status aplikasi sudah final (' . $application->status->value . ') dan tidak dapat diubah lagi.'],
                ]);
            }


            if ($oldStatus === $status->value) {
                return true;
            }

            $updated = $this->applicationRepository->update($application, [
                'status' => $status
            ]);

            if ($updated) {
                $application->refresh();

                event(new ApplicationStatusChanged(
                    $application,
                    $oldStatus,
                    $status->value
                ));
            }

            return $updated;
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function updateCv(Application $application, UploadedFile $cvFile): Application
    {
        try {
            $this->validateCanUpdateCv($application);


            $newCvPath = $this->fileService->uploadPdf($cvFile, 'cv');
            $oldCvPath = $application->getRawOriginal('cv_file');


            $this->applicationRepository->update($application, [
                'cv_file' => $newCvPath
            ]);

            $application->refresh();


            if ($oldCvPath) {
                \App\Jobs\ProcessCvUpload::dispatch($application, $newCvPath, $oldCvPath);
            }


            return $application;
        } catch (\Exception $e) {

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

            return $deleted;
        } catch (\Exception $e) {
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
