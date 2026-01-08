<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Traits\ApiResponse;
use App\Enums\ApplicationStatus;
use App\Services\ApplicationService;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\ApplicationResource;

class UpdateStatusController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    public function __invoke(UpdateStatusRequest $request, Application $application)
    {
        // Authorize status update (only admin/hr)
        $this->authorize('updateStatus', $application);

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
}
