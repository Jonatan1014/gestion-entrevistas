<?php

namespace App\Http\Controllers\Report;

use App\Exports\AveragesExport;
use App\Exports\ComparisonExport;
use App\Exports\InterviewsExport;
use App\Exports\PipelineExport;
use App\Http\Controllers\Controller;
use App\Services\SelectionQueryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private SelectionQueryService $queryService
    ) {
        //
    }

    /**
     * Export candidate comparison report as PDF.
     */
    public function comparisonPdf(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->queryService->comparisonQuery($filters)->get();

        $pdf = Pdf::loadView('reports.comparison', ['rows' => $rows]);

        return $pdf->download('comparison-report.pdf');
    }

    /**
     * Export candidate comparison report as Excel.
     */
    public function comparisonExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);

        return Excel::download(new ComparisonExport($filters), 'comparison-report.xlsx');
    }

    /**
     * Export pipeline report as PDF.
     */
    public function pipelinePdf(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->queryService->pipelineQuery($filters)->get();

        $pdf = Pdf::loadView('reports.pipeline', ['rows' => $rows]);

        return $pdf->download('pipeline-report.pdf');
    }

    /**
     * Export pipeline report as Excel.
     */
    public function pipelineExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);

        return Excel::download(new PipelineExport($filters), 'pipeline-report.xlsx');
    }

    /**
     * Export score averages report as PDF.
     */
    public function averagesPdf(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->queryService->averagesQuery($filters)->get();

        $pdf = Pdf::loadView('reports.averages', ['rows' => $rows]);

        return $pdf->download('averages-report.pdf');
    }

    /**
     * Export score averages report as Excel.
     */
    public function averagesExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);

        return Excel::download(new AveragesExport($filters), 'averages-report.xlsx');
    }

    /**
     * Export completed interviews report as PDF.
     */
    public function interviewsPdf(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->queryService->completedInterviewsQuery($filters)->get();

        $pdf = Pdf::loadView('reports.interviews', ['rows' => $rows]);

        return $pdf->download('interviews-report.pdf');
    }

    /**
     * Export completed interviews report as Excel.
     */
    public function interviewsExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);

        return Excel::download(new InterviewsExport($filters), 'interviews-report.xlsx');
    }

    /**
     * Extract report filters from the request.
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $filters = $request->only(['vacancy_id', 'date_from', 'date_to', 'interviewer_id']);

        if (! $request->user()->hasRole('Admin')) {
            $filters['interviewer_id'] = $request->user()->id;
        }

        return $filters;
    }
}
