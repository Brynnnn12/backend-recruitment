<?php

namespace App\Services;

use App\Models\Vacancy;
use Illuminate\Support\Facades\Auth;
use App\Repositories\VacancyRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VacancyService
{
    protected VacancyRepository $vacancyRepository;

    public function __construct(VacancyRepository $vacancyRepository)
    {
        $this->vacancyRepository = $vacancyRepository;
    }

    public function getAllVacancies(): LengthAwarePaginator
    {
        return $this->vacancyRepository->getAll();
    }

    public function getVacancyById(int $id): ?Vacancy
    {
        return $this->vacancyRepository->findById($id);
    }

    public function createVacancy(array $data): Vacancy
    {
        return $this->vacancyRepository->create($data);
    }

    public function updateVacancy(Vacancy $vacancy, array $data): bool
    {
        return $this->vacancyRepository->update($vacancy, $data);
    }

    public function deleteVacancy(Vacancy $vacancy): bool
    {
        return $this->vacancyRepository->delete($vacancy);
    }

    public function getUserVacancies(int $userId): Collection
    {
        return $this->vacancyRepository->getByCreator($userId);
    }

    public function getOpenVacancies(): LengthAwarePaginator
    {
        return $this->vacancyRepository->getOpenVacancies();
    }

    public function canUserManageVacancy(Vacancy $vacancy, int $userId): bool
    {
        // Only users with admin or hr role can manage vacancies
        return Auth::user()->hasRole(['admin', 'hr']);
    }
}
