<?php

use App\Models\Applicant;
use App\Models\User;
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
// APP-006 — Interviewer Applicant Visibility
// ============================================================================

describe('APP-006: Interviewer Applicant Visibility', function () {
    test('Entrevistador sees all applicants by default', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        Applicant::factory()->count(3)->create();
        Applicant::factory()->count(2)->create(['created_by' => $entrevistador->id]);

        $response = $this->actingAs($entrevistador)->get('/applicants');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('applicants')
            ->where('applicants.data', fn ($applicants) => count($applicants) === 5)
        );
    });

    test('filter assigned to me shows only applicants created by current user', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        Applicant::factory()->count(3)->create();
        Applicant::factory()->count(2)->create(['created_by' => $entrevistador->id]);

        $response = $this->actingAs($entrevistador)->get('/applicants?assigned_to_me=1');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('applicants')
            ->where('applicants.data', fn ($applicants) => count($applicants) === 2)
        );
    });

    test('search filters by name', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        Applicant::factory()->create(['name' => 'Juan Pérez']);
        Applicant::factory()->create(['name' => 'María García']);

        $response = $this->actingAs($entrevistador)->get('/applicants?search=Juan');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('applicants')
            ->where('applicants.data', fn ($applicants) => count($applicants) === 1)
        );
    });

    test('search filters by email', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        Applicant::factory()->create(['email' => 'juan@email.com']);
        Applicant::factory()->create(['email' => 'maria@email.com']);

        $response = $this->actingAs($entrevistador)->get('/applicants?search=juan@email.com');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('applicants')
            ->where('applicants.data', fn ($applicants) => count($applicants) === 1)
        );
    });

    test('unauthenticated user cannot access applicants index', function () {
        $response = $this->get('/applicants');
        $response->assertRedirect('/login');
    });
});
