<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\Vacancy;

require_once __DIR__.'/Helpers.php';

uses()->group('report');

describe('RPT-003: Score Averages per Test', function () {
    test('calculates average score per test excluding pending applicants', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicants = Applicant::factory()->count(10)->create();
        foreach ($applicants as $index => $applicant) {
            reportAttachApplicantToVacancy($applicant, $vacancy, $index < 6 ? 'evaluated' : 'registered');
            if ($index < 6) {
                reportRecordScore($test, $applicant, $vacancy, $admin, 50 + $index * 5);
            }
        }

        $response = $this->actingAs($admin)->get("/reports/averages?vacancy_id={$vacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('reports/Index')
            ->has('averages.data', 1)
            ->where('averages.data.0.scored_count', 6)
        );
    });
});
