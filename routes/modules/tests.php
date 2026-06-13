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
| view-tests: Admin and Entrevistador can view
| manage-tests: Admin only (create, edit, delete, attach, detach, weight)
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Standalone test CRUD — Admin only
    Route::resource('tests', TestController::class)->middleware('permission:manage-tests');

    // Nested under vacancies
    Route::prefix('vacancies/{vacancy}')->group(function () {
        Route::get('tests', [VacancyTestController::class, 'index'])->name('vacancies.tests.index')->middleware('permission:view-tests');
        Route::post('tests', [VacancyTestController::class, 'attach'])->name('vacancies.tests.attach')->middleware('permission:manage-tests');
        Route::put('tests/{test}', [VacancyTestController::class, 'updateWeight'])->name('vacancies.tests.update-weight')->middleware('permission:manage-tests');
        Route::delete('tests/{test}', [VacancyTestController::class, 'detach'])->name('vacancies.tests.detach')->middleware('permission:manage-tests');
    });
});
