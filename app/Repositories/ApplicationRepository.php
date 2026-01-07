<?php

namespace App\Repositories;

use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ApplicationRepository
{
    // Mengembalikan Builder agar reusable jika ingin chain query lain
    protected function baseQuery(): Builder
    {
        return Application::with(['user', 'vacancy']);
    }

    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): Application
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $data): Application
    {
        return Application::create($data);
    }

    public function update(Application $application, array $data): bool
    {
        return $application->update($data);
    }

    public function delete(Application $application): bool
    {
        return $application->delete();
    }

    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->byUser($userId) // Pastikan Scope scopeByUser ada di Model
            ->latest()
            ->paginate($perPage);
    }

    public function existsForUserAndVacancy(int $userId, int $vacancyId): bool
    {
        // Tidak perlu load relation (baseQuery) untuk cek exists, biar ringan
        return Application::where('user_id', $userId)
            ->where('vacancy_id', $vacancyId)
            ->exists();
    }
}
