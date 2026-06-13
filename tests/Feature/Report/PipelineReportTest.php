<?php

use App\Models\Applicant;
use App\Models\Vacancy;

require_once __DIR__.'/Helpers.php';

uses()->group('report');

describe('RPT-004: Selection Pipeline View', function () {
    test('pipeline shows applicant counts per stage', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $statuses = ['registered', 'in_interview', 'evaluated', 'apt', 'no_apt'];

        foreach ($statuses as $status) {
            $applicant = Applicant::factory()->create();
            reportAttachApplicantToVacancy($applicant, $vacancy, $status);
        }

        $response = $this->actingAs($admin)->get("/reports/pipeline?vacancy_id={$vacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('reports/Index')
            ->has('pipeline.data', 5)
        );
    });

    test('pipeline updates counts when applicant status changes', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'in_interview');

        $before = $this->actingAs($admin)->get("/reports/pipeline?vacancy_id={$vacancy->id}");
        $before->assertInertia(fn ($page) => $page
            ->has('pipeline.data', 1)
            ->where('pipeline.data.0.status', 'in_interview')
            ->where('pipeline.data.0.count', 1)
        );

        $applicant->vacancies()->updateExistingPivot($vacancy->id, ['status' => 'evaluated']);

        $after = $this->actingAs($admin)->get("/reports/pipeline?vacancy_id={$vacancy->id}");
        $after->assertInertia(fn ($page) => $page
            ->has('pipeline.data', 1)
            ->where('pipeline.data.0.status', 'evaluated')
            ->where('pipeline.data.0.count', 1)
        );
    });
});
