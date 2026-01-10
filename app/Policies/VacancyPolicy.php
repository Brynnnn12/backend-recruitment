<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    /**
     * Admin has full access to all vacancy operations.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * HR can view vacancies list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('hr');
    }

    /**
     * HR can view any vacancy.
     */
    public function view(User $user, Vacancy $vacancy): bool
    {
        return $user->hasRole('hr');
    }

    /**
     * HR can create vacancies.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('hr');
    }

    /**
     * HR can update vacancies.
     */
    public function update(User $user, Vacancy $vacancy): bool
    {
        return $user->hasRole('hr');
    }

    /**
     * HR can delete vacancies.
     */
    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $user->hasRole('hr');
    }
}
