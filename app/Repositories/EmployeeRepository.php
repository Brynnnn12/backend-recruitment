<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;



class EmployeeRepository
{


    public function __construct(protected User $model) {}

    public function getAll(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->role('hr')
            ->with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(max($perPage, 1));
    }

    public function findById(int $id): ?User
    {
        return $this->model->role('hr')->with('roles')->find($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $employee, array $data): bool
    {
        return $employee->update($data);
    }

    public function delete(User $employee): bool
    {
        return $employee->delete();
    }
}
