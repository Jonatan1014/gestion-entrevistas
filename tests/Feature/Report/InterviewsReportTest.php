<?php

use App\Models\Applicant;
use App\Models\Vacancy;

require_once __DIR__.'/Helpers.php';

uses()->group('report');

describe('RPT-002: Completed Interviews List with Filters', function () {
    test('admin can filter completed interviews by date range', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = reportCreateEntrevistador();
        reportAttachApplicantToVacancy($applicant, $vacancy);

        reportCreateCompletedInterview($vacancy, $applicant, $interviewer, now()->subDays(10));
        reportCreateCompletedInterview($vacancy, $applicant, $interviewer, now()->subDays(2));

        $response = $this->actingAs($admin)->get('/reports/interviews?'.http_build_query([
            'vacancy_id' => $vacancy->id,
            'date_from' => now()->subDays(5)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('reports/Index')
            ->has('interviews.data', 1)
        );
    });

    test('admin can filter completed interviews by interviewer', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = reportCreateEntrevistador();
        $otherInterviewer = reportCreateEntrevistador();
        reportAttachApplicantToVacancy($applicant, $vacancy);

        reportCreateCompletedInterview($vacancy, $applicant, $interviewer);
        reportCreateCompletedInterview($vacancy, $applicant, $otherInterviewer);

        $response = $this->actingAs($admin)->get("/reports/interviews?interviewer_id={$interviewer->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('interviews.data', 1)
            ->where('interviews.data.0.interviewer_id', $interviewer->id)
        );
    });

    test('admin can filter completed interviews by vacancy', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $targetVacancy = Vacancy::factory()->create();
        $otherVacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = reportCreateEntrevistador();
        reportAttachApplicantToVacancy($applicant, $targetVacancy);
        reportAttachApplicantToVacancy($applicant, $otherVacancy);

        reportCreateCompletedInterview($targetVacancy, $applicant, $interviewer);
        reportCreateCompletedInterview($otherVacancy, $applicant, $interviewer);

        $response = $this->actingAs($admin)->get("/reports/interviews?vacancy_id={$targetVacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('interviews.data', 1)
            ->where('interviews.data.0.vacancy_id', $targetVacancy->id)
        );
    });
});
