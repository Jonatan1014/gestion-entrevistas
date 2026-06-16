<?php

namespace App\Http\Controllers\TestResult;

use App\Enums\TestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\TestResult\OverrideScoreRequest;
use App\Http\Requests\TestResult\RecordScoreRequest;
use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\Vacancy;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TestResultController extends Controller
{
    /**
     * Show the form for recording a new test result.
     */
    public function create(Test $test, Applicant $applicant, Vacancy $vacancy): Response
    {
        $test->load('questions');

        $existingResult = TestResult::with('answers')
            ->where('test_id', $test->id)
            ->where('applicant_id', $applicant->id)
            ->where('vacancy_id', $vacancy->id)
            ->first();

        return Inertia::render('tests/results/Create', [
            'test' => $test,
            'applicant' => $applicant,
            'vacancy' => $vacancy,
            'existingResult' => $existingResult,
        ]);
    }

    /**
     * Store a newly recorded test result.
     */
    public function store(RecordScoreRequest $request, Test $test, Applicant $applicant, Vacancy $vacancy): RedirectResponse
    {
        $validated = $request->validated();

        $score = (float) $validated['score'];
        $isManualOverride = false;
        $justification = null;

        if ($test->type === TestType::MULTIPLE_CHOICE) {
            $calculated = ScoringService::calculateMultipleChoiceScore(
                $test->load('questions'),
                $validated['answers'] ?? []
            );

            $override = ScoringService::applyManualOverride(
                $calculated['score'],
                $score,
                null
            );

            $score = $override['score'];
        }

        $result = TestResult::updateOrCreate(
            [
                'test_id' => $test->id,
                'applicant_id' => $applicant->id,
                'vacancy_id' => $vacancy->id,
            ],
            [
                'evaluator_id' => auth()->id(),
                'score' => $score,
                'observations' => $validated['observations'] ?? null,
                'is_manual_override' => $isManualOverride,
                'override_justification' => $justification,
            ]
        );

        if ($test->type === TestType::MULTIPLE_CHOICE) {
            $this->syncAnswers($result, $test, $validated['answers'] ?? []);
        }

        return redirect()->route('test-results.show', [$test, $applicant, $vacancy])
            ->with('success', 'Puntaje registrado correctamente.');
    }

    /**
     * Display the recorded test result.
     */
    public function show(Test $test, Applicant $applicant, Vacancy $vacancy): Response
    {
        $test->load('questions');

        $result = TestResult::with('answers.testQuestion')
            ->where('test_id', $test->id)
            ->where('applicant_id', $applicant->id)
            ->where('vacancy_id', $vacancy->id)
            ->firstOrFail();

        return Inertia::render('tests/results/Show', [
            'test' => $test,
            'applicant' => $applicant,
            'vacancy' => $vacancy,
            'result' => $result,
        ]);
    }

    /**
     * Apply a manual override to an existing result.
     */
    public function update(OverrideScoreRequest $request, Test $test, Applicant $applicant, Vacancy $vacancy): RedirectResponse
    {
        $validated = $request->validated();

        $result = TestResult::where('test_id', $test->id)
            ->where('applicant_id', $applicant->id)
            ->where('vacancy_id', $vacancy->id)
            ->firstOrFail();

        $override = ScoringService::applyManualOverride(
            $result->score,
            (float) $validated['score'],
            $validated['override_justification']
        );

        $result->update([
            'score' => $override['score'],
            'is_manual_override' => $override['is_manual_override'],
            'override_justification' => $override['override_justification'],
            'evaluator_id' => auth()->id(),
        ]);

        return redirect()->route('test-results.show', [$test, $applicant, $vacancy])
            ->with('success', 'Puntaje modificado correctamente.');
    }

    /**
     * Sync the answers for a multiple choice result.
     *
     * @param  array<int, array<int>>  $answers
     */
    private function syncAnswers(TestResult $result, Test $test, array $answers): void
    {
        $result->answers()->delete();

        foreach ($test->questions as $question) {
            $selected = $answers[$question->id] ?? [];
            sort($selected);

            $correct = $question->correct_answer_indices ?? [];
            sort($correct);

            $result->answers()->create([
                'test_question_id' => $question->id,
                'selected_indices' => $selected,
                'is_correct' => $selected === $correct,
            ]);
        }
    }
}
