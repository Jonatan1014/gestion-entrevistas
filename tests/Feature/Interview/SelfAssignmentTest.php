<?php

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Enums\VacancyApplicantStatus;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

// ============================================================================
// INT-003 — Interviewer Self-Assignment
// ============================================================================

describe('INT-003: Interviewer Self-Assignment', function () {
    test('interviewer can self-assign to an applicant', function () {
        intSeedRoles();
        $entrevistador = intCreateEntrevistador();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        intAttachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($entrevistador)->post('/interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $entrevistador->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'type' => InterviewType::VIRTUAL->value,
            'link' => 'https://meet.example/self',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $entrevistador->id,
        ]);
        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'status' => VacancyApplicantStatus::IN_INTERVIEW->value,
        ]);
    });

    test('assigned applicant appears in interviewer list', function () {
        intSeedRoles();
        $entrevistador = intCreateEntrevistador();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create(['name' => 'Jane Doe']);
        intAttachApplicantToVacancy($applicant, $vacancy);
        Interview::factory()->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $entrevistador->id,
            'status' => InterviewStatus::PENDING,
        ]);

        $response = $this->actingAs($entrevistador)->get('/interviews');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('interviews.data', 1)
            ->where('interviews.data.0.applicant.name', 'Jane Doe')
            ->where('interviews.data.0.interviewer.id', $entrevistador->id)
        );
    });
});
