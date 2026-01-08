<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Services\VacancyService;
use Illuminate\Http\JsonResponse;

class VacancyController extends Controller
{

    public function __construct(protected VacancyService $vacancyService)
    {
        $this->authorizeResource(Vacancy::class, 'vacancy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {

        $vacancies = $this->vacancyService->getAllVacancies();
        return $this->successResponse(VacancyResource::collection($vacancies), 'Vacancies retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status'] = 'open';

        $vacancy = $this->vacancyService->createVacancy($data);

        return $this->successResponse(new VacancyResource($vacancy), 'Vacancy created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vacancy $vacancy): JsonResponse
    {

        return $this->successResponse(new VacancyResource($vacancy), 'Vacancy retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVacancyRequest $request, Vacancy $vacancy): JsonResponse
    {

        $this->vacancyService->updateVacancy($vacancy, $request->validated());

        return $this->successResponse(new VacancyResource($vacancy), 'Vacancy updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vacancy $vacancy): JsonResponse
    {

        $this->vacancyService->deleteVacancy($vacancy);

        return $this->successResponse(null, 'Vacancy deleted successfully');
    }
}
