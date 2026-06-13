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
// APP-001 — Applicant Registration
// ============================================================================

describe('APP-001: Applicant Registration', function () {
    test('Entrevistador can register an applicant', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->post('/applicants', [
            'name' => 'Juan Pérez',
            'phone' => '+5491112345678',
            'email' => 'juan@email.com',
            'address' => 'Buenos Aires',
        ]);

        $response->assertRedirectContains('/applicants/');
        $this->assertDatabaseHas('applicants', [
            'name' => 'Juan Pérez',
            'phone' => '+5491112345678',
            'email' => 'juan@email.com',
            'address' => 'Buenos Aires',
            'created_by' => $entrevistador->id,
            'is_blocked' => false,
        ]);
    });

    test('Admin can register an applicant', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/applicants', [
            'name' => 'Admin Applicant',
            'phone' => '+5491198765432',
            'email' => 'admin.applicant@email.com',
            'address' => 'Córdoba',
        ]);

        $response->assertRedirectContains('/applicants/');
        $this->assertDatabaseHas('applicants', [
            'email' => 'admin.applicant@email.com',
            'created_by' => $admin->id,
        ]);
    });

    test('duplicate email is rejected', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        Applicant::factory()->create(['email' => 'juan@email.com']);

        $response = $this->actingAs($entrevistador)->post('/applicants', [
            'name' => 'Juan Pérez Clone',
            'phone' => '+5491112345678',
            'email' => 'juan@email.com',
            'address' => 'Buenos Aires',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('applicants', 1);
    });

    test('name is required', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->post('/applicants', [
            'phone' => '+5491112345678',
            'email' => 'juan@email.com',
            'address' => 'Buenos Aires',
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('phone is required', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->post('/applicants', [
            'name' => 'Juan Pérez',
            'email' => 'juan@email.com',
            'address' => 'Buenos Aires',
        ]);

        $response->assertSessionHasErrors(['phone']);
    });

    test('email is required and must be valid', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->post('/applicants', [
            'name' => 'Juan Pérez',
            'phone' => '+5491112345678',
            'email' => 'not-an-email',
            'address' => 'Buenos Aires',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('unauthenticated user cannot register applicant', function () {
        $response = $this->post('/applicants', [
            'name' => 'Juan Pérez',
            'phone' => '+5491112345678',
            'email' => 'juan@email.com',
            'address' => 'Buenos Aires',
        ]);

        $response->assertRedirect('/login');
    });
});
