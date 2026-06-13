<?php

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
// APP-004 — Applicant Blocking
// ============================================================================

describe('APP-004: Applicant Blocking', function () {
    test('Admin can block an applicant with a reason', function () {
        seedRoles();
        $admin = createAdmin();
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($admin)->post(
            "/applicants/{$applicant->id}/block",
            ['block_reason' => 'Falsified documents']
        );

        $response->assertRedirect();

        $applicant->refresh();
        expect($applicant->is_blocked)->toBeTrue();
        expect($applicant->block_reason)->toBe('Falsified documents');
        expect($applicant->blocked_by)->toBe($admin->id);
        expect($applicant->blocked_at)->not->toBeNull();
    });

    test('block reason is required', function () {
        seedRoles();
        $admin = createAdmin();
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($admin)->post(
            "/applicants/{$applicant->id}/block",
            []
        );

        $response->assertSessionHasErrors(['block_reason']);
    });

    test('Entrevistador cannot block an applicant', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/block",
            ['block_reason' => 'Should not work']
        );

        $response->assertForbidden();

        $applicant->refresh();
        expect($applicant->is_blocked)->toBeFalse();
    });

    test('Admin can unblock an applicant', function () {
        seedRoles();
        $admin = createAdmin();
        $applicant = Applicant::factory()->create([
            'is_blocked' => true,
            'block_reason' => 'Falsified documents',
            'blocked_by' => $admin->id,
            'blocked_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post("/applicants/{$applicant->id}/unblock");

        $response->assertRedirect();

        $applicant->refresh();
        expect($applicant->is_blocked)->toBeFalse();
        expect($applicant->block_reason)->toBeNull();
        expect($applicant->blocked_by)->toBeNull();
        expect($applicant->blocked_at)->toBeNull();
    });

    test('blocked applicant cannot be associated to a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create([
            'is_blocked' => true,
            'block_reason' => 'Falsified documents',
            'blocked_by' => $admin->id,
            'blocked_at' => now(),
        ]);
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::OPEN]);

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/vacancies/{$vacancy->id}/associate"
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('vacancy_applicant', 0);
    });
});
