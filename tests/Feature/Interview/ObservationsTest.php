<?php

use App\Enums\InterviewStatus;
use App\Models\Interview;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

// ============================================================================
// INT-005 — Interview Observations
// ============================================================================

describe('INT-005: Interview Observations', function () {
    test('completion records observations on the interview', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $interview = Interview::factory()->create(['status' => InterviewStatus::PENDING]);

        $response = $this->actingAs($admin)->post("/interviews/{$interview->id}/complete", [
            'observations' => 'Candidate demonstrated strong technical skills',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => InterviewStatus::COMPLETED->value,
            'observations' => 'Candidate demonstrated strong technical skills',
        ]);
    });

    test('completion without observations is rejected', function () {
        intSeedRoles();
        $admin = intCreateAdmin();
        $interview = Interview::factory()->create(['status' => InterviewStatus::PENDING]);

        $response = $this->actingAs($admin)->post("/interviews/{$interview->id}/complete");

        $response->assertSessionHasErrors(['observations']);
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => InterviewStatus::PENDING->value,
        ]);
    });
});
