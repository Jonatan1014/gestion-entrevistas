<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with their permissions.
     */
    public function index(): Response
    {
        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'permissions_count' => $role->permissions->count(),
                'is_protected' => in_array($role->name, ['Admin', 'Entrevistador'], true),
            ]);

        return Inertia::render('admin/roles/Index', [
            'roles' => $roles,
            'canCreateRoles' => auth()->user()->can('create-roles'),
            'canEditRoles' => auth()->user()->can('edit-roles'),
            'canDeleteRoles' => auth()->user()->can('delete-roles'),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);

            return $parts[1] ?? 'other';
        });

        return Inertia::render('admin/roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name')]);
        $role->syncPermissions($request->validated('permissions'));

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$role->name}' creado correctamente.");
    }

    /**
     * Show the form for editing role permissions.
     */
    public function edit(Role $role): Response
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);

            return $parts[1] ?? 'other';
        });

        return Inertia::render('admin/roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'is_protected' => in_array($role->name, ['Admin', 'Entrevistador'], true),
            ],
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the role's permissions.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($request->has('name') && $request->validated('name') !== $role->name) {
            $role->update(['name' => $request->validated('name')]);
        }

        $role->syncPermissions($request->validated('permissions'));

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$role->name}' actualizado correctamente.");
    }

    /**
     * Remove the role. Protects pre-seeded roles (Admin, Entrevistador) from deletion.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['Admin', 'Entrevistador'], true)) {
            return redirect()->route('admin.roles.index')
                ->with('error', "El rol '{$role->name}' es un rol del sistema y no puede ser eliminado.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
