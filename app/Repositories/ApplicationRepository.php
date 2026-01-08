<?php

namespace App\Repositories;

use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ApplicationRepository
{
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
            ->byUser($userId)
            ->latest()
            ->paginate($perPage);
    }

    public function existsForUserAndVacancy(int $userId, int $vacancyId): bool
    {
        return Application::where('user_id', $userId)
            ->where('vacancy_id', $vacancyId)
            ->exists();
    }
}
