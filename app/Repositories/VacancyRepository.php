<?php

namespace App\Repositories;

use App\Models\Vacancy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VacancyRepository
{


    public function __construct(protected Vacancy $vacancy) {}

    public function getAll(): LengthAwarePaginator
    {
        return $this->vacancy->with('creator')->paginate(10);
    }

    public function findById(int $id): ?Vacancy
    {
        return $this->vacancy->with('creator')->find($id);
    }

    public function create(array $data): Vacancy
    {
        return $this->vacancy->create($data);
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
        return $this->vacancy->where('created_by', $userId)->with('creator')->get();
    }

    public function getOpenVacancies(): LengthAwarePaginator
    {
        return $this->vacancy->where('status', 'open')->with('creator')->paginate(10);
    }
}
