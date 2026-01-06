<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * Get the authenticated user.
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'User retrieved successfully');
    }
}
