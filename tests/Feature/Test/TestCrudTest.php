<?php

use App\Models\Test;
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
// TST-001 — Test CRUD
// ============================================================================

describe('TST-001: Test CRUD', function () {
    test('Admin can create a test', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/tests', [
            'name' => 'Technical Score',
            'description' => 'Technical evaluation',
            'type' => 'numeric',
            'max_score' => 100,
            'evaluation_criteria' => 'Score from 0 to 100',
        ]);

        $response->assertRedirectContains('/tests/');
        $this->assertDatabaseHas('tests', [
            'name' => 'Technical Score',
            'type' => 'numeric',
            'max_score' => 100,
        ]);
    });

    test('Admin can update a test', function () {
        seedRoles();
        $admin = createAdmin();
        $test = Test::factory()->create([
            'name' => 'Original Test',
            'type' => 'numeric',
            'max_score' => 100,
        ]);

        $response = $this->actingAs($admin)->put("/tests/{$test->id}", [
            'name' => 'Updated Test',
            'description' => 'Updated description',
            'type' => 'numeric',
            'max_score' => 50,
            'evaluation_criteria' => 'Updated criteria',
        ]);

        $response->assertRedirectContains('/tests/');
        $this->assertDatabaseHas('tests', [
            'id' => $test->id,
            'name' => 'Updated Test',
            'max_score' => 50,
        ]);
    });

    test('Admin can delete a test', function () {
        seedRoles();
        $admin = createAdmin();
        $test = Test::factory()->create();

        $response = $this->actingAs($admin)->delete("/tests/{$test->id}");

        $response->assertRedirect('/tests');
        $this->assertSoftDeleted('tests', ['id' => $test->id]);
    });

    test('Entrevistador cannot create tests', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->post('/tests', [
            'name' => 'Should Fail',
            'type' => 'numeric',
            'max_score' => 100,
        ]);

        $response->assertForbidden();
    });

    test('Admin can view test index', function () {
        seedRoles();
        $admin = createAdmin();
        Test::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/tests');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('tests'));
    });

    test('Admin can view test details', function () {
        seedRoles();
        $admin = createAdmin();
        $test = Test::factory()->create();

        $response = $this->actingAs($admin)->get("/tests/{$test->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('test'));
    });

    test('Admin can access create and edit pages', function () {
        seedRoles();
        $admin = createAdmin();

        $this->actingAs($admin)->get('/tests/create')->assertSuccessful();

        $test = Test::factory()->create();
        $this->actingAs($admin)->get("/tests/{$test->id}/edit")->assertSuccessful();
    });
});
