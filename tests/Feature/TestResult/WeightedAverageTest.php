<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestResult;
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
// RES-004 — Weighted Average Calculation
// ============================================================================

describe('RES-004: Weighted Average Calculation', function () {
    test('vacancy results page shows weighted average for an applicant', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['min_grade' => 70]);
        $applicant = Applicant::factory()->create();
        $testA = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $testB = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($testA->id, ['weight' => 60]);
        $vacancy->tests()->attach($testB->id, ['weight' => 40]);
        attachApplicantToVacancy($applicant, $vacancy);

        TestResult::factory()->create([
            'test_id' => $testA->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 80,
        ]);

        TestResult::factory()->create([
            'test_id' => $testB->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 90,
        ]);

        $response = $this->actingAs($admin)->get(route('vacancies.results.index', $vacancy));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('applicants', 1)
            ->where('applicants.0.weighted_average.score', fn ($score) => abs($score - 84.0) < 0.001)
        );
    });

    test('weighted average recalculates when a new score is recorded', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['min_grade' => 70]);
        $applicant = Applicant::factory()->create();
        $testA = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $testB = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($testA->id, ['weight' => 60]);
        $vacancy->tests()->attach($testB->id, ['weight' => 40]);
        attachApplicantToVacancy($applicant, $vacancy);

        TestResult::factory()->create([
            'test_id' => $testA->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 80,
        ]);

        $this->actingAs($admin)->post(
            route('test-results.store', [$testB, $applicant, $vacancy]),
            ['score' => 90]
        );

        $response = $this->actingAs($admin)->get(route('vacancies.results.index', $vacancy));

        $response->assertInertia(fn ($page) => $page
            ->where('applicants.0.weighted_average.score', fn ($score) => abs($score - 84.0) < 0.001)
        );
    });
});
