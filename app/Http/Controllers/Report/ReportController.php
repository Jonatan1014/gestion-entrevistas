<?php

namespace App\Http\Controllers\Report;

use App\Enums\InterviewStatus;
use App\Enums\VacancyApplicantStatus;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\TestResult;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\SelectionQueryService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Display the reports landing page with KPIs and overview.
     */
    public function index(Request $request): Response
    {
        $isAdmin = $request->user()->hasRole('Admin');
        $userId = $request->user()->id;

        // KPI: Active vacancies
        $activeVacancies = Vacancy::where('status', VacancyStatus::OPEN)->count();

        // KPI: Applicants in process (not apt/no_apt)
        $applicantsInProcess = DB::table('vacancy_applicant')
            ->whereIn('status', ['registered', 'in_interview', 'evaluated'])
            ->when(! $isAdmin, fn ($q) => $q->whereExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))->from('interviews')
                    ->whereColumn('interviews.applicant_id', 'vacancy_applicant.applicant_id')
                    ->whereColumn('interviews.vacancy_id', 'vacancy_applicant.vacancy_id')
                    ->where('interviews.interviewer_id', $userId);
            }))
            ->count();

        // KPI: Interviews this month
        $interviewsThisMonth = Interview::whereMonth('scheduled_at', now()->month)
            ->whereYear('scheduled_at', now()->year)
            ->when(! $isAdmin, fn ($q) => $q->where('interviewer_id', $userId))
            ->count();

        // KPI: Apt conversion rate
        $totalEvaluated = DB::table('vacancy_applicant')
            ->whereIn('status', ['apt', 'no_apt'])
            ->when(! $isAdmin, fn ($q) => $q->whereExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))->from('interviews')
                    ->whereColumn('interviews.applicant_id', 'vacancy_applicant.applicant_id')
                    ->whereColumn('interviews.vacancy_id', 'vacancy_applicant.vacancy_id')
                    ->where('interviews.interviewer_id', $userId);
            }))
            ->count();
        $totalApt = DB::table('vacancy_applicant')
            ->where('status', 'apt')
            ->when(! $isAdmin, fn ($q) => $q->whereExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))->from('interviews')
                    ->whereColumn('interviews.applicant_id', 'vacancy_applicant.applicant_id')
                    ->whereColumn('interviews.vacancy_id', 'vacancy_applicant.vacancy_id')
                    ->where('interviews.interviewer_id', $userId);
            }))
            ->count();
        $conversionRate = $totalEvaluated > 0 ? round(($totalApt / $totalEvaluated) * 100) : 0;

        // Pipeline data per stage
        $pipelineQuery = DB::table('vacancy_applicant as va')
            ->select('va.status', DB::raw('COUNT(*) as count'))
            ->join('vacancies', 'vacancies.id', '=', 'va.vacancy_id')
            ->where('vacancies.status', VacancyStatus::OPEN->value)
            ->when(! $isAdmin, fn ($q) => $q->whereExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))->from('interviews')
                    ->whereColumn('interviews.applicant_id', 'va.applicant_id')
                    ->whereColumn('interviews.vacancy_id', 'va.vacancy_id')
                    ->where('interviews.interviewer_id', $userId);
            }))
            ->groupBy('va.status')
            ->get()
            ->pluck('count', 'status');

        // Recent activity
        $recentScores = TestResult::with(['applicant', 'test', 'evaluator', 'vacancy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'type' => 'score',
                'applicant' => $r->applicant->name,
                'test' => $r->test->name,
                'score' => (float) $r->score,
                'vacancy' => $r->vacancy->position,
                'evaluator' => $r->evaluator->name,
                'date' => $r->created_at->diffForHumans(),
            ]);

        $recentInterviews = Interview::with(['applicant', 'vacancy', 'interviewer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($i) => [
                'type' => 'interview',
                'applicant' => $i->applicant->name,
                'vacancy' => $i->vacancy->position,
                'status' => $i->status->value,
                'interviewer' => $i->interviewer->name,
                'date' => $i->created_at->diffForHumans(),
            ]);

        $activity = $recentScores->concat($recentInterviews)
            ->sortByDesc('date')
            ->take(8)
            ->values();

        return Inertia::render('reports/Overview', [
            'kpis' => [
                'activeVacancies' => $activeVacancies,
                'applicantsInProcess' => $applicantsInProcess,
                'interviewsThisMonth' => $interviewsThisMonth,
                'conversionRate' => $conversionRate,
            ],
            'pipeline' => [
                'registered' => $pipelineQuery[VacancyApplicantStatus::REGISTERED->value] ?? 0,
                'in_interview' => $pipelineQuery[VacancyApplicantStatus::IN_INTERVIEW->value] ?? 0,
                'evaluated' => $pipelineQuery[VacancyApplicantStatus::EVALUATED->value] ?? 0,
                'apt' => $pipelineQuery[VacancyApplicantStatus::APT->value] ?? 0,
                'no_apt' => $pipelineQuery[VacancyApplicantStatus::NO_APT->value] ?? 0,
            ],
            'activity' => $activity,
            'vacancies' => $this->vacancyOptions(),
            'interviewers' => $this->interviewerOptions($request),
            'filters' => $request->only(['vacancy_id', 'date_from', 'date_to', 'interviewer_id']),
            'isAdmin' => $isAdmin,
        ]);
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
