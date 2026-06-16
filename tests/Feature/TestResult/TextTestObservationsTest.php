<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// RES-002 — Text Test Observations
// ============================================================================

describe('RES-002: Text Test Observations', function () {
    test('text test score and observations are persisted', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'text', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $observations = 'Demonstrates strong analytical thinking';

        $response = $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 80, 'observations' => $observations]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('test_results', [
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 80,
            'observations' => $observations,
        ]);
    });

    test('observations are optional for text tests', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'text', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post(
            route('test-results.store', [$test, $applicant, $vacancy]),
            ['score' => 70]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('test_results', [
            'test_id' => $test->id,
            'score' => 70,
            'observations' => null,
        ]);
    });
});
