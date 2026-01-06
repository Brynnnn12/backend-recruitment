<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, string $message = 'Success', int $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        // Jika data adalah API Resource (Single atau Collection)
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            // Ubah resource menjadi array response standar Laravel
            // resolve() akan otomatis menangani pagination (meta & links) jika ada
            $resourceData = $data->response()->getData(true);

            // --- CLEAN CODE TRICK ---
            // Hapus bagian 'links' di dalam 'meta' yang berisi HTML aneh (&laquo;)
            // Karena Frontend Next.js biasanya generate UI pagination sendiri
            if (isset($resourceData['meta']['links'])) {
                unset($resourceData['meta']['links']);
            }
            // ------------------------

            // Gabungkan hasil resource ke response kita
            // Jika ada pagination, 'data', 'meta', 'links' akan otomatis masuk
            $response = array_merge($response, $resourceData);
        }
        // Jika data adalah Paginator murni (tanpa Resource)
        elseif ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
            // Kita tidak menyertakan 'links' HTML di sini
        }
        // Jika data biasa (Array/Object)
        else {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse(string $message = 'Error', int $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        // Hanya include 'errors' jika ada (untuk validation)
        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
