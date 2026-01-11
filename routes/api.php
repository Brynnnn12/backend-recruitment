<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UpdateStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::apiResource('vacancies', VacancyController::class);
    Route::apiResource('applications', ApplicationController::class);
    Route::post('applications/{application}/update-cv', [ApplicationController::class, 'updateCv']);
    Route::put('applications/{application}/status', UpdateStatusController::class);
    Route::apiResource('employees', EmployeeController::class)->parameter('employees', 'employee');
});
