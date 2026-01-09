<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{

    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Application::class);

        $applications = $this->applicationService->list($request->user());

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully'
        );
    }

    public function store(StoreApplicationRequest $request)
    {
        $this->authorize('create', Application::class);

        $application = $this->applicationService->apply(
            array_merge($request->validated(), ['cv_file' => $request->file('cv_file')]),
            $request->user()
        );

        return $this->successResponse(
            new ApplicationResource($application),
            'Application submitted successfully',
            201
        );
    }

    public function show(Application $application)
    {
        $this->authorize('view', $application);

        return $this->successResponse(
            new ApplicationResource($application->load(['user', 'vacancy'])),
            'Application details retrieved successfully'
        );
    }

    public function updateCv(UpdateApplicationRequest $request, Application $application)
    {
        $this->authorize('update', $application);

        $updatedApplication = $this->applicationService->updateCv(
            $application,
            $request->file('cv_file')
        );

        return $this->successResponse(
            new ApplicationResource($updatedApplication),
            'Application CV updated successfully'
        );
    }

    public function destroy(Application $application)
    {
        $this->authorize('delete', $application);

        if ($this->applicationService->delete($application)) {
            return $this->successResponse(
                null,
                'Application deleted successfully'
            );
        }

        return $this->errorResponse('Failed to delete application');
    }
}
