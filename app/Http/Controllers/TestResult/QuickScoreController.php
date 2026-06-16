<?php

namespace App\Http\Controllers\TestResult;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuickScoreController extends Controller
{
    /**
     * Show the quick score recording form.
     */
    public function create(Request $request): Response
    {
        $vacancies = Vacancy::where('status', 'open')
            ->orderBy('position')
            ->get(['id', 'position', 'location']);

        return Inertia::render('tests/QuickScore', [
            'vacancies' => $vacancies,
            'preselected' => [
                'vacancy_id' => $request->input('vacancy_id'),
                'applicant_id' => $request->input('applicant_id'),
                'test_id' => $request->input('test_id'),
            ],
        ]);
    }

    /**
     * Store a new test result.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vacancy_id' => ['required', 'exists:vacancies,id'],
            'applicant_id' => ['required', 'exists:applicants,id'],
            'test_id' => ['required', 'exists:tests,id'],
            'score' => ['required', 'numeric', 'min:0'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);

        // Validate max score
        $test = Test::findOrFail($validated['test_id']);
        if ($validated['score'] > $test->max_score) {
            return back()->withErrors(['score' => "El puntaje no puede superar el máximo de {$test->max_score}."])->withInput();
        }

        TestResult::create([
            'test_id' => $validated['test_id'],
            'applicant_id' => $validated['applicant_id'],
            'vacancy_id' => $validated['vacancy_id'],
            'evaluator_id' => $request->user()->id,
            'score' => $validated['score'],
            'observations' => $validated['observations'] ?? null,
        ]);

        return redirect()->route('quick-score.create')
            ->with('success', 'Puntaje registrado correctamente.');
    }

    /**
     * Get applicants for a vacancy (JSON).
     */
    public function applicants(Vacancy $vacancy)
    {
        return $vacancy->applicants()
            ->orderBy('name')
            ->get(['applicants.id', 'applicants.name', 'applicants.email']);
    }

    /**
     * Get tests for a vacancy (JSON).
     */
    public function tests(Vacancy $vacancy)
    {
        return $vacancy->tests()
            ->orderBy('name')
            ->get(['tests.id', 'tests.name', 'tests.type', 'tests.max_score']);
    }

    /**
     * Get previous scores for applicant+test+vacancy (JSON).
     */
    public function history(Vacancy $vacancy, Applicant $applicant, Test $test)
    {
        return TestResult::where('vacancy_id', $vacancy->id)
            ->where('applicant_id', $applicant->id)
            ->where('test_id', $test->id)
            ->with('evaluator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'score' => (float) $r->score,
                'observations' => $r->observations,
                'evaluator_name' => $r->evaluator->name,
                'created_at' => $r->created_at->format('d/m/Y H:i'),
            ]);
    }
}
