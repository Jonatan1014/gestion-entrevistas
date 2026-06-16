<?php

use App\Enums\InterviewStatus;
use App\Models\Interview;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

// ============================================================================
// INT-002 — Interview Status Lifecycle
// ============================================================================

describe('INT-002: Interview Status Lifecycle', function () {
    test('pending interview can be completed with observations', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $interview = Interview::factory()->create(['status' => InterviewStatus::PENDING]);

        $response = $this->actingAs($admin)->post("/interviews/{$interview->id}/complete", [
            'observations' => 'Strong technical profile',
            'score' => 7,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'Strong technical profile',
            'score' => 7,
        ]);
        $this->assertNotNull($interview->fresh()->completed_at);
    });

    test('pending interview can be cancelled with a reason', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $interview = Interview::factory()->create(['status' => InterviewStatus::PENDING]);

        $response = $this->actingAs($admin)->post("/interviews/{$interview->id}/cancel", [
            'cancellation_reason' => 'Applicant withdrew',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => InterviewStatus::CANCELLED->value,
            'cancellation_reason' => 'Applicant withdrew',
        ]);
    });

    test('completed interview cannot be modified', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $interview = Interview::factory()->create(['status' => InterviewStatus::COMPLETED]);

        $response = $this->actingAs($admin)->post("/interviews/{$interview->id}/cancel", [
            'cancellation_reason' => 'Should not work',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => InterviewStatus::COMPLETED->value,
        ]);
    });

    test('cancelled interview cannot be completed', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $interview = Interview::factory()->create(['status' => InterviewStatus::CANCELLED]);

        $response = $this->actingAs($admin)->post("/interviews/{$interview->id}/complete", [
            'observations' => 'Should not work',
            'score' => 5,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => InterviewStatus::CANCELLED->value,
        ]);
    });
});
