<?php

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    return $admin;
}

function seedRoles(): void
{
    (new RoleSeeder)->run();
}

// ============================================================================
// TST-004 — Multiple Choice Test Type
// ============================================================================

describe('TST-004: Multiple Choice Test Type', function () {
    test('Admin can create a multiple choice test with questions', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/tests', [
            'name' => 'Multiple Choice Exam',
            'description' => 'Technical quiz',
            'type' => 'multiple_choice',
            'max_score' => 30,
            'evaluation_criteria' => 'Each correct answer adds 10 points',
            'questions' => [
                [
                    'question_text' => 'What is 2 + 2?',
                    'options' => ['3', '4', '5', '6'],
                    'correct_answer_indices' => [1],
                    'points' => 10,
                    'order' => 1,
                ],
                [
                    'question_text' => 'What is the capital of France?',
                    'options' => ['Madrid', 'Berlin', 'Paris', 'Rome'],
                    'correct_answer_indices' => [2],
                    'points' => 10,
                    'order' => 2,
                ],
                [
                    'question_text' => 'Which of these are prime numbers?',
                    'options' => ['2', '3', '4', '5'],
                    'correct_answer_indices' => [0, 1, 3],
                    'points' => 10,
                    'order' => 3,
                ],
            ],
        ]);

        $response->assertRedirectContains('/tests/');

        $this->assertDatabaseHas('tests', [
            'name' => 'Multiple Choice Exam',
            'type' => 'multiple_choice',
            'max_score' => 30,
        ]);

        $this->assertDatabaseCount('test_questions', 3);

        $test = Test::where('name', 'Multiple Choice Exam')->first();
        expect($test->questions)->toHaveCount(3);
        expect($test->questions->first()->options)->toBeArray();
        expect($test->questions->first()->correct_answer_indices)->toBeArray();
    });

    test('multiple choice auto-calculates score based on correct answers', function () {
        seedRoles();
        createAdmin();

        $test = Test::factory()->create([
            'type' => 'multiple_choice',
            'max_score' => 30,
        ]);

        $questions = TestQuestion::factory()->count(3)->create([
            'test_id' => $test->id,
            'points' => 10,
            'correct_answer_indices' => [1],
        ]);

        $answers = [
            $questions[0]->id => [1], // correct
            $questions[1]->id => [1], // correct
            $questions[2]->id => [0], // incorrect
        ];

        $score = $test->calculateMultipleChoiceScore($answers);

        expect($score)->toBe(20.0);
    });

    test('manual override of auto-calculated score stores overridden score with flag', function () {
        seedRoles();
        createAdmin();

        $test = Test::factory()->create([
            'type' => 'multiple_choice',
            'max_score' => 30,
        ]);

        $result = $test->applyManualOverride(
            calculatedScore: 20,
            manualScore: 25,
            isManualOverride: true,
        );

        expect($result['score'])->toBe(25.0);
        expect($result['is_manual_override'])->toBeTrue();
    });
});
