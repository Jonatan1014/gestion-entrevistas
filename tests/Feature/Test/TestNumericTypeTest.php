<?php

use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// TST-002 — Numeric Test Type
// ============================================================================

describe('TST-002: Numeric Test Type', function () {
    test('numeric test stores max_score and accepts valid configuration', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/tests', [
            'name' => 'Numeric Evaluation',
            'description' => 'Direct numeric score',
            'type' => 'numeric',
            'max_score' => 100,
            'evaluation_criteria' => 'Score must be between 0 and 100',
        ]);

        $response->assertRedirectContains('/tests/');
        $this->assertDatabaseHas('tests', [
            'name' => 'Numeric Evaluation',
            'type' => 'numeric',
            'max_score' => 100,
            'evaluation_criteria' => 'Score must be between 0 and 100',
        ]);
    });

    test('numeric test rejects negative max_score', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/tests', [
            'name' => 'Invalid Numeric',
            'type' => 'numeric',
            'max_score' => -10,
        ]);

        $response->assertSessionHasErrors(['max_score']);
    });

    test('numeric test max_score must be within decimal range', function () {
        seedRoles();
        $admin = createAdmin();

        $test = Test::factory()->create([
            'type' => 'numeric',
            'max_score' => 100,
        ]);

        expect($test->max_score)->toBe(100.0);
        expect($test->type->value)->toBe('numeric');
    });
});
