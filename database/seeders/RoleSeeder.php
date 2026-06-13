<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * All system permissions grouped by module.
     * Pattern: {action}-{module} (e.g., view-vacancies, create-applicants).
     */
    private const PERMISSIONS = [
        // Vacancies
        'view-vacancies',
        'create-vacancies',
        'edit-vacancies',
        'delete-vacancies',
        'close-vacancies',
        'cancel-vacancies',
        'reopen-vacancies',

        // Applicants
        'view-applicants',
        'create-applicants',
        'edit-applicants',
        'delete-applicants',
        'block-applicants',
        'unblock-applicants',

        // Applicant Documents
        'upload-documents',
        'download-documents',
        'delete-documents',

        // Tests
        'view-tests',
        'manage-tests',
        'create-tests',
        'edit-tests',
        'delete-tests',

        // Test Results
        'record-test-results',
        'view-test-results',
        'override-test-results',
        'record-results',
        'view-results',
        'override-results',
        'set-final-status',

        // Interviews
        'view-interviews',
        'create-interviews',
        'edit-interviews',
        'manage-interviews',
        'complete-interviews',
        'cancel-interviews',
        'delete-interviews',

        // Reports
        'view-reports',
        'export-reports',

        // RBAC / Role Management
        'view-roles',
        'create-roles',
        'edit-roles',
        'delete-roles',
        'manage-roles',
    ];

    /**
     * Permissions assigned to the Entrevistador role.
     */
    private const ENTREVISTADOR_PERMISSIONS = [
        'view-vacancies',
        'view-applicants',
        'create-applicants',
        'edit-applicants',
        'create-interviews',
        'edit-interviews',
        'manage-interviews',
        'view-interviews',
        'view-tests',
        'record-test-results',
        'view-test-results',
        'record-results',
        'view-results',
        'set-final-status',
        'view-reports',
    ];

    /**
     * Run the database seeds.
     *
     * Idempotent: uses firstOrCreate to avoid duplicates on repeat runs.
     */
    public function run(): void
    {
        // Create or retrieve all permissions
        foreach (self::PERMISSIONS as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Admin role — gets ALL permissions
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web']
        );
        $adminRole->syncPermissions(Permission::all());

        // Entrevistador role — gets limited permissions
        $entrevistadorRole = Role::firstOrCreate(
            ['name' => 'Entrevistador', 'guard_name' => 'web']
        );
        $entrevistadorRole->syncPermissions(
            Permission::whereIn('name', self::ENTREVISTADOR_PERMISSIONS)->get()
        );

        // Create a default admin user if it doesn't exist
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sistema-seleccion.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole($adminRole);
    }
}
