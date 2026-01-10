<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Services\VacancyService;
use Illuminate\Http\JsonResponse;

/**
 * @group Lowongan
 *
 * API untuk mengelola lowongan pekerjaan. Admin dan HR dapat membuat, mengubah, dan menghapus lowongan.
 * Semua pengguna dapat melihat daftar lowongan yang tersedia.
 */
class VacancyController extends Controller
{

    public function __construct(protected VacancyService $vacancyService)
    {
        $this->authorizeResource(Vacancy::class, 'vacancy');
    }

    /**
     * Tampilkan daftar semua lowongan
     * 
     * Endpoint ini mengembalikan daftar semua lowongan pekerjaan yang tersedia.
     * Dapat diakses oleh semua pengguna yang terautentikasi.
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Lowongan berhasil diambil",
     *  "data": [
     *    {
     *      "id": 1,
     *      "title": "Senior Backend Developer",
     *      "description": "Kami mencari backend developer berpengalaman...",
     *      "requirements": "3+ tahun pengalaman dengan PHP/Laravel",
     *      "status": "open",
     *      "created_by": {
     *        "id": 1,
     *        "name": "Admin HR"
     *      }
     *    }
     *  ]
     * }
     */
    public function index(): JsonResponse
    {

        $vacancies = $this->vacancyService->getAllVacancies();
        return $this->successResponse(VacancyResource::collection($vacancies), 'Berhasil Mengambil Lowongan');
    }

    /**
     * Buat lowongan pekerjaan baru
     * 
     * Endpoint ini digunakan untuk membuat lowongan pekerjaan baru.
     * Hanya dapat diakses oleh Admin dan HR.
     * 
     * @bodyParam title string required Judul lowongan. Example: Senior Backend Developer
     * @bodyParam description string required Deskripsi lowongan. Example: Kami mencari backend developer yang berpengalaman...
     * @bodyParam requirements string required Persyaratan lowongan. Example: Minimal 3 tahun pengalaman dengan PHP/Laravel
     * 
     * @response 201 {
     *  "status": true,
     *  "message": "Lowongan berhasil dibuat",
     *  "data": {
     *    "id": 1,
     * Tampilkan detail lowongan
     * 
     * Endpoint ini mengembalikan detail lengkap dari satu lowongan pekerjaan.
     * 
     * @urlParam vacancy integer required ID lowongan. Example: 1
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Lowongan berhasil diambil",
     *  "data": {
     *    "id": 1,
     *    "title": "Senior Backend Developer",
     *    "description": "Kami mencari backend developer berpengalaman...",
     *    "requirements": "3+ tahun pengalaman dengan PHP/Laravel",
     *    "status": "open"
     *  }
     * }
     */
    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status'] = 'open';

        $vacancy = $this->vacancyService->createVacancy($data);

        return $this->successResponse(new VacancyResource($vacancy), 'Lowongan Berhasil Dibuat', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vacancy $vacancy): JsonResponse
    {

        return $this->successResponse(new VacancyResource($vacancy), 'Berhasil Mengambil Detail Lowongan');
    }

    /**
     * Perbarui lowongan pekerjaan
     * 
     * Endpoint ini digunakan untuk memperbarui data lowongan pekerjaan.
     * Hanya dapat diakses oleh Admin dan HR.
     * 
     * @urlParam vacancy integer required ID lowongan. Example: 1
     * @bodyParam title string Judul lowongan. Example: Senior Backend Developer
     * @bodyParam description string Deskripsi lowongan. Example: Kami mencari backend developer yang berpengalaman...
     * @bodyParam requirements string Persyaratan lowongan. Example: Minimal 3 tahun pengalaman dengan PHP/Laravel
     * @bodyParam status string Status lowongan. Example: open
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Lowongan berhasil diperbarui",
     *  "data": {
     *    "id": 1,
     *    "title": "Senior Backend Developer",
     *    "status": "open"
     *  }
     * }
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
