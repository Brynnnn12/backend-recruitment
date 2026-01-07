<?php

namespace App\Services;

use App\Models\User;
use App\Models\Application;
use App\Enums\ApplicationStatus;
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
        // 1. Validasi Bisnis: Cek Duplikat
        // Gunakan $user->id (dari parameter), JANGAN Auth::id()
        if ($this->applicationRepository->existsForUserAndVacancy($user->id, $data['vacancy_id'])) {
            throw ValidationException::withMessages([
                'vacancy_id' => ['You have already applied for this vacancy.'],
            ]);
        }

        // 2. Handle Upload
        if (isset($data['cv_file']) && $data['cv_file'] instanceof UploadedFile) {
            $data['cv_file'] = $this->fileService->uploadPdf($data['cv_file']);
        }

        // 3. Prepare Data
        // Menggabungkan array data dengan data override
        $applicationData = array_merge($data, [
            'user_id'    => $user->id, // Konsisten pakai $user object
            'status'     => ApplicationStatus::APPLIED, // Pastikan Enum ini benar (APPLIED/PENDING?)
            'applied_at' => now(),
        ]);

        return $this->applicationRepository->create($applicationData);
    }

    public function updateStatus(Application $application, ApplicationStatus $status): bool
    {
        // Clean: Langsung pass ke repo
        return $this->applicationRepository->update($application, [
            'status' => $status
        ]);
    }

    public function delete(Application $application): bool
    {
        // 1. AMBIL PATH ASLI
        // getRawOriginal('cv_file') mengambil string "cv/namafile.pdf" langsung dari kolom DB.
        // Ini membypass Accessor yang mengubahnya jadi "http://localhost..."
        $path = $application->getRawOriginal('cv_file');

        if ($path) {
            // 2. HAPUS FILE
            // Karena $path sudah murni path (bukan URL), kita tidak perlu logic str_replace lagi.
            // Langsung kirim ke FileUploadService.
            $this->fileService->delete($path);
        }

        return $this->applicationRepository->delete($application);
    }
}
