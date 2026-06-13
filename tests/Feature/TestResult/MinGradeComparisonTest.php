<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function attachApplicantToVacancy(Applicant $applicant, Vacancy $vacancy): void
{
    $vacancy->applicants()->attach($applicant->id, ['status' => 'registered']);
}

// ============================================================================
// RES-005 — Minimum Grade Comparison
// ============================================================================

describe('RES-005: Minimum Grade Comparison', function () {
    test('meets requirement is shown when weighted average is above min_grade', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['min_grade' => 70]);
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        TestResult::factory()->create([
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 84,
        ]);

        $response = $this->actingAs($admin)->get(route('vacancies.results.index', $vacancy));

        $response->assertInertia(fn ($page) => $page
            ->where('applicants.0.weighted_average.meets_min_grade', true)
        );
    });

    test('below threshold is shown when weighted average is under min_grade', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['min_grade' => 70]);
        $applicant = Applicant::factory()->create();
        $test = Test::factory()->create(['type' => 'numeric', 'max_score' => 100]);
        $vacancy->tests()->attach($test->id, ['weight' => 100]);
        attachApplicantToVacancy($applicant, $vacancy);

        TestResult::factory()->create([
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'score' => 65,
        ]);

        $response = $this->actingAs($admin)->get(route('vacancies.results.index', $vacancy));

        $response->assertInertia(fn ($page) => $page
            ->where('applicants.0.weighted_average.meets_min_grade', false)
        );
    });
});
