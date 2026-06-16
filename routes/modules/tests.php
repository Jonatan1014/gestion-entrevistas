<?php

use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\Test\VacancyTestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
|
| Standalone test CRUD for Admins and nested vacancy-test associations.
| view-tests: Admin and Entrevistador can view index and show
| manage-tests: Admin only (create, store, edit, update, destroy, attach, detach, weight)
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // CRUD write routes — MUST be registered BEFORE wildcard {test}
    Route::middleware('permission:manage-tests')->group(function () {
        Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
        Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    });

    // View-only — Entrevistador and Admin
    Route::middleware('permission:view-tests')->group(function () {
        Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
        Route::get('/tests/{test}', [TestController::class, 'show'])->name('tests.show');
    });

    // Edit/Update/Delete — Admin only
    Route::middleware('permission:manage-tests')->group(function () {
        Route::get('/tests/{test}/edit', [TestController::class, 'edit'])->name('tests.edit');
        Route::put('/tests/{test}', [TestController::class, 'update'])->name('tests.update');
        Route::delete('/tests/{test}', [TestController::class, 'destroy'])->name('tests.destroy');
    });

    // Nested under vacancies
    Route::prefix('vacancies/{vacancy}')->group(function () {
        Route::get('tests', [VacancyTestController::class, 'index'])
            ->name('vacancies.tests.index')
            ->middleware('permission:view-tests');
        Route::post('tests', [VacancyTestController::class, 'attach'])
            ->name('vacancies.tests.attach')
            ->middleware('permission:manage-tests');
        Route::put('tests/{test}', [VacancyTestController::class, 'updateWeight'])
            ->name('vacancies.tests.update-weight')
            ->middleware('permission:manage-tests');
        Route::delete('tests/{test}', [VacancyTestController::class, 'detach'])
            ->name('vacancies.tests.detach')
            ->middleware('permission:manage-tests');
    });
});
