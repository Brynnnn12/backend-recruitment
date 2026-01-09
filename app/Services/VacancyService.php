<?php

namespace App\Services;

use App\Models\Vacancy;
use App\Repositories\VacancyRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class VacancyService
{
    public function __construct(protected VacancyRepository $vacancyRepository) {}

    public function getAllVacancies(): LengthAwarePaginator
    {
        try {
            return $this->vacancyRepository->getAll();
        } catch (\Exception $e) {
            Log::error('Failed to get all vacancies', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getVacancyById(int $id): ?Vacancy
    {
        try {
            return $this->vacancyRepository->findById($id);
        } catch (\Exception $e) {
            Log::error('Failed to get vacancy by ID', [
                'vacancy_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function createVacancy(array $data): Vacancy
    {
        try {
            $vacancy = $this->vacancyRepository->create($data);

            Log::info('Vacancy created successfully', [
                'vacancy_id' => $vacancy->id,
                'created_by' => $data['created_by'] ?? null,
            ]);

            return $vacancy;
        } catch (\Exception $e) {
            Log::error('Failed to create vacancy', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function updateVacancy(Vacancy $vacancy, array $data): bool
    {
        try {
            $updated = $this->vacancyRepository->update($vacancy, $data);

            if ($updated) {
                Log::info('Vacancy updated successfully', [
                    'vacancy_id' => $vacancy->id,
                ]);
            }

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to update vacancy', [
                'vacancy_id' => $vacancy->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function deleteVacancy(Vacancy $vacancy): bool
    {
        try {
            $deleted = $this->vacancyRepository->delete($vacancy);

            if ($deleted) {
                Log::info('Vacancy deleted successfully', [
                    'vacancy_id' => $vacancy->id,
                ]);
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete vacancy', [
                'vacancy_id' => $vacancy->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getUserVacancies(int $userId): Collection
    {
        try {
            return $this->vacancyRepository->getByCreator($userId);
        } catch (\Exception $e) {
            Log::error('Failed to get user vacancies', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getOpenVacancies(): LengthAwarePaginator
    {
        try {
            return $this->vacancyRepository->getOpenVacancies();
        } catch (\Exception $e) {
            Log::error('Failed to get open vacancies', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
