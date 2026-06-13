<?php

use App\Enums\InterviewStatus;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Test;
use App\Models\Vacancy;
use App\Services\SelectionQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    reportSeedRoles();
});

describe('SelectionQueryService', function () {
    describe('comparisonQuery', function () {
        test('returns all applicants for a vacancy with their scores', function () {
            $admin = reportCreateAdmin();
            $vacancy = Vacancy::factory()->create();
            $test = Test::factory()->create(['max_score' => 100]);
            reportAttachTestToVacancy($test, $vacancy, 100);

            $applicants = Applicant::factory()->count(5)->create();
            foreach ($applicants as $index => $applicant) {
                reportAttachApplicantToVacancy($applicant, $vacancy, $index === 4 ? 'registered' : 'evaluated');
                if ($index < 4) {
                    reportRecordScore($test, $applicant, $vacancy, $admin, 60 + $index * 10);
                }
            }

            $service = new SelectionQueryService;
            $results = $service->comparisonQuery(['vacancy_id' => $vacancy->id])->get();

            expect($results->where('applicant_id', $applicants->first()->id))->toHaveCount(1);
            expect($results->where('applicant_id', $applicants->last()->id))->toHaveCount(1);
            expect($results)->toHaveCount(5);

            $firstApplicant = $results->firstWhere('applicant_id', $applicants->first()->id);
            expect($firstApplicant->applicant_name)->toBe($applicants->first()->name);
            expect((float) $firstApplicant->score)->toBe(60.0);
            expect((float) $firstApplicant->weighted_avg)->toBe(60.0);
        });

        test('pending applicants appear with null score and weighted average', function () {
            $admin = reportCreateAdmin();
            $vacancy = Vacancy::factory()->create();
            $test = Test::factory()->create(['max_score' => 100]);
            reportAttachTestToVacancy($test, $vacancy, 100);

            $scoredApplicant = Applicant::factory()->create();
            $pendingApplicant = Applicant::factory()->create();
            reportAttachApplicantToVacancy($scoredApplicant, $vacancy, 'evaluated');
            reportAttachApplicantToVacancy($pendingApplicant, $vacancy, 'registered');
            reportRecordScore($test, $scoredApplicant, $vacancy, $admin, 80);

            $service = new SelectionQueryService;
            $results = $service->comparisonQuery(['vacancy_id' => $vacancy->id])->get();

            $scored = $results->firstWhere('applicant_id', $scoredApplicant->id);
            $pending = $results->firstWhere('applicant_id', $pendingApplicant->id);

            expect($scored)->not->toBeNull();
            expect((float) $scored->score)->toBe(80.0);
            expect($pending)->not->toBeNull();
            expect($pending->score)->toBeNull();
            expect($pending->weighted_avg)->toBeNull();
        });
    });

    describe('completedInterviewsQuery', function () {
        test('returns only completed interviews', function () {
            $vacancy = Vacancy::factory()->create();
            $applicant = Applicant::factory()->create();
            $interviewer = reportCreateEntrevistador();
            reportAttachApplicantToVacancy($applicant, $vacancy);

            reportCreateCompletedInterview($vacancy, $applicant, $interviewer);
            Interview::factory()->pending()->create([
                'vacancy_id' => $vacancy->id,
                'applicant_id' => $applicant->id,
                'interviewer_id' => $interviewer->id,
            ]);

            $service = new SelectionQueryService;
            $results = $service->completedInterviewsQuery(['vacancy_id' => $vacancy->id])->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->status)->toBe(InterviewStatus::COMPLETED->value);
        });

        test('filters by date range', function () {
            $vacancy = Vacancy::factory()->create();
            $applicant = Applicant::factory()->create();
            $interviewer = reportCreateEntrevistador();
            reportAttachApplicantToVacancy($applicant, $vacancy);

            reportCreateCompletedInterview($vacancy, $applicant, $interviewer, now()->subDays(10));
            reportCreateCompletedInterview($vacancy, $applicant, $interviewer, now()->subDays(2));

            $service = new SelectionQueryService;
            $results = $service->completedInterviewsQuery([
                'vacancy_id' => $vacancy->id,
                'date_from' => now()->subDays(5)->toDateString(),
                'date_to' => now()->toDateString(),
            ])->get();

            expect($results)->toHaveCount(1);
        });
    });

    describe('averagesQuery', function () {
        test('calculates average score per test per vacancy', function () {
            $admin = reportCreateAdmin();
            $vacancy = Vacancy::factory()->create();
            $test = Test::factory()->create(['max_score' => 100]);
            reportAttachTestToVacancy($test, $vacancy, 100);

            $applicants = Applicant::factory()->count(10)->create();
            foreach ($applicants as $index => $applicant) {
                reportAttachApplicantToVacancy($applicant, $vacancy, $index < 6 ? 'evaluated' : 'registered');
                if ($index < 6) {
                    reportRecordScore($test, $applicant, $vacancy, $admin, 50 + $index * 5);
                }
            }

            $service = new SelectionQueryService;
            $results = $service->averagesQuery(['vacancy_id' => $vacancy->id])->get();

            expect($results)->toHaveCount(1);
            $average = (50 + 55 + 60 + 65 + 70 + 75) / 6;
            expect((float) $results->first()->avg_score)->toBe($average);
            expect((int) $results->first()->scored_count)->toBe(6);
        });
    });

    describe('pipelineQuery', function () {
        test('counts applicants per status stage per vacancy', function () {
            $vacancy = Vacancy::factory()->create();
            $statuses = ['registered', 'in_interview', 'evaluated', 'apt', 'no_apt'];

            foreach ($statuses as $status) {
                $applicant = Applicant::factory()->create();
                reportAttachApplicantToVacancy($applicant, $vacancy, $status);
            }

            $service = new SelectionQueryService;
            $results = $service->pipelineQuery(['vacancy_id' => $vacancy->id])->get();

            expect($results)->toHaveCount(5);
            foreach ($statuses as $status) {
                expect($results->firstWhere('status', $status)->count)->toBe(1);
            }
        });

        test('updates counts when status changes', function () {
            $vacancy = Vacancy::factory()->create();
            $applicant = Applicant::factory()->create();
            reportAttachApplicantToVacancy($applicant, $vacancy, 'in_interview');

            $service = new SelectionQueryService;
            $before = $service->pipelineQuery(['vacancy_id' => $vacancy->id])->get();
            expect($before->firstWhere('status', 'in_interview')->count)->toBe(1);
            expect($before->firstWhere('status', 'evaluated'))->toBeNull();

            $applicant->vacancies()->updateExistingPivot($vacancy->id, ['status' => 'evaluated']);

            $after = $service->pipelineQuery(['vacancy_id' => $vacancy->id])->get();
            expect($after->firstWhere('status', 'in_interview'))->toBeNull();
            expect($after->firstWhere('status', 'evaluated')->count)->toBe(1);
        });
    });

    describe('scopeByInterviewer', function () {
        test('restricts comparison results to applicants interviewed by the user', function () {
            $vacancy = Vacancy::factory()->create();
            $test = Test::factory()->create(['max_score' => 100]);
            reportAttachTestToVacancy($test, $vacancy, 100);

            $interviewer = reportCreateEntrevistador();
            $otherInterviewer = reportCreateEntrevistador();

            $scopedApplicant = Applicant::factory()->create();
            $otherApplicant = Applicant::factory()->create();
            reportAttachApplicantToVacancy($scopedApplicant, $vacancy, 'evaluated');
            reportAttachApplicantToVacancy($otherApplicant, $vacancy, 'evaluated');
            reportRecordScore($test, $scopedApplicant, $vacancy, $interviewer, 80);
            reportRecordScore($test, $otherApplicant, $vacancy, $otherInterviewer, 90);

            reportCreateCompletedInterview($vacancy, $scopedApplicant, $interviewer);
            reportCreateCompletedInterview($vacancy, $otherApplicant, $otherInterviewer);

            $service = new SelectionQueryService;
            $query = $service->comparisonQuery(['vacancy_id' => $vacancy->id]);
            $service->scopeByInterviewer($query, $interviewer);
            $results = $query->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->applicant_id)->toBe($scopedApplicant->id);
        });

        test('restricts interviews report to user interviews', function () {
            $vacancy = Vacancy::factory()->create();
            $applicant = Applicant::factory()->create();
            $interviewer = reportCreateEntrevistador();
            $otherInterviewer = reportCreateEntrevistador();
            reportAttachApplicantToVacancy($applicant, $vacancy);

            reportCreateCompletedInterview($vacancy, $applicant, $interviewer);
            reportCreateCompletedInterview($vacancy, $applicant, $otherInterviewer);

            $service = new SelectionQueryService;
            $query = $service->completedInterviewsQuery(['vacancy_id' => $vacancy->id]);
            $service->scopeByInterviewer($query, $interviewer);
            $results = $query->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->interviewer_id)->toBe($interviewer->id);
        });
    });
});
