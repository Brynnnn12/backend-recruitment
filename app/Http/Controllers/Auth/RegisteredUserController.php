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

class RegisteredUserController extends Controller
{
    use ApiResponse;

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
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

        // Dispatch job untuk email verifikasi secara asynchronous
        // Gunakan try-catch untuk memastikan error email tidak mempengaruhi response
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
