<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(protected EmployeeService $employeeService)
    {
        $this->authorizeResource(User::class, 'employee');
    }

    public function index(Request $request): JsonResponse
    {
        $employees = $this->employeeService->list([
            'search' => $request->query('search'),
            'per_page' => min(max((int) $request->query('per_page', 10), 1), 100),
        ]);

        return $this->successResponse(
            EmployeeResource::collection($employees),
            'Daftar HR berhasil diambil'
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return $this->successResponse(
            new EmployeeResource($employee),
            'Employee HR berhasil dibuat',
            201
        );
    }

    public function show(User $employee): JsonResponse
    {
        $employee = $this->ensureHr($employee);

        return $this->successResponse(
            new EmployeeResource($employee),
            'Detail HR berhasil diambil'
        );
    }

    public function update(UpdateEmployeeRequest $request, User $employee): JsonResponse
    {
        $employee = $this->ensureHr($employee);

        $this->employeeService->update($employee, $request->validated());

        return $this->successResponse(
            new EmployeeResource($employee),
            'Employee HR berhasil diperbarui'
        );
    }

    public function destroy(User $employee): JsonResponse
    {
        $employee = $this->ensureHr($employee);

        $this->employeeService->delete($employee);

        return $this->successResponse(null, 'Employee HR berhasil dihapus');
    }

    private function ensureHr(User $employee): User
    {
        if (! $employee->hasRole('hr')) {
            abort(404);
        }

        return $employee;
    }
}
