<?php

use App\Models\Test;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// TST-005 — Test Reuse Across Vacancies
// ============================================================================

describe('TST-005: Test Reuse Across Vacancies', function () {
    test('same test can be associated to multiple vacancies', function () {
        seedRoles();
        $admin = createAdmin();
        $test = Test::factory()->create();
        $vacancyA = Vacancy::factory()->create();
        $vacancyB = Vacancy::factory()->create();

        $this->actingAs($admin)->post("/vacancies/{$vacancyA->id}/tests", [
            'test_id' => $test->id,
            'weight' => 40,
        ])->assertRedirect();

        $this->actingAs($admin)->post("/vacancies/{$vacancyB->id}/tests", [
            'test_id' => $test->id,
            'weight' => 60,
        ])->assertRedirect();

        $this->assertDatabaseHas('vacancy_test', [
            'vacancy_id' => $vacancyA->id,
            'test_id' => $test->id,
            'weight' => 40,
        ]);

        $this->assertDatabaseHas('vacancy_test', [
            'vacancy_id' => $vacancyB->id,
            'test_id' => $test->id,
            'weight' => 60,
        ]);
    });

    test('editing a test template affects all associated vacancies', function () {
        seedRoles();
        $admin = createAdmin();
        $test = Test::factory()->create([
            'name' => 'Shared Test',
            'type' => 'numeric',
            'max_score' => 100,
        ]);
        $vacancyA = Vacancy::factory()->create();
        $vacancyB = Vacancy::factory()->create();

        $vacancyA->tests()->attach($test->id, ['weight' => 50]);
        $vacancyB->tests()->attach($test->id, ['weight' => 50]);

        $this->actingAs($admin)->put("/tests/{$test->id}", [
            'name' => 'Shared Test Updated',
            'description' => $test->description,
            'type' => 'numeric',
            'max_score' => 50,
            'evaluation_criteria' => $test->evaluation_criteria,
        ]);

        $freshTest = Test::find($test->id);
        expect($freshTest->max_score)->toBe(50.0);

        foreach ([$vacancyA, $vacancyB] as $vacancy) {
            $vacancy->load('tests');
            expect($vacancy->tests->first()->max_score)->toBe(50.0);
        }
    });
});
