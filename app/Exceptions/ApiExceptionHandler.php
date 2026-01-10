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
        // 1. Error 401 (Unauthenticated / Belum Login)
        if ($e instanceof AuthenticationException) {
            return $this->errorResponse(
                'Anda belum login atau sesi Anda telah berakhir. Silakan login terlebih dahulu.',
                401
            );
        }

        // 2. Error 403 (Access Denied / Salah Role)
        if ($e instanceof AccessDeniedHttpException) {
            return $this->errorResponse(
                'Akses ditolak. Anda tidak memiliki izin untuk melakukan aksi ini.',
                403
            );
        }

        // 3. Error 404 (Not Found)
        if ($e instanceof NotFoundHttpException) {
            return $this->errorResponse(
                'Resource yang Anda cari tidak ditemukan. Periksa kembali URL atau data yang diminta.',
                404
            );
        }

        // 4. Error 422 (Validasi)
        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                'Data yang Anda kirimkan tidak valid. Periksa kembali input Anda.',
                422,
                $e->errors()
            );
        }

        // 5. General Error (500)
        $message = app()->isLocal()
            ? $e->getMessage()
            : 'Terjadi kesalahan pada server. Silakan coba beberapa saat lagi atau hubungi administrator.';

        return $this->errorResponse($message, 500);
    }
}
