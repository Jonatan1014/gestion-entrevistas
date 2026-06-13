<?php

use App\Models\Applicant;
use App\Models\Test;
use App\Models\Vacancy;

require_once __DIR__.'/Helpers.php';

uses()->group('report');

describe('RPT-007: Entrevistador Report Scope', function () {
    test('entrevistador sees only own interviews in all report types', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $entrevistador = reportCreateEntrevistador();
        $otherInterviewer = reportCreateEntrevistador();

        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $scopedApplicant = Applicant::factory()->create();
        $otherApplicant = Applicant::factory()->create();
        reportAttachApplicantToVacancy($scopedApplicant, $vacancy, 'evaluated');
        reportAttachApplicantToVacancy($otherApplicant, $vacancy, 'evaluated');
        reportRecordScore($test, $scopedApplicant, $vacancy, $entrevistador, 80);
        reportRecordScore($test, $otherApplicant, $vacancy, $otherInterviewer, 90);

        reportCreateCompletedInterview($vacancy, $scopedApplicant, $entrevistador);
        reportCreateCompletedInterview($vacancy, $otherApplicant, $otherInterviewer);

        $comparison = $this->actingAs($entrevistador)->get("/reports/comparison?vacancy_id={$vacancy->id}");
        $comparison->assertInertia(fn ($page) => $page
            ->has('comparison.data', 1)
            ->where('comparison.data.0.applicant_id', $scopedApplicant->id)
        );

        $pipeline = $this->actingAs($entrevistador)->get("/reports/pipeline?vacancy_id={$vacancy->id}");
        $pipeline->assertInertia(fn ($page) => $page
            ->has('pipeline.data', 1)
            ->where('pipeline.data.0.count', 1)
        );

        $averages = $this->actingAs($entrevistador)->get("/reports/averages?vacancy_id={$vacancy->id}");
        $averages->assertInertia(fn ($page) => $page
            ->has('averages.data', 1)
            ->where('averages.data.0.scored_count', 1)
        );

        $interviews = $this->actingAs($entrevistador)->get('/reports/interviews');
        $interviews->assertInertia(fn ($page) => $page
            ->has('interviews.data', 1)
            ->where('interviews.data.0.interviewer_id', $entrevistador->id)
        );
    });

    test('admin sees all data across all interviewers and vacancies', function () {
        reportSeedRoles();
        $admin = reportCreateAdmin();
        $interviewerOne = reportCreateEntrevistador();
        $interviewerTwo = reportCreateEntrevistador();

        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create(['max_score' => 100]);
        reportAttachTestToVacancy($test, $vacancy, 100);

        $applicantOne = Applicant::factory()->create();
        $applicantTwo = Applicant::factory()->create();
        reportAttachApplicantToVacancy($applicantOne, $vacancy, 'evaluated');
        reportAttachApplicantToVacancy($applicantTwo, $vacancy, 'evaluated');
        reportRecordScore($test, $applicantOne, $vacancy, $interviewerOne, 80);
        reportRecordScore($test, $applicantTwo, $vacancy, $interviewerTwo, 90);

        reportCreateCompletedInterview($vacancy, $applicantOne, $interviewerOne);
        reportCreateCompletedInterview($vacancy, $applicantTwo, $interviewerTwo);

        $comparison = $this->actingAs($admin)->get("/reports/comparison?vacancy_id={$vacancy->id}");
        $comparison->assertInertia(fn ($page) => $page->has('comparison.data', 2));

        $pipeline = $this->actingAs($admin)->get("/reports/pipeline?vacancy_id={$vacancy->id}");
        $pipeline->assertInertia(fn ($page) => $page->has('pipeline.data', 1));

        $averages = $this->actingAs($admin)->get("/reports/averages?vacancy_id={$vacancy->id}");
        $averages->assertInertia(fn ($page) => $page
            ->has('averages.data', 1)
            ->where('averages.data.0.scored_count', 2)
        );

        $interviews = $this->actingAs($admin)->get('/reports/interviews');
        $interviews->assertInertia(fn ($page) => $page->has('interviews.data', 2));
    });
});
