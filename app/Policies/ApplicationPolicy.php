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
     * KECUALI untuk create() - admin tidak boleh apply job
     */
    public function before($user, $ability)
    {
        if ($user->hasRole('admin') && $ability !== 'create') {
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
        if ($user->hasRole('hr')) {
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
     * Update application CV
     * - User: hanya CV miliknya sendiri
     * - Admin & HR: tidak bisa update CV (hanya user yang bisa)
     */
    public function update(User $user, Application $application): bool
    {
        // Hanya user pemilik application yang bisa update CV
        return $user->id === $application->user_id;
    }

    /**
     * Update status application
     * - Admin & HR: bisa update status
     * - User: tidak bisa update status
     */
    public function updateStatus(User $user, Application $application): bool
    {
        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Delete application
     * - Admin saja
     */
    public function delete(User $user, Application $application): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        //user hanya bisa menghapus aplikasinya jika statusnya masih 'applied'
        if ($user->hasRole('user')) {
            return $user->id === $application->user_id
                && $application->status->value === 'applied';
        }
        return false;
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
