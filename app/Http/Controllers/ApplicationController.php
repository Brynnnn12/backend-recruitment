<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Services\ApplicationService;
use App\Traits\ApiResponse; // Asumsi kamu punya ini
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ApplicationService $applicationService
    ) {
        // 1. BEST PRACTICE: Aktifkan ini!
        // Ini otomatis menjalankan policy: viewAny, view, create, update, delete
        // Syarat: Parameter route harus bernama {application}
        $this->authorizeResource(Application::class, 'application');
    }

    public function index(Request $request)
    {
        // $request->user() lebih disarankan daripada Auth::user() untuk testing
        $applications = $this->applicationService->list($request->user());

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully'
        );
    }

    public function store(StoreApplicationRequest $request)
    {
        // Kirim data yang sudah divalidasi + user object
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
        // Load relationship di sini (Eager Loading)
        return $this->successResponse(
            new ApplicationResource($application->load(['user', 'vacancy'])),
            'Application details retrieved successfully'
        );
    }

    public function update(UpdateApplicationRequest $request, Application $application)
    {
        // Tidak perlu $this->authorize('update'), sudah dihandle constructor

        $updated = $this->applicationService->updateStatus(
            $application,
            ApplicationStatus::from($request->status)
        );

        if ($updated) {
            return $this->successResponse(
                new ApplicationResource($application->fresh()),
                'Application status updated successfully'
            );
        }

        return $this->errorResponse('Failed to update application status');
    }

    public function destroy(Application $application)
    {
        // Tidak perlu $this->authorize('delete'), sudah dihandle constructor

        if ($this->applicationService->delete($application)) {
            return $this->successResponse(
                null,
                'Application deleted successfully'
            );
        }

        return $this->errorResponse('Failed to delete application');
    }
}
