<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Role Management Routes
|--------------------------------------------------------------------------
|
| Full CRUD for role management. Protected by spatie/laravel-permission
| middleware. Only Admin users can access.
|
| Pre-seeded roles (Admin, Entrevistador) are protected from deletion
| at runtime by RoleController@destroy.
|
*/

Route::middleware(['auth', 'verified'])
    ->prefix('admin/roles')
    ->name('admin.roles.')
    ->group(function () {
        Route::middleware('permission:manage-roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
        });

        Route::middleware('permission:create-roles')->group(function () {
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
        });

        Route::middleware('permission:edit-roles')->group(function () {
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::patch('/{role}', [RoleController::class, 'update'])->name('update');
        });

        Route::middleware('permission:delete-roles')->group(function () {
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });
