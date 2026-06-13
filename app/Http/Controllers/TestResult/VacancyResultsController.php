<?php

namespace App\Http\Controllers\TestResult;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestResult\SetFinalStatusRequest;
use App\Models\Applicant;
use App\Models\Vacancy;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VacancyResultsController extends Controller
{
    /**
     * Display all applicants and their results for the vacancy.
     */
    public function index(Vacancy $vacancy): Response
    {
        $vacancy->load(['tests', 'applicants']);

        $applicants = $vacancy->applicants->map(function (Applicant $applicant) use ($vacancy) {
            $results = $applicant->testResults()
                ->where('vacancy_id', $vacancy->id)
                ->with('test')
                ->get();

            $testData = $results->map(function ($result) {
                return [
                    'score' => (float) $result->score,
                    'max_score' => (float) $result->test->max_score,
                    'test_name' => $result->test->name,
                ];
            });

            $weights = $results->map(function ($result) use ($vacancy) {
                $pivot = $vacancy->tests->firstWhere('id', $result->test_id);

                return (float) ($pivot?->pivot?->weight ?? 0);
            })->all();

            $weightedAverage = ScoringService::weightedAverage(
                $testData->all(),
                $weights,
                $vacancy->min_grade !== null ? (float) $vacancy->min_grade : null
            );

            return [
                'id' => $applicant->id,
                'name' => $applicant->name,
                'email' => $applicant->email,
                'status' => $applicant->pivot->status,
                'results' => $results,
                'weighted_average' => $weightedAverage,
            ];
        });

        return Inertia::render('vacancies/results/Index', [
            'vacancy' => $vacancy,
            'applicants' => $applicants,
            'tests' => $vacancy->tests,
        ]);
    }

    /**
     * Set the final status for an applicant on the vacancy.
     */
    public function setFinalStatus(SetFinalStatusRequest $request, Vacancy $vacancy, Applicant $applicant): RedirectResponse
    {
        $validated = $request->validated();

        $vacancy->applicants()->updateExistingPivot($applicant->id, [
            'status' => $validated['status'],
            'final_decided_by' => auth()->id(),
            'final_decided_at' => now(),
            'justification' => $validated['justification'] ?? null,
        ]);

        return redirect()->route('vacancies.results.index', $vacancy)
            ->with('success', 'Final status updated successfully.');
    }
}
