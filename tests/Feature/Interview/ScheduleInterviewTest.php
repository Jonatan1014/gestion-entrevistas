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
// INT-001 — Schedule Interview
// ============================================================================

describe('INT-001: Schedule Interview', function () {
    test('user can schedule a virtual interview with a link', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = intCreateEntrevistador();
        intAttachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post('/interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'type' => InterviewType::VIRTUAL->value,
            'link' => 'https://meet.example/abc',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
            'type' => InterviewType::VIRTUAL->value,
            'link' => 'https://meet.example/abc',
            'status' => InterviewStatus::PENDING->value,
        ]);
    });

    test('user can schedule a presencial interview with location notes', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = intCreateEntrevistador();
        intAttachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post('/interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'type' => InterviewType::PRESENCIAL->value,
            'location_notes' => 'Room 3',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
            'type' => InterviewType::PRESENCIAL->value,
            'location_notes' => 'Room 3',
            'status' => InterviewStatus::PENDING->value,
        ]);
    });

    test('virtual interview without link is rejected', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $vacancy = Vacancy::factory()->create();
        $applicant = Applicant::factory()->create();
        $interviewer = intCreateEntrevistador();
        intAttachApplicantToVacancy($applicant, $vacancy);

        $response = $this->actingAs($admin)->post('/interviews', [
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'type' => InterviewType::VIRTUAL->value,
        ]);

        $response->assertSessionHasErrors(['link']);
        $this->assertDatabaseCount('interviews', 0);
    });
});

// ============================================================================
// Interview Model & Enum
// ============================================================================

describe('Interview Model & Enum', function () {
    test('InterviewType enum has expected values', function () {
        expect(InterviewType::VIRTUAL->value)->toBe('virtual');
        expect(InterviewType::PRESENCIAL->value)->toBe('presencial');
    });

    test('InterviewStatus enum has expected values', function () {
        expect(InterviewStatus::PENDING->value)->toBe('pending');
        expect(InterviewStatus::COMPLETED->value)->toBe('completed');
        expect(InterviewStatus::CANCELLED->value)->toBe('cancelled');
    });

    test('Interview model casts status and type to enums', function () {
        intSeedRoles();
        $interview = Interview::factory()->create([
            'type' => InterviewType::VIRTUAL,
            'status' => InterviewStatus::PENDING,
        ]);

        $fresh = Interview::find($interview->id);
        expect($fresh->type)->toBeInstanceOf(InterviewType::class);
        expect($fresh->status)->toBeInstanceOf(InterviewStatus::class);
        expect($fresh->type)->toBe(InterviewType::VIRTUAL);
        expect($fresh->status)->toBe(InterviewStatus::PENDING);
    });

    test('Interview belongs to vacancy, applicant and interviewer', function () {
        intSeedRoles();
        $interview = Interview::factory()->create();

        expect($interview->vacancy)->not->toBeNull();
        expect($interview->applicant)->not->toBeNull();
        expect($interview->interviewer)->not->toBeNull();
    });
});
