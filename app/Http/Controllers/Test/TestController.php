<?php

namespace App\Http\Controllers\Test;

use App\Enums\TestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Test\StoreTestRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    /**
     * Display a listing of tests.
     */
    public function index(): Response
    {
        $tests = Test::orderBy('created_at', 'desc')->get();

        return Inertia::render('tests/Index', [
            'tests' => $tests,
            'canManageTests' => auth()->user()->can('manage-tests'),
        ]);
    }

    /**
     * Show the form for creating a new test.
     */
    public function create(): Response
    {
        return Inertia::render('tests/Create');
    }

    /**
     * Store a newly created test.
     */
    public function store(StoreTestRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $test = Test::create($validated);

        if ($test->type === TestType::MULTIPLE_CHOICE && $request->has('questions')) {
            $this->syncQuestions($test, $request->input('questions', []));
        }

        return redirect()->route('tests.show', $test)
            ->with('success', 'Prueba creada correctamente.');
    }

    /**
     * Display the specified test.
     */
    public function show(Test $test): Response
    {
        $test->load('questions');

        return Inertia::render('tests/Show', [
            'test' => $test,
            'canManageTests' => auth()->user()->can('manage-tests'),
        ]);
    }

    /**
     * Show the form for editing the specified test.
     */
    public function edit(Test $test): Response
    {
        $test->load('questions');

        return Inertia::render('tests/Edit', [
            'test' => $test,
        ]);
    }

    /**
     * Update the specified test.
     */
    public function update(UpdateTestRequest $request, Test $test): RedirectResponse
    {
        $validated = $request->validated();
        $test->update($validated);

        if ($test->type === TestType::MULTIPLE_CHOICE && $request->has('questions')) {
            $this->syncQuestions($test, $request->input('questions', []));
        }

        return redirect()->route('tests.show', $test)
            ->with('success', 'Prueba actualizada correctamente.');
    }

    /**
     * Remove the specified test (soft delete).
     */
    public function destroy(Test $test): RedirectResponse
    {
        $test->questions()->delete();
        $test->delete();

        return redirect()->route('tests.index')
            ->with('success', 'Prueba eliminada correctamente.');
    }

    /**
     * Sync the questions for a multiple choice test.
     *
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function syncQuestions(Test $test, array $questions): void
    {
        $validatedQuestions = collect($questions)->filter(function ($question) {
            return ! empty($question['question_text']);
        })->map(function ($question, int $index) {
            return [
                'question_text' => $question['question_text'],
                'options' => $question['options'] ?? [],
                'correct_answer_indices' => $question['correct_answer_indices'] ?? [],
                'points' => $question['points'] ?? 0,
                'order' => $question['order'] ?? ($index + 1),
            ];
        })->values()->all();

        $test->questions()->delete();

        foreach ($validatedQuestions as $question) {
            $test->questions()->create($question);
        }
    }
}
