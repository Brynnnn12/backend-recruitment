<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use App\Policies\ApplicationPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\VacancyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Application::class => ApplicationPolicy::class,
        Vacancy::class => VacancyPolicy::class,
        User::class => EmployeePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
