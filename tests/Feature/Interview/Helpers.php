<?php

use App\Models\Applicant;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\RoleSeeder;

if (! function_exists('intSeedRoles')) {
    /**
     * Seed RBAC roles and permissions.
     */
    function intSeedRoles(): void
    {
        (new RoleSeeder)->run();
    }
}

if (! function_exists('intCreateAdmin')) {
    /**
     * Create and return a user with the Admin role.
     */
    function intCreateAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }
}

if (! function_exists('intCreateEntrevistador')) {
    /**
     * Create and return a user with the Entrevistador role.
     */
    function intCreateEntrevistador(): User
    {
        $entrevistador = User::factory()->create();
        $entrevistador->assignRole('Entrevistador');

        return $entrevistador;
    }
}

if (! function_exists('intAttachApplicantToVacancy')) {
    /**
     * Attach an applicant to a vacancy with the given pivot status.
     */
    function intAttachApplicantToVacancy(Applicant $applicant, Vacancy $vacancy, string $status = 'registered'): void
    {
        $applicant->vacancies()->attach($vacancy->id, ['status' => $status]);
    }
}
