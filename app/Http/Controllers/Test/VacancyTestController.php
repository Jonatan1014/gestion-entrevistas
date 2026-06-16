<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Http\Requests\Test\AttachTestRequest;
use App\Models\Test;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VacancyTestController extends Controller
{
    /**
     * Display a listing of tests associated with the vacancy.
     */
    public function index(Vacancy $vacancy): Response
    {
        $vacancy->load('tests.questions');

        $attachedIds = $vacancy->tests->pluck('id');
        $availableTests = Test::whereNotIn('id', $attachedIds)->orderBy('name')->get();

        return Inertia::render('vacancies/tests/Index', [
            'vacancy' => $vacancy,
            'tests' => $vacancy->tests,
            'availableTests' => $availableTests,
            'canManageTests' => auth()->user()->can('manage-tests'),
        ]);
    }

    /**
     * Attach a test to a vacancy with a weight.
     */
    public function attach(AttachTestRequest $request, Vacancy $vacancy): RedirectResponse
    {
        $validated = $request->validated();

        $vacancy->tests()->attach($validated['test_id'], [
            'weight' => $validated['weight'],
        ]);

        return redirect()->route('vacancies.tests.index', $vacancy)
            ->with('success', 'Prueba asociada correctamente.');
    }

    /**
     * Update the weight for an attached test.
     */
    public function updateWeight(Request $request, Vacancy $vacancy, Test $test): RedirectResponse
    {
        $validated = $request->validate([
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $newWeight = (float) $validated['weight'];

        $currentWeight = $vacancy->tests()
            ->where('tests.id', '!=', $test->id)
            ->sum('weight');

        if ($currentWeight + $newWeight > 100) {
            throw ValidationException::withMessages([
                'weight' => 'The total weight for this vacancy cannot exceed 100%.',
            ]);
        }

        $vacancy->tests()->updateExistingPivot($test->id, [
            'weight' => $newWeight,
        ]);

        return redirect()->route('vacancies.tests.index', $vacancy)
            ->with('success', 'Peso actualizado correctamente.');
    }

    /**
     * Detach a test from a vacancy.
     */
    public function detach(Vacancy $vacancy, Test $test): RedirectResponse
    {
        $vacancy->tests()->detach($test->id);

        return redirect()->route('vacancies.tests.index', $vacancy)
            ->with('success', 'Prueba desasociada correctamente.');
    }
}
