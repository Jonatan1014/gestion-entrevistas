<?php

use App\Http\Controllers\Report\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Report Routes
|--------------------------------------------------------------------------
|
| Reports and exports for the selection process.
|
*/

Route::middleware(['auth', 'verified', 'permission:view-reports'])->group(function () {
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/comparison', [ReportController::class, 'comparison'])->name('reports.comparison');
    Route::get('reports/pipeline', [ReportController::class, 'pipeline'])->name('reports.pipeline');
    Route::get('reports/averages', [ReportController::class, 'averages'])->name('reports.averages');
    Route::get('reports/interviews', [ReportController::class, 'interviews'])->name('reports.interviews');
});
