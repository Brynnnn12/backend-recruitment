<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\Request;

/**
 * @group Lamaran
 *
 * API untuk mengelola lamaran pekerjaan. Pengguna dapat melamar ke lowongan dan melihat status lamaran mereka.
 * Admin dan HR dapat melihat semua lamaran dan mengubah statusnya.
 */
class ApplicationController extends Controller
{

    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    /**
     * Tampilkan daftar lamaran
     * 
     * Endpoint ini mengembalikan daftar lamaran berdasarkan peran pengguna:
     * - User: hanya melihat lamaran mereka sendiri
     * - Admin/HR: melihat semua lamaran
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Lamaran berhasil diambil",
     *  "data": [
     *    {
     *      "id": 1,
     *      "vacancy": {
     *        "id": 1,
     *        "title": "Senior Backend Developer"
     *      },
     *      "status": "pending",
     *      "cv_path": "storage/cvs/example.pdf",
     *      "applied_at": "2026-01-09T10:00:00.000000Z"
     *    }
     *  ]
     * }
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Application::class);

        $applications = $this->applicationService->list($request->user());

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully'
        );
    }

    /**
     * Buat lamaran baru
     * 
     * Endpoint ini digunakan untuk melamar ke lowongan pekerjaan.
     * File CV akan diupload dan diproses secara asynchronous.
     * 
     * @bodyParam vacancy_id integer required ID lowongan yang dilamar. Example: 1
     * @bodyParam cv_file file required File CV dalam format PDF (maksimal 2MB).
     * 
     * @response 201 {
     *  "status": true,
     *  "message": "Lamaran berhasil dikirim",
     *  "data": {
     *    "id": 1,
     *    "vacancy_id": 1,
     *    "status": "pending",
     *    "cv_path": "storage/cvs/example.pdf"
     *  }
     * }
     */
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

    /**
     * Tampilkan detail lamaran
     * 
     * Endpoint ini mengembalikan detail lengkap dari satu lamaran.
     * User hanya dapat melihat lamaran mereka sendiri.
     * 
     * @urlParam application integer required ID lamaran. Example: 1
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Detail lamaran berhasil diambil",
     *  "data": {
     *    "id": 1,
     *    "user": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com"
     *    },
     *    "vacancy": {
     *      "id": 1,
     *      "title": "Senior Backend Developer"
     *    },
     *    "status": "pending",
     *    "cv_path": "storage/cvs/example.pdf"
     *  }
     * }
     */
    public function show(Application $application)
    {
        $this->authorize('view', $application);

        return $this->successResponse(
            new ApplicationResource($application->load(['user', 'vacancy'])),
            'Application details retrieved successfully'
        );
    }

    /**
     * Perbarui CV lamaran
     * 
     * Endpoint ini digunakan untuk memperbarui file CV dari lamaran yang sudah ada.
     * User hanya dapat memperbarui lamaran mereka sendiri.
     * 
     * @urlParam application integer required ID lamaran. Example: 1
     * @bodyParam cv_file file required File CV baru dalam format PDF (maksimal 2MB).
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "CV lamaran berhasil diperbarui",
     *  "data": {
     *    "id": 1,
     *    "cv_path": "storage/cvs/example_updated.pdf"
     *  }
     * }
     */
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

    /**
     * Hapus lamaran
     * 
     * Endpoint ini digunakan untuk menghapus lamaran.
     * User hanya dapat menghapus lamaran mereka sendiri.
     * 
     * @urlParam application integer required ID lamaran. Example: 1
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Lamaran berhasil dihapus",
     *  "data": null
     * }
     */
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
