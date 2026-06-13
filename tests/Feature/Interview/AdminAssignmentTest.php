<?php

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

// ============================================================================
// INT-004 — Admin Interviewer Assignment
// ============================================================================

describe('INT-004: Admin Interviewer Assignment', function () {
    test('admin can assign interviewer B to an applicant', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewerB = intCreateEntrevistador();
        intAttachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post('/interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewerB->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'type' => InterviewType::PRESENCIAL->value,
            'location_notes' => 'Room A',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewerB->id,
        ]);
    });

    test('admin can reassign interviewer from A to B', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewerA = intCreateEntrevistador();
        $interviewerB = intCreateEntrevistador();
        intAttachApplicantToVacancy($applicant, $vacancy);
        Interview::factory()->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewerA->id,
            'status' => InterviewStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->post('/interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewerB->id,
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
            'type' => InterviewType::VIRTUAL->value,
            'link' => 'https://meet.example/reassign',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewerB->id,
        ]);
    });
});
