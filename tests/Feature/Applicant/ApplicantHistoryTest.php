<?php

use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// APP-005 — Applicant History Timeline
// ============================================================================

describe('APP-005: Applicant History Timeline', function () {
    test('new applicant has empty history', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($entrevistador)->get("/applicants/{$applicant->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('history')
            ->where('history', fn ($history) => count($history) === 0)
        );
    });

    test('history prop is present on applicant show page', function () {
        seedRoles();
        $admin = createAdmin();
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($admin)->get("/applicants/{$applicant->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('applicant')
            ->has('history')
        );
    });

    test('blocked event appears in history', function () {
        seedRoles();
        $admin = createAdmin();
        $applicant = Applicant::factory()->create();

        $this->actingAs($admin)->post("/applicants/{$applicant->id}/block", [
            'block_reason' => 'Falsified documents',
        ]);

        $response = $this->actingAs($admin)->get("/applicants/{$applicant->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('history')
            ->where('history', fn ($history) => count($history) >= 1)
        );
    });
});
