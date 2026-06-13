<?php

namespace App\Http\Controllers\Applicant;

use App\Enums\VacancyApplicantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\BlockApplicantRequest;
use App\Http\Requests\Applicant\StoreApplicantRequest;
use App\Http\Requests\Applicant\UpdateApplicantRequest;
use App\Models\Applicant;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantController extends Controller
{
    /**
     * Display a listing of applicants.
     */
    public function index(Request $request): Response
    {
        $query = Applicant::with(['createdBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('assigned_to_me')) {
            $query->where('created_by', $request->user()->id);
        }

        $applicants = $query->paginate(15)->withQueryString();

        return Inertia::render('applicants/Index', [
            'applicants' => $applicants,
            'canCreateApplicants' => $request->user()->can('create-applicants'),
            'canEditApplicants' => $request->user()->can('edit-applicants'),
            'canDeleteApplicants' => $request->user()->can('delete-applicants'),
            'canBlockApplicants' => $request->user()->can('block-applicants'),
            'filters' => $request->only(['search', 'assigned_to_me']),
        ]);
    }

    /**
     * Show the form for creating a new applicant.
     */
    public function create(): Response
    {
        return Inertia::render('applicants/Create');
    }

    /**
     * Store a newly created applicant.
     */
    public function store(StoreApplicantRequest $request): RedirectResponse
    {
        $applicant = Applicant::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Applicant created successfully.');
    }

    /**
     * Display the specified applicant.
     */
    public function show(Applicant $applicant): Response
    {
        $applicant->load(['documents', 'vacancies', 'createdBy', 'blockedBy']);

        return Inertia::render('applicants/Show', [
            'applicant' => $applicant,
            'history' => $this->buildHistory($applicant),
            'canEditApplicants' => auth()->user()->can('edit-applicants'),
            'canDeleteApplicants' => auth()->user()->can('delete-applicants'),
            'canBlockApplicants' => auth()->user()->can('block-applicants'),
            'canCreateApplicants' => auth()->user()->can('create-applicants'),
        ]);
    }

    /**
     * Show the form for editing the specified applicant.
     */
    public function edit(Applicant $applicant): Response
    {
        return Inertia::render('applicants/Edit', [
            'applicant' => $applicant,
        ]);
    }

    /**
     * Update the specified applicant.
     */
    public function update(UpdateApplicantRequest $request, Applicant $applicant): RedirectResponse
    {
        $applicant->update($request->validated());

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Applicant updated successfully.');
    }

    /**
     * Remove the specified applicant (soft delete).
     */
    public function destroy(Applicant $applicant): RedirectResponse
    {
        $applicant->delete();

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant deleted successfully.');
    }

    /**
     * Block the applicant.
     */
    public function block(BlockApplicantRequest $request, Applicant $applicant): RedirectResponse
    {
        $applicant->update([
            'is_blocked' => true,
            'block_reason' => $request->validated('block_reason'),
            'blocked_by' => $request->user()->id,
            'blocked_at' => now(),
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Applicant blocked successfully.');
    }

    /**
     * Unblock the applicant.
     */
    public function unblock(Request $request, Applicant $applicant): RedirectResponse
    {
        if (! $request->user()->can('block-applicants')) {
            abort(403);
        }

        $applicant->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_by' => null,
            'blocked_at' => null,
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Applicant unblocked successfully.');
    }

    /**
     * Associate the applicant to a vacancy.
     */
    public function associateVacancy(Request $request, Applicant $applicant, Vacancy $vacancy): RedirectResponse
    {
        if (! $request->user()->can('edit-applicants')) {
            abort(403);
        }

        if ($applicant->is_blocked) {
            return redirect()->back()
                ->with('error', "Cannot assign blocked applicant: {$applicant->block_reason}");
        }

        if ($applicant->vacancies()->where('vacancy_id', $vacancy->id)->exists()) {
            Validator::make([], [])->errors()->add('vacancy_id', 'Applicant is already associated with this vacancy.');

            return redirect()->back()
                ->withErrors(['vacancy_id' => 'Applicant is already associated with this vacancy.']);
        }

        $applicant->vacancies()->attach($vacancy, [
            'status' => VacancyApplicantStatus::REGISTERED,
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Applicant associated to vacancy successfully.');
    }

    /**
     * Update the applicant status for a specific vacancy.
     */
    public function updateVacancyStatus(Request $request, Applicant $applicant, Vacancy $vacancy): RedirectResponse
    {
        if (! $request->user()->can('edit-applicants')) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_column(VacancyApplicantStatus::cases(), 'value'))],
        ]);

        $applicant->vacancies()->updateExistingPivot($vacancy->id, [
            'status' => $validated['status'],
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Vacancy status updated successfully.');
    }

    /**
     * Build a history timeline for the applicant.
     */
    private function buildHistory(Applicant $applicant): array
    {
        $history = [];

        if ($applicant->is_blocked && $applicant->blocked_at) {
            $history[] = [
                'type' => 'blocked',
                'date' => $applicant->blocked_at->toIso8601String(),
                'title' => 'Applicant blocked',
                'description' => $applicant->block_reason,
            ];
        }

        foreach ($applicant->vacancies as $vacancy) {
            $history[] = [
                'type' => 'vacancy',
                'date' => $vacancy->pivot->created_at->toIso8601String(),
                'title' => "Associated to vacancy: {$vacancy->position}",
                'description' => "Status: {$vacancy->pivot->status}",
            ];
        }

        return collect($history)
            ->sortByDesc('date')
            ->values()
            ->all();
    }
}
