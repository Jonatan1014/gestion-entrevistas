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

function seedRoles(): void
{
    (new RoleSeeder)->run();
}

// ============================================================================
// TST-003 — Text Test Type
// ============================================================================

describe('TST-003: Text Test Type', function () {
    test('text test stores score configuration with observations criteria', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/tests', [
            'name' => 'Open-ended Interview',
            'description' => 'Manual scoring with observations',
            'type' => 'text',
            'max_score' => 100,
            'evaluation_criteria' => 'Evaluate communication and clarity',
        ]);

        $response->assertRedirectContains('/tests/');
        $this->assertDatabaseHas('tests', [
            'name' => 'Open-ended Interview',
            'type' => 'text',
            'max_score' => 100,
            'evaluation_criteria' => 'Evaluate communication and clarity',
        ]);
    });

    test('text test does not support auto-calculation', function () {
        seedRoles();
        $admin = createAdmin();

        $test = Test::factory()->create([
            'type' => 'text',
            'max_score' => 100,
        ]);

        expect($test->type->value)->toBe('text');
        expect($test->questions)->toHaveCount(0);
    });
});
