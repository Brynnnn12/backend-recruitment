<?php

namespace App\Policies;

use App\Models\User;

class EmployeePolicy
{
    /**
     * Admin can do anything with employees.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $employee): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $employee): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, User $employee): bool
    {
        return $user->hasRole('admin');
    }
}
