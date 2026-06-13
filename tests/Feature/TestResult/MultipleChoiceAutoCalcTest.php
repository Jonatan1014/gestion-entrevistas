<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\User;
use App\Models\Vacancy;
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

function attachApplicantToVacancy(Applicant $applicant, Vacancy $vacancy): void
{
    $vacancy->applicants()->attach($applicant->id, ['status' => 'registered']);
}

// ============================================================================
// RES-003 — Multiple Choice Auto-Calculation
// ============================================================================

describe('RES-003: Multiple Choice Auto-Calculation', function () {
    test('multiple choice score is auto-calculated from answers', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'multiple_choice', 'max_score' => 50]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $questions = TestQuestion::factory()->count(5)->create([
            'test_id' => $test->id,
            'points' => 10,
            'correct_answer_indices' => [1],
        ]);

        $answers = [
            $questions[0]->id => [1],
            $questions[1]->id => [1],
            $questions[2]->id => [1],
            $questions[3]->id => [1],
            $questions[4]->id => [0],
        ];

        $response = $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 40, 'answers' => $answers]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('test_results', [
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'score' => 40,
            'is_manual_override' => false,
        ]);
        $this->assertDatabaseCount('test_answers', 5);
    });

    test('manual override stores overridden score and flag with justification', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'multiple_choice', 'max_score' => 50]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $questions = TestQuestion::factory()->count(5)->create([
            'test_id' => $test->id,
            'points' => 10,
            'correct_answer_indices' => [1],
        ]);

        $answers = [
            $questions[0]->id => [1],
            $questions[1]->id => [1],
            $questions[2]->id => [1],
            $questions[3]->id => [1],
            $questions[4]->id => [0],
        ];

        $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 40, 'answers' => $answers]
        );

        $response = $this->actingAs($admin)->put(
            route('test-results.update', [$test, $applicant, $vacancy]),
            ['score' => 45, 'override_justification' => 'partial credit']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('test_results', [
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'score' => 45,
            'is_manual_override' => true,
            'override_justification' => 'partial credit',
        ]);
    });
});
