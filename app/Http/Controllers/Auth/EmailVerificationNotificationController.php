<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    use ApiResponse;

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        /** @var \App\Models\User&\Illuminate\Contracts\Auth\MustVerifyEmail $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification link sent to your email');
    }
}
