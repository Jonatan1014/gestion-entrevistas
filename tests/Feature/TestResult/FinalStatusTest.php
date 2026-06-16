<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// RES-006 — Human-Determined Final Status
// ============================================================================

describe('RES-006: Human-Determined Final Status', function () {
    test('admin can set final status to apt overriding calculated guidance', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['min_grade' => 70]);
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy, 'evaluated');

        TestResult::factory()->create([
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 65,
        ]);

        $response = $this->actingAs($admin)->put(
            route('vacancies.results.final-status', [$vacancy, $applicant]),
            ['status' => 'apt', 'justification' => 'Strong interview performance']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'status' => 'apt',
            'final_decided_by' => $admin->id,
            'justification' => 'Strong interview performance',
        ]);
    });

    test('admin can set final status to no_apt', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['min_grade' => 70]);
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy, 'evaluated');

        TestResult::factory()->create([
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 84,
        ]);

        $response = $this->actingAs($admin)->put(
            route('vacancies.results.final-status', [$vacancy, $applicant]),
            ['status' => 'no_apt', 'justification' => 'Missing required skills']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'status' => 'no_apt',
        ]);
    });

    test('final status is independent per vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $applicant = Applicant::factory()->create();

        $vacancyA = Vacancy::factory()->create();
        $testA = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancyA->tests()->attach($testA->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancyA, 'evaluated');

        $vacancyB = Vacancy::factory()->create();
        $testB = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancyB->tests()->attach($testB->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancyB, 'evaluated');

        $this->actingAs($admin)->put(
            route('vacancies.results.final-status', [$vacancyA, $applicant]),
            ['status' => 'apt', 'justification' => 'Good fit']
        );

        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancyA->id,
            'applicant_id' => $applicant->id,
            'status' => 'apt',
        ]);
        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancyB->id,
            'applicant_id' => $applicant->id,
            'status' => 'evaluated',
        ]);
    });
});
