<?php

use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\ReportExportController;
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

    Route::get('reports/comparison/pdf', [ReportExportController::class, 'comparisonPdf'])->name('reports.comparison.pdf');
    Route::get('reports/comparison/excel', [ReportExportController::class, 'comparisonExcel'])->name('reports.comparison.excel');
    Route::get('reports/pipeline/pdf', [ReportExportController::class, 'pipelinePdf'])->name('reports.pipeline.pdf');
    Route::get('reports/pipeline/excel', [ReportExportController::class, 'pipelineExcel'])->name('reports.pipeline.excel');
    Route::get('reports/averages/pdf', [ReportExportController::class, 'averagesPdf'])->name('reports.averages.pdf');
    Route::get('reports/averages/excel', [ReportExportController::class, 'averagesExcel'])->name('reports.averages.excel');
    Route::get('reports/interviews/pdf', [ReportExportController::class, 'interviewsPdf'])->name('reports.interviews.pdf');
    Route::get('reports/interviews/excel', [ReportExportController::class, 'interviewsExcel'])->name('reports.interviews.excel');
});
