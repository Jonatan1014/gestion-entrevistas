<?php

use App\Http\Controllers\Interview\InterviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Interview Routes
|--------------------------------------------------------------------------
|
| Interview scheduling and lifecycle management.
|
*/

Route::middleware(['auth', 'verified', 'permission:view-interviews'])->group(function () {
    // Calendar — MUST be registered BEFORE resource to avoid {interview} wildcard match
    Route::get('interviews/calendar', [InterviewController::class, 'calendar'])->name('interviews.calendar');

    Route::resource('interviews', InterviewController::class)->except(['edit', 'update']);

    Route::post('interviews/{interview}/complete', [InterviewController::class, 'complete'])
        ->name('interviews.complete')
        ->middleware('permission:manage-interviews');

    Route::post('interviews/{interview}/cancel', [InterviewController::class, 'cancel'])
        ->name('interviews.cancel')
        ->middleware('permission:manage-interviews');
});
