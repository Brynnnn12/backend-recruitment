<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendEmailVerification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Auth\StoreUserRequest;

/**
 * @group Autentikasi
 *
 * API untuk registrasi pengguna baru.
 */
class RegisteredUserController extends Controller
{
    use ApiResponse;

    /**
     * Registrasi pengguna baru
     * 
     * Endpoint ini digunakan untuk mendaftarkan pengguna baru ke sistem.
     * Setelah registrasi berhasil, token autentikasi akan dikembalikan.
     * Email verifikasi akan dikirim secara otomatis.
     * 
     * @bodyParam name string required Nama lengkap pengguna. Example: John Doe
     * @bodyParam email string required Alamat email pengguna. Example: john@example.com
     * @bodyParam password string required Password minimal 8 karakter. Example: password123
     * @bodyParam password_confirmation string required Konfirmasi password. Example: password123
     * 
     * @response 201 {
     *  "status": true,
     *  "message": "Pengguna berhasil didaftarkan",
     *  "data": {
     *    "token": "1|abcdefghijklmnopqrstuvwxyz"
     *  }
     * }
     * 
     * @unauthenticated
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->string('password')),
            ]);

            $user->assignRole('user');

            Auth::login($user);

            return $user;
        });


        try {
            SendEmailVerification::dispatch($user);
        } catch (\Exception $e) {
            // Log error tapi tetap lanjutkan registrasi
            Log::error('Failed to dispatch email verification job', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Tetap fire event untuk kebutuhan lain (logging, analytics, dll)
        event(new Registered($user));

        $token = $user->createToken('API Token')->plainTextToken;

        return $this->successResponse(['token' => $token], 'User registered successfully', 201);
    }
}
