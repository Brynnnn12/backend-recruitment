<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Admin has full access except for creating applications.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin') && $ability !== 'create') {
            return true;
        }

        return null;
    }

    /**
     * Anyone with hr or user role can view applications list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['hr', 'user']);
    }

    /**
     * Users can view their own applications, HR can view all.
     */
    public function view(User $user, Application $application): bool
    {
        return $user->hasRole('hr') || $user->id === $application->user_id;
    }

    /**
     * Only users can create applications.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('user');
    }

    /**
     * Users can update their own CV only if application status is still APPLIED.
     */
    public function update(User $user, Application $application): bool
    {
        return $user->hasRole('user')
            && $user->id === $application->user_id
            && $application->status === ApplicationStatus::APPLIED;
    }

    /**
     * Only admin and HR can update application status.
     */
    public function updateStatus(User $user): bool
    {
        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Admin can delete any application, users can delete their own if still APPLIED.
     */
    public function delete(User $user, Application $application): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('user')
            && $user->id === $application->user_id
            && $application->status === ApplicationStatus::APPLIED;
    }

    /**
     * Only admin can restore applications.
     */
    public function restore(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Only admin can force delete applications.
     */
    public function forceDelete(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
