<?php

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// Helper — create authenticated users with roles
// ============================================================================

// ============================================================================
// VAC-001 — Vacancy CRUD
// ============================================================================

describe('VAC-001: Vacancy CRUD', function () {
    test('Admin can create a vacancy', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Senior Developer',
            'location' => 'Remote',
            'requirements' => '5+ years of experience in Laravel',
            'status' => 'open',
        ]);

        $response->assertRedirectContains('/vacancies/');
        $this->assertDatabaseHas('vacancies', [
            'position' => 'Senior Developer',
            'location' => 'Remote',
            'requirements' => '5+ years of experience in Laravel',
            'status' => VacancyStatus::OPEN->value,
            'created_by' => $admin->id,
        ]);
    });

    test('vacancy status defaults to open when created', function () {
        seedRoles();
        $admin = createAdmin();

        $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Junior Developer',
            'location' => 'New York',
            'requirements' => '1+ year experience',
        ]);

        $this->assertDatabaseHas('vacancies', [
            'position' => 'Junior Developer',
            'status' => VacancyStatus::OPEN->value,
        ]);
    });

    test('Admin can view vacancy index', function () {
        seedRoles();
        $admin = createAdmin();
        Vacancy::factory()->count(3)->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get('/vacancies');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('vacancies'));
    });

    test('Admin can view vacancy details', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get("/vacancies/{$vacancy->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('vacancy'));
    });

    test('Admin can update a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'position' => 'Old Position',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put("/vacancies/{$vacancy->id}", [
            'position' => 'Updated Position',
            'location' => $vacancy->location,
            'requirements' => $vacancy->requirements,
        ]);

        $response->assertRedirect("/vacancies/{$vacancy->id}");
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'position' => 'Updated Position',
        ]);
    });

    test('Admin can delete a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->delete("/vacancies/{$vacancy->id}");

        $response->assertRedirect('/vacancies');
        $this->assertSoftDeleted('vacancies', ['id' => $vacancy->id]);
    });

    test('Entrevistador cannot create vacancies', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->post('/vacancies', [
            'position' => 'Developer',
            'location' => 'Remote',
            'requirements' => '3+ years experience',
        ]);

        $response->assertForbidden();
    });

    test('Entrevistador cannot update a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($entrevistador)->put("/vacancies/{$vacancy->id}", [
            'position' => 'Hacked Position',
            'location' => 'Nowhere',
            'requirements' => 'Nothing',
        ]);

        $response->assertForbidden();
    });

    test('Entrevistador cannot delete a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($entrevistador)->delete("/vacancies/{$vacancy->id}");

        $response->assertForbidden();
    });

    test('unauthenticated user cannot access vacancies index', function () {
        $response = $this->get('/vacancies');
        $response->assertRedirect('/login');
    });
});

// ============================================================================
// VAC-002 — Vacancy Status Lifecycle
// ============================================================================

describe('VAC-002: Vacancy Status Lifecycle', function () {
    test('Admin can close an open vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::OPEN,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/close");

        $response->assertRedirect("/vacancies/{$vacancy->id}");
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'status' => VacancyStatus::CLOSED->value,
        ]);
    });

    test('Admin can cancel an open vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::OPEN,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/cancel");

        $response->assertRedirect("/vacancies/{$vacancy->id}");
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'status' => VacancyStatus::CANCELLED->value,
        ]);
    });

    test('Admin can reopen a closed vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::CLOSED,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/reopen");

        $response->assertRedirect("/vacancies/{$vacancy->id}");
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'status' => VacancyStatus::OPEN->value,
        ]);
    });

    test('cancelled vacancy cannot change status', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::CANCELLED,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/close");

        $response->assertForbidden();
        // Also verify status has NOT changed
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'status' => VacancyStatus::CANCELLED->value,
        ]);
    });

    test('cancelled vacancy cannot be reopened', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::CANCELLED,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/reopen");

        $response->assertForbidden();
    });

    test('closing an already closed vacancy is forbidden', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::CLOSED,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/vacancies/{$vacancy->id}/close");

        $response->assertForbidden();
    });

    test('Entrevistador cannot close a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::OPEN,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($entrevistador)->post("/vacancies/{$vacancy->id}/close");

        $response->assertForbidden();
    });

    test('Entrevistador cannot cancel a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::OPEN,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($entrevistador)->post("/vacancies/{$vacancy->id}/cancel");

        $response->assertForbidden();
    });

    test('Entrevistador cannot reopen a vacancy', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::CLOSED,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($entrevistador)->post("/vacancies/{$vacancy->id}/reopen");

        $response->assertForbidden();
    });
});

// ============================================================================
// VAC-003 — Vacancy Status Lifecycle (view + list — Entrevistador access)
// ============================================================================

describe('VAC-003: Entrevistador can view vacancies', function () {
    test('Entrevistador can view vacancy list', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        Vacancy::factory()->count(3)->create(['created_by' => $admin->id]);

        $response = $this->actingAs($entrevistador)->get('/vacancies');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->has('vacancies'));
    });

    test('Entrevistador can view vacancy detail page', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($entrevistador)->get("/vacancies/{$vacancy->id}");

        $response->assertSuccessful();
    });
});

// ============================================================================
// VAC-004 — Status change via ChangeStatusRequest (validation edge cases)
// ============================================================================

describe('VAC-004: Vacancy validation', function () {
    test('creating a vacancy requires position', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'location' => 'Remote',
            'requirements' => 'Some requirements',
        ]);

        $response->assertSessionHasErrors(['position']);
    });

    test('creating a vacancy requires location', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Developer',
            'requirements' => 'Some requirements',
        ]);

        $response->assertSessionHasErrors(['location']);
    });

    test('creating a vacancy requires requirements', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Developer',
            'location' => 'Remote',
        ]);

        $response->assertSessionHasErrors(['requirements']);
    });

    test('updating a vacancy requires position', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->put("/vacancies/{$vacancy->id}", [
            'location' => 'Updated Location',
            'requirements' => 'Updated requirements',
        ]);

        $response->assertSessionHasErrors(['position']);
    });

    test('min_grade must be numeric and not negative', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Developer',
            'location' => 'Remote',
            'requirements' => '3+ years experience',
            'min_grade' => -5,
        ]);

        $response->assertSessionHasErrors(['min_grade']);
    });

    test('min_grade accepts valid decimal', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Developer',
            'location' => 'Remote',
            'requirements' => '3+ years experience',
            'min_grade' => 70.50,
        ]);

        $response->assertRedirectContains('/vacancies/');
        $this->assertDatabaseHas('vacancies', [
            'position' => 'Developer',
            'min_grade' => 70.50,
        ]);
    });

    test('min_grade is optional and nullable', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Developer',
            'location' => 'Remote',
            'requirements' => '3+ years experience',
        ]);

        $response->assertRedirectContains('/vacancies/');
        $this->assertDatabaseHas('vacancies', [
            'position' => 'Developer',
            'min_grade' => null,
        ]);
    });
});

// ============================================================================
// VAC-005 — Configurable evaluation criteria (min_grade field)
// ============================================================================

describe('VAC-005: Evaluation criteria (min_grade)', function () {
    test('vacancy can store min_grade threshold', function () {
        seedRoles();
        $admin = createAdmin();

        $this->actingAs($admin)->post('/vacancies', [
            'position' => 'Lead Developer',
            'location' => 'Berlin',
            'requirements' => '7+ years experience',
            'min_grade' => 80.00,
        ]);

        $this->assertDatabaseHas('vacancies', [
            'position' => 'Lead Developer',
            'min_grade' => 80.00,
        ]);
    });

    test('vacancy creation page is accessible to Admin', function () {
        seedRoles();
        $admin = createAdmin();

        $response = $this->actingAs($admin)->get('/vacancies/create');

        $response->assertSuccessful();
    });

    test('vacancy edit page is accessible to Admin', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get("/vacancies/{$vacancy->id}/edit");

        $response->assertSuccessful();
    });

    test('Entrevistador cannot access vacancy creation page', function () {
        seedRoles();
        $entrevistador = createEntrevistador();

        $response = $this->actingAs($entrevistador)->get('/vacancies/create');

        $response->assertForbidden();
    });

    test('Entrevistador cannot access vacancy edit page', function () {
        seedRoles();
        $admin = createAdmin();
        $entrevistador = createEntrevistador();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($entrevistador)->get("/vacancies/{$vacancy->id}/edit");

        $response->assertForbidden();
    });
});

// ============================================================================
// Model & Enum unit-level tests
// ============================================================================

describe('Vacancy Model & Enum', function () {
    test('VacancyStatus enum has expected values', function () {
        expect(VacancyStatus::OPEN->value)->toBe('open');
        expect(VacancyStatus::CLOSED->value)->toBe('closed');
        expect(VacancyStatus::CANCELLED->value)->toBe('cancelled');
    });

    test('Vacancy model casts status to VacancyStatus enum', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create([
            'status' => VacancyStatus::OPEN,
            'created_by' => $admin->id,
        ]);

        $fresh = Vacancy::find($vacancy->id);
        expect($fresh->status)->toBeInstanceOf(VacancyStatus::class);
        expect($fresh->status)->toBe(VacancyStatus::OPEN);
    });

    test('Vacancy model belongs to creator (User)', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);

        expect($vacancy->creator)->not->toBeNull();
        expect($vacancy->creator->id)->toBe($admin->id);
    });

    test('Vacancy uses soft deletes', function () {
        seedRoles();
        $admin = createAdmin();
        $vacancy = Vacancy::factory()->create(['created_by' => $admin->id]);
        $vacancyId = $vacancy->id;

        $vacancy->delete();

        // Still in DB with deleted_at
        $this->assertSoftDeleted('vacancies', ['id' => $vacancyId]);
        // Not found in normal query
        expect(Vacancy::find($vacancyId))->toBeNull();
        // Found with trashed
        expect(Vacancy::withTrashed()->find($vacancyId))->not->toBeNull();
    });
});
