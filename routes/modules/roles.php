<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Role Management Routes
|--------------------------------------------------------------------------
|
| Protected by 'permission:create-roles' middleware.
| Only Admin users can access. Entrevistador users receive 403.
| Full RoleController CRUD will be added in a later phase.
|
*/

Route::middleware(['auth', 'verified', 'permission:create-roles'])
    ->prefix('admin/roles')
    ->group(function () {
        Route::post('/', fn () => response()->json(['message' => 'Role created']))
            ->name('admin.roles.store');
    });