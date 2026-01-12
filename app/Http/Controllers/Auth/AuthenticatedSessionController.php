<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Autentikasi
 *
 * API untuk login dan logout pengguna.
 */
class AuthenticatedSessionController extends Controller
{
    use ApiResponse;

    /**
     * Login pengguna
     * 
     * Endpoint ini digunakan untuk login ke sistem.
     * Setelah login berhasil, token autentikasi akan dikembalikan.
     * 
     * @bodyParam email string required Alamat email pengguna. Example: john@example.com
     * @bodyParam password string required Password pengguna. Example: password123
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Login berhasil",
     *  "data": {
     *    "token": "1|abcdefghijklmnopqrstuvwxyz"
     *  }
     * }
     * 
     * @unauthenticated
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        return $this->successResponse(['token' => $token], 'Login successful');
    }

    /**
     * Logout pengguna
     * 
     * Endpoint ini digunakan untuk logout dari sistem.
     * Token autentikasi akan dihapus dan tidak dapat digunakan lagi.
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Logout berhasil",
     *  "data": null
     * }
     */
    public function destroy(Request $request): JsonResponse
    {
        // Delete the current access token if it exists
        if ($token = $request->user()->currentAccessToken()) {
            $token->delete();
        }

        return $this->successResponse(null, 'Logout successful');
    }
}
