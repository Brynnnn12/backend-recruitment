<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Pengguna
 *
 * API untuk mengelola data pengguna yang sedang login.
 */
class UserController extends Controller
{

    /**
     * Tampilkan data pengguna yang sedang login
     * 
     * Endpoint ini mengembalikan informasi lengkap pengguna yang sedang login.
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Data pengguna berhasil diambil",
     *  "data": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "email_verified_at": "2026-01-09T10:00:00.000000Z",
     *    "roles": ["user"]
     *  }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'User Berhasil Diambil');
    }
}
