<?php

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\RoleSeeder;

if (! function_exists('reportSeedRoles')) {
    /**
     * Seed RBAC roles and permissions.
     */
    function reportSeedRoles(): void
    {
        (new RoleSeeder)->run();
    }
}

if (! function_exists('reportCreateAdmin')) {
    /**
     * Create and return a user with the Admin role.
     */
    function reportCreateAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }
}

if (! function_exists('reportCreateEntrevistador')) {
    /**
     * Create and return a user with the Entrevistador role.
     */
    function reportCreateEntrevistador(): User
    {
        $entrevistador = User::factory()->create();
        $entrevistador->assignRole('Entrevistador');

        return $entrevistador;
    }
}

if (! function_exists('reportAttachApplicantToVacancy')) {
    /**
     * Attach an applicant to a vacancy with the given pivot status.
     */
    function reportAttachApplicantToVacancy(Applicant $applicant, Vacancy $vacancy, string $status = 'registered'): void
    {
        $applicant->vacancies()->attach($vacancy->id, ['status' => $status]);
    }
}

if (! function_exists('reportAttachTestToVacancy')) {
    /**
     * Attach a test to a vacancy with the given weight.
     */
    function reportAttachTestToVacancy(Test $test, Vacancy $vacancy, float $weight): void
    {
        $vacancy->tests()->attach($test->id, ['weight' => $weight]);
    }
}

if (! function_exists('reportRecordScore')) {
    /**
     * Record a test result for an applicant on a vacancy.
     */
    function reportRecordScore(Test $test, Applicant $applicant, Vacancy $vacancy, User $evaluator, float $score): TestResult
    {
        return TestResult::factory()->create([
            'test_id' => $test->id,
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancy->id,
            'evaluator_id' => $evaluator->id,
            'score' => $score,
        ]);
    }
}

if (! function_exists('reportCreateCompletedInterview')) {
    /**
     * Create a completed interview.
     */
    function reportCreateCompletedInterview(Vacancy $vacancy, Applicant $applicant, User $interviewer, ?DateTimeInterface $scheduledAt = null): Interview
    {
        return Interview::factory()->completed()->create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $applicant->id,
            'interviewer_id' => $interviewer->id,
            'scheduled_at' => $scheduledAt ?? now()->subDay(),
        ]);
    }
}
