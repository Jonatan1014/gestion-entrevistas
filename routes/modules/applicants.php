<?php

use App\Http\Controllers\Applicant\ApplicantController;
use App\Http\Controllers\Applicant\ApplicantDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Applicant Routes
|--------------------------------------------------------------------------
|
| Applicant CRUD, blocking, document management, and vacancy association.
| view-applicants: both Admin and Entrevistador can view
| create/edit/delete: Admin and Entrevistador (per permission)
| block/unblock: Admin only
|
| IMPORTANT: Explicit routes (/create, /store) must be registered BEFORE
| wildcard routes (/{applicant}) to avoid Laravel interpreting "create"
| as a route parameter.
|
*/

// Create and Store — registered BEFORE wildcard {applicant} routes
Route::middleware(['auth', 'verified', 'permission:create-applicants'])->group(function () {
    Route::get('/applicants/create', [ApplicantController::class, 'create'])->name('applicants.create');
    Route::post('/applicants', [ApplicantController::class, 'store'])->name('applicants.store');
});

// Document management
Route::middleware(['auth', 'verified', 'permission:create-applicants'])->group(function () {
    Route::post('/applicants/{applicant}/documents', [ApplicantDocumentController::class, 'store'])->name('applicants.documents.store');
    Route::delete('/applicants/{applicant}/documents/{document}', [ApplicantDocumentController::class, 'destroy'])->name('applicants.documents.destroy');
});

// View — wildcard routes
Route::middleware(['auth', 'verified', 'permission:view-applicants'])->group(function () {
    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('/applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::get('/applicants/{applicant}/documents', [ApplicantDocumentController::class, 'index'])->name('applicants.documents.index');
    Route::get('/applicants/{applicant}/documents/{document}/preview', [ApplicantDocumentController::class, 'preview'])
        ->name('applicants.documents.preview')
        ->middleware('permission:view-applicants');
    Route::get('/applicants/{applicant}/documents/{document}/download', [ApplicantDocumentController::class, 'download'])
        ->name('applicants.documents.download')
        ->middleware('permission:view-applicants');
});

// Edit
Route::middleware(['auth', 'verified', 'permission:edit-applicants'])->group(function () {
    Route::get('/applicants/{applicant}/edit', [ApplicantController::class, 'edit'])->name('applicants.edit');
    Route::put('/applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');
    Route::post('/applicants/{applicant}/vacancies/{vacancy}/associate', [ApplicantController::class, 'associateVacancy'])
        ->name('applicants.vacancies.associate');
    Route::put('/applicants/{applicant}/vacancies/{vacancy}/status', [ApplicantController::class, 'updateVacancyStatus'])
        ->name('applicants.vacancies.status');
});

// Delete
Route::middleware(['auth', 'verified', 'permission:delete-applicants'])->group(function () {
    Route::delete('/applicants/{applicant}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');
});

// Block/Unblock — Admin only
Route::middleware(['auth', 'verified', 'permission:block-applicants'])->group(function () {
    Route::post('/applicants/{applicant}/block', [ApplicantController::class, 'block'])->name('applicants.block');
    Route::post('/applicants/{applicant}/unblock', [ApplicantController::class, 'unblock'])->name('applicants.unblock');
});
