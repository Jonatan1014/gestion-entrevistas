<?php

namespace App\Http\Controllers\Vacancy;

use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vacancy\StoreVacancyRequest;
use App\Http\Requests\Vacancy\UpdateVacancyRequest;
use App\Models\Applicant;
use App\Models\Test;
use App\Models\Vacancy;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VacancyController extends Controller
{
    /**
     * Display a listing of vacancies.
     */
    public function index(): Response
    {
        $vacancies = Vacancy::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('vacancies/Index', [
            'vacancies' => $vacancies,
            'canCreateVacancies' => auth()->user()->can('create-vacancies'),
            'canEditVacancies' => auth()->user()->can('edit-vacancies'),
            'canDeleteVacancies' => auth()->user()->can('delete-vacancies'),
        ]);
    }

    /**
     * Show the form for creating a new vacancy.
     */
    public function create(): Response
    {
        $tests = Test::orderBy('name')->get(['id', 'name', 'type']);

        return Inertia::render('vacancies/Create', [
            'tests' => $tests,
        ]);
    }

    /**
     * Store a newly created vacancy.
     */
    public function store(StoreVacancyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $testIds = $data['test_ids'] ?? [];
        unset($data['test_ids']);

        $vacancy = Vacancy::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        // Attach tests if selected
        if (! empty($testIds)) {
            foreach ($testIds as $testId) {
                $vacancy->tests()->attach($testId, ['weight' => 0]);
            }
        }

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacante creada correctamente.');
    }

    /**
     * Display the specified vacancy.
     */
    public function show(Vacancy $vacancy): Response
    {
        $vacancy->load(['creator', 'tests', 'applicants']);

        // Build applicant ranking with weighted averages
        $ranking = $vacancy->applicants->map(function (Applicant $applicant) use ($vacancy) {
            $results = $applicant->testResults()
                ->where('vacancy_id', $vacancy->id)
                ->with('test')
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('test_id');

            $testData = $results->map(fn ($r) => [
                'score' => (float) $r->score,
                'max_score' => (float) $r->test->max_score,
                'test_name' => $r->test->name,
            ]);

            $weights = $results->map(function ($r) use ($vacancy) {
                $pivot = $vacancy->tests->firstWhere('id', $r->test_id);
                return (float) ($pivot?->pivot?->weight ?? 0);
            })->all();

            $avg = ScoringService::weightedAverage(
                $testData->all(),
                $weights,
                $vacancy->min_grade !== null ? (float) $vacancy->min_grade : null
            );

            return [
                'id' => $applicant->id,
                'name' => $applicant->name,
                'email' => $applicant->email,
                'status' => $applicant->pivot->status,
                'weighted_average' => $avg['score'] ?? 0,
                'meets_min_grade' => $avg['meets_min_grade'] ?? false,
                'has_scores' => $results->isNotEmpty(),
            ];
        })
        ->sortByDesc('weighted_average')
        ->values();

        return Inertia::render('vacancies/Show', [
            'vacancy' => $vacancy,
            'tests' => $vacancy->tests->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'type' => $t->type->value,
                'max_score' => $t->max_score,
                'weight' => $t->pivot->weight,
            ]),
            'applicants' => $vacancy->applicants->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
                'status' => $a->pivot->status,
            ]),
            'ranking' => $ranking,
            'canEditVacancies' => auth()->user()->can('edit-vacancies'),
            'canDeleteVacancies' => auth()->user()->can('delete-vacancies'),
        ]);
    }

    /**
     * Show the form for editing the specified vacancy.
     */
    public function edit(Vacancy $vacancy): Response
    {
        $vacancy->load('tests');
        $allTests = Test::orderBy('name')->get(['id', 'name', 'type']);

        return Inertia::render('vacancies/Edit', [
            'vacancy' => $vacancy,
            'tests' => $allTests,
            'attachedTestIds' => $vacancy->tests->pluck('id'),
        ]);
    }

    /**
     * Update the specified vacancy.
     */
    public function update(UpdateVacancyRequest $request, Vacancy $vacancy): RedirectResponse
    {
        $data = $request->validated();
        $testIds = $data['test_ids'] ?? null;
        unset($data['test_ids']);

        $vacancy->update($data);

        // Sync tests if provided
        if (is_array($testIds)) {
            $sync = [];
            foreach ($testIds as $testId) {
                // Keep existing weight if already attached, or default to 0
                $existingWeight = $vacancy->tests()->where('test_id', $testId)->first()?->pivot->weight ?? 0;
                $sync[$testId] = ['weight' => $existingWeight];
            }
            $vacancy->tests()->sync($sync);
        }

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacante actualizada correctamente.');
    }

    /**
     * Remove the specified vacancy (soft delete).
     */
    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        $vacancy->delete();

        return redirect()->route('vacancies.index')
            ->with('success', 'Vacante eliminada correctamente.');
    }

    /**
     * Close an open vacancy.
     */
    public function close(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->status !== VacancyStatus::OPEN) {
            abort(403, 'Solo se pueden cerrar vacantes abiertas.');
        }

        $vacancy->update(['status' => VacancyStatus::CLOSED]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacante cerrada correctamente.');
    }

    /**
     * Cancel an open vacancy.
     */
    public function cancel(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->status !== VacancyStatus::OPEN) {
            abort(403, 'Solo se pueden cancelar vacantes abiertas.');
        }

        $vacancy->update(['status' => VacancyStatus::CANCELLED]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacante cancelada correctamente.');
    }

    /**
     * Reopen a closed vacancy.
     */
    public function reopen(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->status !== VacancyStatus::CLOSED) {
            abort(403, 'Solo se pueden reabrir vacantes cerradas.');
        }

        $vacancy->update(['status' => VacancyStatus::OPEN]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacante reabierta correctamente.');
    }
}
