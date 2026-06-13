<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\Vacancy;

require_once __DIR__.'/Helpers.php';

uses()->group('export');

describe('RPT-005: PDF and Excel Export', function () {
    beforeEach(function () {
        reportSeedRoles();
    });

    test('comparison pdf export returns pdf content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'evaluated');
        reportRecordScore($test, $applicant, $vacancy, $admin, 80);

        $response = $this->actingAs($admin)->get("/reports/comparison/pdf?vacancy_id={$vacancy->id}");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    });

    test('comparison excel export returns xlsx content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'evaluated');
        reportRecordScore($test, $applicant, $vacancy, $admin, 80);

        $response = $this->actingAs($admin)->get("/reports/comparison/excel?vacancy_id={$vacancy->id}");

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    });

    test('pipeline pdf export returns pdf content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'registered');

        $response = $this->actingAs($admin)->get("/reports/pipeline/pdf?vacancy_id={$vacancy->id}");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    });

    test('pipeline excel export returns xlsx content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'registered');

        $response = $this->actingAs($admin)->get("/reports/pipeline/excel?vacancy_id={$vacancy->id}");

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    });

    test('averages pdf export returns pdf content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'evaluated');
        reportRecordScore($test, $applicant, $vacancy, $admin, 80);

        $response = $this->actingAs($admin)->get("/reports/averages/pdf?vacancy_id={$vacancy->id}");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    });

    test('averages excel export returns xlsx content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicant, $vacancy, 'evaluated');
        reportRecordScore($test, $applicant, $vacancy, $admin, 80);

        $response = $this->actingAs($admin)->get("/reports/averages/excel?vacancy_id={$vacancy->id}");

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    });

    test('interviews pdf export returns pdf content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = reportCreateEntrevistador();
        reportAttachApplicantToVacancy($applicant, $vacancy);
        reportCreateCompletedInterview($vacancy, $applicant, $interviewer);

        $response = $this->actingAs($admin)->get('/reports/interviews/pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    });

    test('interviews excel export returns xlsx content type', function () {
        $admin = reportCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = reportCreateEntrevistador();
        reportAttachApplicantToVacancy($applicant, $vacancy);
        reportCreateCompletedInterview($vacancy, $applicant, $interviewer);

        $response = $this->actingAs($admin)->get('/reports/interviews/excel');

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    });
});
