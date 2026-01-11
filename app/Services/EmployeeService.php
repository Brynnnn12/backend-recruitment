<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(protected EmployeeRepository $employeeRepository) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $search = $filters['search'] ?? null;

        return $this->employeeRepository->getAll($search, $perPage);
    }

    public function find(int $id): ?User
    {
        return $this->employeeRepository->findById($id);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $employee = $this->employeeRepository->create($data);

            $employee->syncRoles(['hr']);

            return $employee;
        });
    }

    public function update(User $employee, array $data): bool
    {
        return $this->employeeRepository->update($employee, $data);
    }

    public function delete(User $employee): bool
    {
        return $this->employeeRepository->delete($employee);
    }
}
