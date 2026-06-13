<?php

use App\Enums\VacancyApplicantStatus;
use App\Enums\VacancyStatus;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    return $admin;
}

function createEntrevistador(): User
{
    $entrevistador = User::factory()->create();
    $entrevistador->assignRole('Entrevistador');

    return $entrevistador;
}

function seedRoles(): void
{
    (new RoleSeeder)->run();
}

// ============================================================================
// APP-003 — Multi-Vacancy Association
// ============================================================================

describe('APP-003: Multi-Vacancy Association', function () {
    test('applicant can be associated to two vacancies with registered status', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();
        $vacancyA = Vacancy::factory()->create(['status' => VacancyStatus::OPEN]);
        $vacancyB = Vacancy::factory()->create(['status' => VacancyStatus::OPEN]);

        $responseA = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/vacancies/{$vacancyA->id}/associate"
        );
        $responseA->assertRedirect();

        $responseB = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/vacancies/{$vacancyB->id}/associate"
        );
        $responseB->assertRedirect();

        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancyA->id,
            'applicant_id' => $applicant->id,
            'status' => VacancyApplicantStatus::REGISTERED->value,
        ]);

        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancyB->id,
            'applicant_id' => $applicant->id,
            'status' => VacancyApplicantStatus::REGISTERED->value,
        ]);
    });

    test('status change affects only one vacancy', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();
        $vacancyA = Vacancy::factory()->create(['status' => VacancyStatus::OPEN]);
        $vacancyB = Vacancy::factory()->create(['status' => VacancyStatus::OPEN]);

        $applicant->vacancies()->attach($vacancyA, ['status' => VacancyApplicantStatus::REGISTERED]);
        $applicant->vacancies()->attach($vacancyB, ['status' => VacancyApplicantStatus::IN_INTERVIEW]);

        $response = $this->actingAs($entrevistador)->put(
            "/applicants/{$applicant->id}/vacancies/{$vacancyA->id}/status",
            ['status' => VacancyApplicantStatus::APT->value]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancyA->id,
            'applicant_id' => $applicant->id,
            'status' => VacancyApplicantStatus::APT->value,
        ]);

        $this->assertDatabaseHas('vacancy_applicant', [
            'vacancy_id' => $vacancyB->id,
            'applicant_id' => $applicant->id,
            'status' => VacancyApplicantStatus::IN_INTERVIEW->value,
        ]);
    });

    test('same applicant cannot be associated to the same vacancy twice', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::OPEN]);

        $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/vacancies/{$vacancy->id}/associate"
        );

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/vacancies/{$vacancy->id}/associate"
        );

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('vacancy_applicant', 1);
    });
});
