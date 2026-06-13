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

        $applicants = Applicant::orderBy('name')->get();
        $vacancies = Vacancy::orderBy('position')->get();
        $interviewers = $isAdmin
            ? User::role('Entrevistador')->orderBy('name')->get()
            : collect([$request->user()]);

        return Inertia::render('interviews/Create', [
            'applicants' => $applicants,
            'vacancies' => $vacancies,
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
            ->with('success', 'Interview scheduled successfully.');
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
            ->with('success', 'Interview deleted successfully.');
    }

    /**
     * Mark the interview as completed.
     */
    public function complete(CompleteInterviewRequest $request, Interview $interview): RedirectResponse
    {
        if ($interview->status !== InterviewStatus::PENDING) {
            abort(403, 'Only pending interviews can be completed.');
        }

        $interview->update([
            'status' => InterviewStatus::COMPLETED,
            'observations' => $request->validated('observations'),
            'completed_at' => now(),
        ]);

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Interview marked as completed.');
    }

    /**
     * Mark the interview as cancelled.
     */
    public function cancel(CancelInterviewRequest $request, Interview $interview): RedirectResponse
    {
        if ($interview->status !== InterviewStatus::PENDING) {
            abort(403, 'Only pending interviews can be cancelled.');
        }

        $interview->update([
            'status' => InterviewStatus::CANCELLED,
            'cancellation_reason' => $request->validated('cancellation_reason'),
        ]);

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Interview cancelled successfully.');
    }
}
