<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Traits\ApiResponse;
use App\Services\ApplicationService;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\ApplicationResource;

/**
 * @group Lamaran
 */
class UpdateStatusController extends Controller
{

    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    /**
     * Perbarui status lamaran
     * 
     * Endpoint ini digunakan untuk mengubah status lamaran pekerjaan.
     * Hanya dapat diakses oleh Admin dan HR.
     * Status yang tersedia: pending, reviewed, shortlisted, interview, rejected, accepted
     * 
     * @urlParam application integer required ID lamaran. Example: 1
     * @bodyParam status string required Status baru lamaran. Example: reviewed
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Status lamaran berhasil diperbarui",
     *  "data": {
     *    "id": 1,
     *    "status": "reviewed",
     *    "updated_at": "2026-01-09T10:00:00.000000Z"
     *  }
     * }
     */
    public function __invoke(UpdateStatusRequest $request, Application $application)
    {
        $this->authorize('updateStatus', $application);

        $updated = $this->applicationService->updateStatus(
            $application,
            $request->status
        );

        if ($updated) {
            return $this->successResponse(
                new ApplicationResource($application->fresh()),
                'Lamaran Berhasil Diupdate'
            );
        }

        return $this->errorResponse('Gagal Update Status');
    }
}
