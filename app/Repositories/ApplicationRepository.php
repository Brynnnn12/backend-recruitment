<?php

namespace App\Repositories;

use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ApplicationRepository
{

    public function __construct(protected Application $application) {}

    protected function baseQuery(): Builder
    {
        return $this->application->with(['user', 'vacancy']);
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
        return $this->application->create($data);
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
            ->byUser($userId)
            ->latest()
            ->paginate($perPage);
    }

    public function existsForUserAndVacancy(int $userId, int $vacancyId): bool
    {
        return $this->application->where('user_id', $userId)
            ->where('vacancy_id', $vacancyId)
            ->exists();
    }


    /**
     * Menghitung jumlah aplikasi aktif oleh user tertentu.
     * Aplikasi aktif adalah aplikasi yang statusnya bukan 'rejected'.
     */
    public function countActiveByUser(int $userId): int
    {
        return $this->application->where('user_id', $userId)
            ->where('status', '!=', 'rejected')
            ->count();
    }
}
