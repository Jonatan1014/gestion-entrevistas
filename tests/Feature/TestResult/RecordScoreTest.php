<?php

use App\Models\Applicant;
use App\Models\Test;
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

function createEntrevistador(): User
{
    $entrevistador = User::factory()->create();
    $entrevistador->assignRole('Entrevistador');

    return $entrevistador;
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
// RES-001 — Record Score per Applicant per Test
// ============================================================================

describe('RES-001: Record Score per Applicant per Test', function () {
    test('admin can record a numeric score for an applicant', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 75, 'observations' => 'Good performance']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('test_results', [
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'evaluator_id' => $admin->id,
            'score' => 75,
        ]);
    });

    test('entrevistador can record a numeric score with permission', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($entrevistador)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 65]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('test_results', [
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'score' => 65,
        ]);
    });

    test('score above max_score is rejected', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 110]
        );

        $response->assertSessionHasErrors(['score']);
        $this->assertDatabaseCount('test_results', 0);
    });

    test('negative score is rejected', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => -5]
        );

        $response->assertSessionHasErrors(['score']);
        $this->assertDatabaseCount('test_results', 0);
    });
});
