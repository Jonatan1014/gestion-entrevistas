<?php

use App\Http\Controllers\TestResult\TestResultController;
use App\Http\Controllers\TestResult\VacancyResultsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test Result Routes
|--------------------------------------------------------------------------
|
| Score recording per applicant per test and vacancy-level result overview.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Record scores per test
    Route::prefix('tests/{test}/applicants/{applicant}/vacancies/{vacancy}')->group(function () {
        Route::get('results/create', [TestResultController::class, 'create'])->name('test-results.create')->middleware('permission:record-results');
        Route::post('results', [TestResultController::class, 'store'])->name('test-results.store')->middleware('permission:record-results');
        Route::get('results', [TestResultController::class, 'show'])->name('test-results.show')->middleware('permission:view-results');
        Route::put('results', [TestResultController::class, 'update'])->name('test-results.update')->middleware('permission:override-results');
    });

    // Vacancy-level results
    Route::prefix('vacancies/{vacancy}')->group(function () {
        Route::get('results', [VacancyResultsController::class, 'index'])->name('vacancies.results.index')->middleware('permission:view-results');
        Route::put('applicants/{applicant}/final-status', [VacancyResultsController::class, 'setFinalStatus'])->name('vacancies.results.final-status')->middleware('permission:set-final-status');
    });
});
