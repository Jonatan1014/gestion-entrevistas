<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\SelectionQueryService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
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
     * Display the reports landing page (defaults to comparison tab).
     */
    public function index(Request $request): Response
    {
        return $this->comparison($request);
    }

    /**
     * Display the candidate comparison report.
     */
    public function comparison(Request $request): Response
    {
        $filters = $this->filters($request);
        $query = $this->queryService->comparisonQuery($filters);
        $query = $this->applyInterviewerScope($request, $query);

        return Inertia::render('reports/Index', [
            'activeTab' => 'comparison',
            'comparison' => $query->paginate(100)->withQueryString(),
            'vacancies' => $this->vacancyOptions(),
            'interviewers' => $this->interviewerOptions($request),
            'filters' => $filters,
            'isAdmin' => $request->user()->hasRole('Admin'),
        ]);
    }

    /**
     * Display the selection pipeline report.
     */
    public function pipeline(Request $request): Response
    {
        $filters = $this->filters($request);
        $query = $this->queryService->pipelineQuery($filters);
        $query = $this->applyInterviewerScope($request, $query);

        return Inertia::render('reports/Index', [
            'activeTab' => 'pipeline',
            'pipeline' => $query->paginate(100)->withQueryString(),
            'vacancies' => $this->vacancyOptions(),
            'interviewers' => $this->interviewerOptions($request),
            'filters' => $filters,
            'isAdmin' => $request->user()->hasRole('Admin'),
        ]);
    }

    /**
     * Display the test score averages report.
     */
    public function averages(Request $request): Response
    {
        $filters = $this->filters($request);
        $query = $this->queryService->averagesQuery($filters);
        $query = $this->applyInterviewerScope($request, $query);

        return Inertia::render('reports/Index', [
            'activeTab' => 'averages',
            'averages' => $query->paginate(100)->withQueryString(),
            'vacancies' => $this->vacancyOptions(),
            'interviewers' => $this->interviewerOptions($request),
            'filters' => $filters,
            'isAdmin' => $request->user()->hasRole('Admin'),
        ]);
    }

    /**
     * Display the completed interviews report.
     */
    public function interviews(Request $request): Response
    {
        $filters = $this->filters($request);
        $query = $this->queryService->completedInterviewsQuery($filters);
        $query = $this->applyInterviewerScope($request, $query);

        return Inertia::render('reports/Index', [
            'activeTab' => 'interviews',
            'interviews' => $query->paginate(15)->withQueryString(),
            'vacancies' => $this->vacancyOptions(),
            'interviewers' => $this->interviewerOptions($request),
            'filters' => $filters,
            'isAdmin' => $request->user()->hasRole('Admin'),
        ]);
    }

    /**
     * Extract report filters from the request.
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return $request->only(['vacancy_id', 'date_from', 'date_to', 'interviewer_id']);
    }

    /**
     * Apply Entrevistador scope to a report query when the user is not Admin.
     */
    private function applyInterviewerScope(Request $request, Builder $query): Builder
    {
        if (! $request->user()->hasRole('Admin')) {
            return $this->queryService->scopeByInterviewer($query, $request->user());
        }

        return $query;
    }

    /**
     * Get vacancy options for the filter dropdown.
     */
    private function vacancyOptions()
    {
        return Vacancy::orderBy('position')->get(['id', 'position']);
    }

    /**
     * Get interviewer options for the filter dropdown (admin only).
     */
    private function interviewerOptions(Request $request)
    {
        if (! $request->user()->hasRole('Admin')) {
            return collect();
        }

        return User::role('Entrevistador')->orderBy('name')->get(['id', 'name']);
    }
}
