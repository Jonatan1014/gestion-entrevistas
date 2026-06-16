<?php

use App\Http\Controllers\TestResult\QuickScoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Quick Score Routes
|--------------------------------------------------------------------------
|
| Direct score recording: select vacancy → applicant → test → score.
| Accessible by users with record-results permission (Entrevistador + Admin).
|
*/

Route::middleware(['auth', 'verified', 'permission:record-results'])->group(function () {
    Route::get('/quick-score', [QuickScoreController::class, 'create'])->name('quick-score.create');
    Route::post('/quick-score', [QuickScoreController::class, 'store'])->name('quick-score.store');

    // Ajax endpoints for cascading selects
    Route::get('/api/vacancies/{vacancy}/applicants', [QuickScoreController::class, 'applicants'])->name('api.vacancies.applicants');
    Route::get('/api/vacancies/{vacancy}/tests', [QuickScoreController::class, 'tests'])->name('api.vacancies.tests');
    Route::get('/api/vacancies/{vacancy}/applicants/{applicant}/tests/{test}/history', [QuickScoreController::class, 'history'])->name('api.scores.history');
});
