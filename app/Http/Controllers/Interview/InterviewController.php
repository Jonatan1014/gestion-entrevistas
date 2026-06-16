<?php

namespace App\Http\Controllers\Interview;

use App\Enums\InterviewStatus;
use App\Enums\VacancyApplicantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Interview\CancelInterviewRequest;
use App\Http\Requests\Interview\CompleteInterviewRequest;
use App\Http\Requests\Interview\StoreInterviewRequest;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    /**
     * Display a listing of interviews.
     */
    public function index(Request $request): Response
    {
        $query = Interview::with(['applicant', 'vacancy', 'interviewer'])
            ->orderBy('scheduled_at', 'desc');

        // Entrevistador users only see their own interviews.
        if (! $request->user()->hasRole('Admin')) {
            $query->where('interviewer_id', $request->user()->id);
        }

        if ($request->filled('interviewer_id')) {
            $query->where('interviewer_id', $request->input('interviewer_id'));
        }

        if ($request->filled('applicant_id')) {
            $query->where('applicant_id', $request->input('applicant_id'));
        }

        if ($request->filled('applicant_name')) {
            $search = $request->input('applicant_name');
            $query->whereHas('applicant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('vacancy_id')) {
            $query->where('vacancy_id', $request->input('vacancy_id'));
        }

        $interviews = $query->paginate(15)->withQueryString();

        return Inertia::render('interviews/Index', [
            'interviews' => $interviews,
            'interviewers' => User::role('Entrevistador')->orderBy('name')->get(),
            'vacancies' => Vacancy::orderBy('position')->get(),
            'canManageInterviews' => $request->user()->can('manage-interviews'),
            'filters' => $request->only(['interviewer_id', 'applicant_id', 'applicant_name', 'vacancy_id']),
        ]);
    }

    /**
     * Show the form for creating a new interview.
     */
    public function create(Request $request): Response
    {
        $isAdmin = $request->user()->hasRole('Admin');

        $vacancies = Vacancy::with('applicants')->orderBy('position')->get(['id', 'position', 'location']);

        // Build vacancy → applicants map for the frontend
        $vacancyApplicants = $vacancies->mapWithKeys(function ($vacancy) {
            return [$vacancy->id => $vacancy->applicants->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
            ])->values()];
        });

        $interviewers = $isAdmin
            ? User::role('Entrevistador')->orderBy('name')->get()
            : collect([$request->user()]);

        return Inertia::render('interviews/Create', [
            'vacancies' => $vacancies->map(fn ($v) => ['id' => $v->id, 'position' => $v->position, 'location' => $v->location])->values(),
            'vacancyApplicants' => $vacancyApplicants,
            'interviewers' => $interviewers,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Store a newly created interview.
     */
    public function store(StoreInterviewRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $interview = Interview::create([
            ...$validated,
            'status' => InterviewStatus::PENDING,
        ]);

        // ADR-03: assignment also moves the vacancy_applicant pivot from registered to in_interview.
        $applicant = Applicant::find($validated['applicant_id']);
        $existingPivot = $applicant->vacancies()
            ->where('vacancy_id', $validated['vacancy_id'])
            ->first();

        if ($existingPivot && $existingPivot->pivot->status === VacancyApplicantStatus::REGISTERED->value) {
            $applicant->vacancies()->updateExistingPivot($validated['vacancy_id'], [
                'status' => VacancyApplicantStatus::IN_INTERVIEW,
            ]);
        }

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Entrevista programada correctamente.');
    }

    /**
     * Display the specified interview.
     */
    public function show(Interview $interview): Response
    {
        $interview->load(['applicant', 'vacancy', 'interviewer']);

        return Inertia::render('interviews/Show', [
            'interview' => $interview,
            'canManageInterviews' => auth()->user()->can('manage-interviews'),
        ]);
    }

    /**
     * Remove the specified interview (soft delete).
     */
    public function destroy(Interview $interview): RedirectResponse
    {
        if (! auth()->user()->can('delete-interviews')) {
            abort(403);
        }

        $interview->delete();

        return redirect()->route('interviews.index')
            ->with('success', 'Entrevista eliminada correctamente.');
    }

    /**
     * Mark the interview as completed.
     */
    public function complete(CompleteInterviewRequest $request, Interview $interview): RedirectResponse
    {
        if ($interview->status !== InterviewStatus::PENDING) {
            abort(403, 'Solo se pueden completar entrevistas pendientes.');
        }

        $interview->update([
            'status' => InterviewStatus::COMPLETED,
            'observations' => $request->validated('observations'),
            'score' => $request->validated('score'),
            'completed_at' => now(),
        ]);

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Entrevista marcada como completada.');
    }

    /**
     * Mark the interview as cancelled.
     */
    public function cancel(CancelInterviewRequest $request, Interview $interview): RedirectResponse
    {
        if ($interview->status !== InterviewStatus::PENDING) {
            abort(403, 'Solo se pueden cancelar entrevistas pendientes.');
        }

        $interview->update([
            'status' => InterviewStatus::CANCELLED,
            'cancellation_reason' => $request->validated('cancellation_reason'),
        ]);

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Entrevista cancelada correctamente.');
    }

    /**
     * Display a calendar view of scheduled interviews for the current month.
     */
    public function calendar(Request $request): Response
    {
        $user = $request->user();
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Interview::with(['applicant', 'vacancy', 'interviewer'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at');

        // Entrevistador only sees own interviews
        if (! $user->can('manage-interviews')) {
            $query->where('interviewer_id', $user->id);
        }

        $interviews = $query->get()->groupBy(fn ($i) => $i->scheduled_at->format('Y-m-d'));

        $calendar = [];
        foreach ($interviews as $date => $dayInterviews) {
            $calendar[$date] = $dayInterviews->map(fn ($i) => [
                'id' => $i->id,
                'scheduled_at' => $i->scheduled_at->format('H:i'),
                'type' => $i->type,
                'status' => $i->status->value,
                'applicant_name' => $i->applicant->name,
                'vacancy_position' => $i->vacancy->position,
                'interviewer_name' => $i->interviewer->name,
            ]);
        }

        // Month navigation
        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();
        $today = now()->format('Y-m-d');

        return Inertia::render('interviews/Calendar', [
            'calendar' => $calendar,
            'year' => $year,
            'month' => $month,
            'monthName' => $start->translatedFormat('F Y'),
            'prevMonth' => ['year' => $prevMonth->year, 'month' => $prevMonth->month],
            'nextMonth' => ['year' => $nextMonth->year, 'month' => $nextMonth->month],
            'daysInMonth' => $start->daysInMonth,
            'firstDayOfWeek' => $start->dayOfWeek,
            'today' => $today,
            'canManageInterviews' => $user->can('manage-interviews'),
        ]);
    }
}
