<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException; // <--- WAJIB IMPORT INI
use Throwable;
use App\Traits\ApiResponse;

class ApiExceptionHandler
{
    use ApiResponse;

    public static function register(Exceptions $exceptions): void
    {
        $handler = new self();

        $exceptions->render(function (Throwable $e, Request $request) use ($handler) {
            if ($request->is('api/*')) {
                return $handler->handleApiException($e);
            }
        });
    }

    public function handleApiException(Throwable $e)
    {
        // 1. Error 401 (Unauthenticated / Belum Login) <--- TAMBAHKAN INI
        if ($e instanceof AuthenticationException) {
            return $this->errorResponse(
                'Unauthenticated.',
                401
            );
        }

        // 2. Error 403 (Access Denied / Salah Role)
        if ($e instanceof AccessDeniedHttpException) {
            return $this->errorResponse(
                'Anda tidak memiliki akses untuk aksi ini.',
                403
            );
        }

        // 3. Error 404 (Not Found)
        if ($e instanceof NotFoundHttpException) {
            return $this->errorResponse(
                'Halaman yang Anda minta tidak ditemukan.',
                404
            );
        }

        // 4. Error 422 (Validasi)
        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                'Validasi gagal.',
                422,
                $e->errors()
            );
        }

        // 5. General Error (500)
        $message = app()->isLocal() ? $e->getMessage() : 'Terjadi kesalahan pada server.';

        return $this->errorResponse($message, 500);
    }
}
