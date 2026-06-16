<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedRoles();
});

// ============================================================================
// RBAC-004 — Custom Role CRUD: RoleController
// ============================================================================

describe('RBAC-004: Custom Role CRUD — RoleController', function () {
    test('admin can view roles index listing all roles with permissions', function () {
        $admin = createAdmin();
        Role::create(['name' => 'Supervisor']);

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/roles/Index')
                ->has('roles', 3)
            );
    });

    test('admin can create a custom role with specific permissions', function () {
        $admin = createAdmin();
        $permissions = Permission::whereIn('name', ['view-applicants', 'edit-applicants'])->pluck('name')->toArray();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Supervisor',
                'permissions' => $permissions,
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $role = Role::where('name', 'Supervisor')->first();
        expect($role)->not->toBeNull();
        expect($role->permissions->pluck('name')->toArray())->toEqualCanonicalizing($permissions);
    });

    test('admin can edit a custom role — add new permissions', function () {
        $admin = createAdmin();
        $role = Role::create(['name' => 'Supervisor']);
        $role->syncPermissions(['view-applicants']);

        $newPermissions = ['view-applicants', 'edit-applicants', 'view-vacancies'];

        $this->actingAs($admin)
            ->patch(route('admin.roles.update', $role), [
                'name' => 'Supervisor',
                'permissions' => $newPermissions,
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $role->refresh();
        expect($role->permissions->pluck('name')->toArray())->toEqualCanonicalizing($newPermissions);
    });

    test('admin can remove all permissions from a custom role', function () {
        $admin = createAdmin();
        $role = Role::create(['name' => 'Observer']);
        $role->syncPermissions(['view-applicants', 'view-vacancies']);

        $this->actingAs($admin)
            ->patch(route('admin.roles.update', $role), [
                'name' => 'Observer',
                'permissions' => [],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        expect($role->permissions)->toHaveCount(0);
    });

    test('admin can delete a custom role', function () {
        $admin = createAdmin();
        $role = Role::create(['name' => 'Temporary']);

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        expect(Role::where('name', 'Temporary')->exists())->toBeFalse();
    });

    test('admin cannot delete pre-seeded Admin role — runtime guard rejects', function () {
        $admin = createAdmin();
        $adminRole = Role::where('name', 'Admin')->first();

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $adminRole))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('error');

        expect(Role::where('name', 'Admin')->exists())->toBeTrue();
    });

    test('admin cannot delete pre-seeded Entrevistador role — runtime guard rejects', function () {
        $admin = createAdmin();
        $entrevistadorRole = Role::where('name', 'Entrevistador')->first();

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $entrevistadorRole))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('error');

        expect(Role::where('name', 'Entrevistador')->exists())->toBeTrue();
    });

    test('entrevistador cannot access role management index', function () {
        $entrevistador = createEntrevistador();

        $this->actingAs($entrevistador)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    test('entrevistador cannot create a role', function () {
        $entrevistador = createEntrevistador();

        $this->actingAs($entrevistador)
            ->post(route('admin.roles.store'), [
                'name' => 'Hacker',
                'permissions' => ['view-applicants'],
            ])
            ->assertForbidden();
    });

    test('entrevistador cannot edit a role', function () {
        $entrevistador = createEntrevistador();
        $role = Role::create(['name' => 'Supervisor']);

        $this->actingAs($entrevistador)
            ->patch(route('admin.roles.update', $role), [
                'name' => 'Supervisor',
                'permissions' => ['view-applicants'],
            ])
            ->assertForbidden();
    });

    test('entrevistador cannot delete a role', function () {
        $entrevistador = createEntrevistador();
        $role = Role::create(['name' => 'Supervisor']);

        $this->actingAs($entrevistador)
            ->delete(route('admin.roles.destroy', $role))
            ->assertForbidden();
    });

    test('creating a role named Admin is rejected by validation', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Admin',
                'permissions' => ['view-applicants'],
            ])
            ->assertSessionHasErrors('name');
    });

    test('creating a role named Entrevistador is rejected by validation', function () {
        $admin = createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Entrevistador',
                'permissions' => ['view-applicants'],
            ])
            ->assertSessionHasErrors('name');
    });
});
