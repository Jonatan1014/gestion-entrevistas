<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\User;
use App\Models\Vacancy;

require_once __DIR__.'/Helpers.php';

uses()->group('report');

describe('RPT-001: Candidate Comparison per Vacancy', function () {
    test('admin can view comparison report with all applicants and weighted averages', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicants = Applicant::factory()->count(5)->create();
        foreach ($applicants as $index => $applicant) {
            reportAttachApplicantToVacancy($applicant, $vacancy, $index === 4 ? 'registered' : 'evaluated');
            if ($index < 4) {
                reportRecordScore($test, $applicant, $vacancy, $admin, 60 + $index * 10);
            }
        }

        $response = $this->actingAs($admin)->get("/reports/comparison?vacancy_id={$vacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('reports/Index')
            ->where('activeTab', 'comparison')
            ->has('comparison.data', 5)
        );
    });

    test('pending applicants show N/A in comparison report', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'registered');

        $response = $this->actingAs($admin)->get("/reports/comparison?vacancy_id={$vacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('comparison.data', 1)
            ->where('comparison.data.0.score', null)
        );
    });

    test('user without view-reports permission cannot access comparison report', function () {
        reportSeedRoles();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports/comparison');

        $response->assertForbidden();
    });
});
