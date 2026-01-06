<?php

namespace App\Repositories;

use App\Models\Vacancy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VacancyRepository
{
    public function getAll(): LengthAwarePaginator
    {
        return Vacancy::with('creator')->paginate(10);
    }

    public function findById(int $id): ?Vacancy
    {
        return Vacancy::with('creator')->find($id);
    }

    public function create(array $data): Vacancy
    {
        return Vacancy::create($data);
    }

    public function update(Vacancy $vacancy, array $data): bool
    {
        return $vacancy->update($data);
    }

    public function delete(Vacancy $vacancy): bool
    {
        return $vacancy->delete();
    }

    public function getByCreator(int $userId): Collection
    {
        return Vacancy::where('created_by', $userId)->with('creator')->get();
    }

    public function getOpenVacancies(): LengthAwarePaginator
    {
        return Vacancy::where('status', 'open')->with('creator')->paginate(10);
    }
}
