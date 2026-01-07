<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * INI WAJIB ADA.
     * Method ini jalan duluan sebelum method lain (view, create, dll).
     * Jika admin, langsung lolos (return true).
     */
    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    // ... method viewAny, view, dll biarkan seperti logic bisnis biasa
    public function viewAny(User $user): bool
    {
        // Admin sudah lolos di atas, jadi di sini cukup cek HR & User
        return $user->hasRole(['hr', 'user']);
    }

    /**
     * View specific application
     * - Admin & HR: semua
     * - User: hanya miliknya
     */
    public function view(User $user, Application $application): bool
    {
        if ($user->hasAnyRole(['admin', 'hr'])) {
            return true;
        }

        return $user->id === $application->user_id;
    }

    /**
     * Create application (apply job)
     * - Hanya user (applicant)
     */
    public function create(User $user): bool
    {
        return $user->hasRole('user');
    }

    /**
     * Update application (status)
     * - Admin & HR
     */
    public function update(User $user, Application $application): bool
    {
        return $user->hasAnyRole(['admin', 'hr']);
    }

    /**
     * Delete application
     * - Admin saja
     */
    public function delete(User $user, Application $application): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Restore (optional)
     */
    public function restore(User $user, Application $application): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Force delete (optional)
     */
    public function forceDelete(User $user, Application $application): bool
    {
        return $user->hasRole('admin');
    }
}
