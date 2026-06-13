<?php

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

// ============================================================================
// INT-006 — Interview Listings
// ============================================================================

describe('INT-006: Interview Listings', function () {
    test('can filter interviews by interviewer', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = intCreateEntrevistador();
        $otherInterviewer = User::factory()->create();
        intAttachApplicantToVacancy($applicant, $vacancy);

        Interview::factory()->count(5)->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
        ]);

        Interview::factory()->count(2)->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $otherInterviewer->id,
        ]);

        $response = $this->actingAs($admin)->get("/interviews?interviewer_id={$interviewer->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('interviews.data', 5));
    });

    test('can filter interviews by vacancy', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $targetVacancy = Vacancy::factory()->create();
        $otherVacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = intCreateEntrevistador();
        intAttachApplicantToVacancy($applicant, $targetVacancy);
        intAttachApplicantToVacancy($applicant, $otherVacancy);

        Interview::factory()->count(3)->create([
            'vacancy_id' => $targetVacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
        ]);

        Interview::factory()->count(4)->create([
            'vacancy_id' => $otherVacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
        ]);

        $response = $this->actingAs($admin)->get("/interviews?vacancy_id={$targetVacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('interviews.data', 3));
    });

    test('can filter interviews by applicant', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $targetApplicant = Applicant::factory()->create();
        $otherApplicant = Applicant::factory()->create();
        $interviewer = intCreateEntrevistador();
        intAttachApplicantToVacancy($targetApplicant, $vacancy);
        intAttachApplicantToVacancy($otherApplicant, $vacancy);

        Interview::factory()->count(2)->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $targetApplicant->id,
            'interviewer_id' => $interviewer->id,
        ]);

        Interview::factory()->count(3)->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $otherApplicant->id,
            'interviewer_id' => $interviewer->id,
        ]);

        $response = $this->actingAs($admin)->get("/interviews?applicant_id={$targetApplicant->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('interviews.data', 2));
    });
});
