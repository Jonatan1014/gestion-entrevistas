<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin User Management Routes
|--------------------------------------------------------------------------
|
| Admin-only user management with role assignment.
| Protected by manage-roles permission.
|
*/

Route::middleware(['auth', 'verified', 'permission:manage-roles'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
