<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'vacancy_id',
        'cv_file',
        'status',
        'applied_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ApplicationStatus::class,
        'applied_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------- */
    /* Relationships                                                              */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the user that owns the application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vacancy that the application is for.
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    /* -------------------------------------------------------------------------- */
    /* Accessors                                                                  */
    /* -------------------------------------------------------------------------- */

    /**
     * Modern Accessor (Laravel 9+).
     * Otomatis mengubah output $application->cv_file menjadi URL lengkap.
     */
    protected function cvFile(): Attribute
    {
        return Attribute::get(function (?string $value) {
            if ($value) {
                return url('storage/' . $value);
            }
            return null;
        });
    }
    /* -------------------------------------------------------------------------- */
    /* Scopes                                                                     */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope untuk filter status.
     * Penggunaan: Application::status(ApplicationStatus::APPLIED)->get();
     */
    public function scopeStatus(Builder $query, ApplicationStatus $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope untuk mengambil lamaran milik user tertentu.
     * Penggunaan: Application::byUser($userId)->get();
     */
    public function scopeByUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope untuk mengambil lamaran pada lowongan tertentu.
     * Penggunaan: Application::forVacancy($vacancyId)->get();
     */
    public function scopeForVacancy(Builder $query, int $vacancyId): void
    {
        $query->where('vacancy_id', $vacancyId);
    }
}
