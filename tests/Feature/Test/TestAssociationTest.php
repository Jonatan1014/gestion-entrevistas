<?php

use App\Models\Test;
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

function seedRoles(): void
{
    (new RoleSeeder)->run();
}

// ============================================================================
// TST-006 — Test Association to Vacancy
// ============================================================================

describe('TST-006: Test Association to Vacancy', function () {
    test('Admin can attach a test to a vacancy with weight', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create();

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/tests", [
            'test_id' => $test->id,
            'weight' => 40,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vacancy_test', [
            'vacancy_id' => $vacancy->id,
            'test_id' => $test->id,
            'weight' => 40,
        ]);
    });

    test('total weights for a vacancy cannot exceed 100%', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $existingTest = Test::factory()->create();
        $newTest = Test::factory()->create();

        $vacancy->tests()->attach($existingTest->id, ['weight' => 80]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/tests", [
            'test_id' => $newTest->id,
            'weight' => 30,
        ]);

        $response->assertSessionHasErrors(['weight']);
        $this->assertDatabaseMissing('vacancy_test', [
            'vacancy_id' => $vacancy->id,
            'test_id' => $newTest->id,
        ]);
    });

    test('weight must be between 0 and 100', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create();

        $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/tests", [
            'test_id' => $test->id,
            'weight' => -10,
        ])->assertSessionHasErrors(['weight']);

        $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/tests", [
            'test_id' => $test->id,
            'weight' => 110,
        ])->assertSessionHasErrors(['weight']);
    });

    test('Admin can detach a test from a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create();
        $vacancy->tests()->attach($test->id, ['weight' => 40]);

        $response = $this->actingAs($admin)->delete("/vacancies/{$vacancy->id}/tests/{$test->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('vacancy_test', [
            'vacancy_id' => $vacancy->id,
            'test_id' => $test->id,
        ]);
    });

    test('Admin can update weight for an attached test', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create();
        $test = Test::factory()->create();
        $vacancy->tests()->attach($test->id, ['weight' => 40]);

        $response = $this->actingAs($admin)->put("/vacancies/{$vacancy->id}/tests/{$test->id}", [
            'weight' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vacancy_test', [
            'vacancy_id' => $vacancy->id,
            'test_id' => $test->id,
            'weight' => 60,
        ]);
    });
});
