<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users with their roles.
     */
    public function index(Request $request): Response
    {
        $query = User::with('roles')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->diffForHumans(),
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at->format('d/m/Y'),
            ]),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for editing a user's roles.
     */
    public function edit(User $user): Response
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/users/Edit', [
            'editUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ],
            'roles' => $roles,
        ]);
    }

    /**
     * Update the user's roles.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles' => ['present', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user->syncRoles($request->input('roles', []));

        return redirect()->route('admin.users.index')
            ->with('success', "Roles de {$user->name} actualizados correctamente.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No podés eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
