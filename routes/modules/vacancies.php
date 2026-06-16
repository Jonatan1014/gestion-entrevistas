<?php

use App\Http\Controllers\Vacancy\VacancyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vacancy Routes
|--------------------------------------------------------------------------
|
| Vacancy CRUD and status management routes.
| view-vacancies: both Admin and Entrevistador can view
| create/edit/delete/close/cancel/reopen: Admin only
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Index — accessible by both Admin and Entrevistador (view-vacancies)
    Route::middleware('permission:view-vacancies')->group(function () {
        Route::get('/vacancies', [VacancyController::class, 'index'])->name('vacancies.index');
    });

    // Create and Store — requires create-vacancies permission
    // IMPORTANT: create must be registered BEFORE {vacancy} to avoid route collision
    Route::middleware('permission:create-vacancies')->group(function () {
        Route::get('/vacancies/create', [VacancyController::class, 'create'])->name('vacancies.create');
        Route::post('/vacancies', [VacancyController::class, 'store'])->name('vacancies.store');
    });

    // Show — accessible by both Admin and Entrevistador (view-vacancies)
    Route::middleware('permission:view-vacancies')->group(function () {
        Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show'])->name('vacancies.show');
    });

    // Edit and Update — requires edit-vacancies permission
    Route::middleware('permission:edit-vacancies')->group(function () {
        Route::get('/vacancies/{vacancy}/edit', [VacancyController::class, 'edit'])->name('vacancies.edit');
        Route::put('/vacancies/{vacancy}', [VacancyController::class, 'update'])->name('vacancies.update');
        Route::post('/vacancies/{vacancy}/close', [VacancyController::class, 'close'])->name('vacancies.close');
        Route::post('/vacancies/{vacancy}/cancel', [VacancyController::class, 'cancel'])->name('vacancies.cancel');
        Route::post('/vacancies/{vacancy}/reopen', [VacancyController::class, 'reopen'])->name('vacancies.reopen');
    });

    // Delete — requires delete-vacancies permission
    Route::middleware('permission:delete-vacancies')->group(function () {
        Route::delete('/vacancies/{vacancy}', [VacancyController::class, 'destroy'])->name('vacancies.destroy');
    });
});
