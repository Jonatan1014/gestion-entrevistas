<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SelectionQueryService
{
    /**
     * Applicants per vacancy with test scores, weighted average, and final status.
     *
     * @param  array<string, mixed>  $filters
     */
    public function comparisonQuery(array $filters): Builder
    {
        $vacancyId = $filters['vacancy_id'] ?? null;

        $weightedAverageSubquery = DB::table('test_results as tr2')
            ->selectRaw('SUM((tr2.score * 1.0) / tests2.max_score * 100 * vacancy_test2.weight) / SUM(vacancy_test2.weight)')
            ->join('tests as tests2', 'tests2.id', '=', 'tr2.test_id')
            ->join('vacancy_test as vacancy_test2', function ($join) {
                $join->on('vacancy_test2.test_id', '=', 'tr2.test_id')
                    ->on('vacancy_test2.vacancy_id', '=', 'tr2.vacancy_id');
            })
            ->whereColumn('tr2.applicant_id', 'va.applicant_id')
            ->whereColumn('tr2.vacancy_id', 'va.vacancy_id');

        $query = DB::table('vacancy_applicant as va')
            ->select([
                'va.applicant_id',
                'applicants.name as applicant_name',
                'va.vacancy_id',
                'vacancies.position as vacancy_position',
                'tests.id as test_id',
                'tests.name as test_name',
                'test_results.score',
                'tests.max_score',
                'vacancy_test.weight',
                DB::raw("({$weightedAverageSubquery->toSql()}) as weighted_avg"),
                'va.status',
            ])
            ->join('applicants', 'applicants.id', '=', 'va.applicant_id')
            ->join('vacancies', 'vacancies.id', '=', 'va.vacancy_id')
            ->leftJoin('test_results', function ($join) {
                $join->on('test_results.applicant_id', '=', 'va.applicant_id')
                    ->on('test_results.vacancy_id', '=', 'va.vacancy_id');
            })
            ->leftJoin('tests', 'tests.id', '=', 'test_results.test_id')
            ->leftJoin('vacancy_test', function ($join) {
                $join->on('vacancy_test.test_id', '=', 'test_results.test_id')
                    ->on('vacancy_test.vacancy_id', '=', 'test_results.vacancy_id');
            })
            ->orderBy('applicants.name')
            ->orderBy('tests.name');

        if ($vacancyId) {
            $query->where('va.vacancy_id', $vacancyId);
        }

        $this->applyInterviewerFilter($query, $filters);

        return $query;
    }

    /**
     * Completed interviews with applicant, vacancy, and interviewer details.
     *
     * @param  array<string, mixed>  $filters
     */
    public function completedInterviewsQuery(array $filters): Builder
    {
        $query = DB::table('interviews')
            ->select([
                'interviews.*',
                'applicants.name as applicant_name',
                'applicants.email as applicant_email',
                'vacancies.position as vacancy_position',
                'users.name as interviewer_name',
            ])
            ->join('applicants', 'applicants.id', '=', 'interviews.applicant_id')
            ->join('vacancies', 'vacancies.id', '=', 'interviews.vacancy_id')
            ->join('users', 'users.id', '=', 'interviews.interviewer_id')
            ->where('interviews.status', 'completed')
            ->orderBy('interviews.scheduled_at', 'desc');

        $this->applyBasicFilters($query, $filters);

        return $query;
    }

    /**
     * Average scores per test per vacancy.
     *
     * @param  array<string, mixed>  $filters
     */
    public function averagesQuery(array $filters): Builder
    {
        $vacancyId = $filters['vacancy_id'] ?? null;

        $query = DB::table('test_results')
            ->select([
                'tests.id as test_id',
                'tests.name as test_name',
                'vacancies.id as vacancy_id',
                'vacancies.position as vacancy_position',
                DB::raw('AVG(test_results.score) as avg_score'),
                DB::raw('COUNT(*) as scored_count'),
            ])
            ->join('tests', 'tests.id', '=', 'test_results.test_id')
            ->join('vacancies', 'vacancies.id', '=', 'test_results.vacancy_id')
            ->join('applicants', 'applicants.id', '=', 'test_results.applicant_id')
            ->groupBy('tests.id', 'vacancies.id')
            ->orderBy('vacancies.position')
            ->orderBy('tests.name');

        if ($vacancyId) {
            $query->where('test_results.vacancy_id', $vacancyId);
        }

        $this->applyInterviewerFilter($query, $filters);

        return $query;
    }

    /**
     * Applicant counts per status stage per vacancy.
     *
     * @param  array<string, mixed>  $filters
     */
    public function pipelineQuery(array $filters): Builder
    {
        $vacancyId = $filters['vacancy_id'] ?? null;

        $query = DB::table('vacancy_applicant as va')
            ->select([
                'va.vacancy_id',
                'vacancies.position as vacancy_position',
                'va.status',
                DB::raw('COUNT(*) as count'),
            ])
            ->join('applicants', 'applicants.id', '=', 'va.applicant_id')
            ->join('vacancies', 'vacancies.id', '=', 'va.vacancy_id')
            ->groupBy('va.vacancy_id', 'va.status')
            ->orderBy('vacancies.position')
            ->orderBy('va.status');

        if ($vacancyId) {
            $query->where('va.vacancy_id', $vacancyId);
        }

        $this->applyInterviewerFilter($query, $filters);

        return $query;
    }

    /**
     * Restrict a report query to applicants interviewed by the given user.
     */
    public function scopeByInterviewer(Builder $query, User $user): Builder
    {
        $from = $query->from;

        if ($from === 'interviews' || str_starts_with((string) $from, 'interviews ')) {
            return $query->where('interviews.interviewer_id', $user->id);
        }

        return $query->whereExists(function ($q) use ($user) {
            $q->select(DB::raw(1))
                ->from('interviews')
                ->whereColumn('interviews.applicant_id', 'applicants.id')
                ->whereColumn('interviews.vacancy_id', 'vacancies.id')
                ->where('interviews.interviewer_id', $user->id);
        });
    }

    /**
     * Apply vacancy, date, and interviewer filters to the interviews query.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyBasicFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['vacancy_id'])) {
            $query->where('interviews.vacancy_id', $filters['vacancy_id']);
        }

        if (! empty($filters['interviewer_id'])) {
            $query->where('interviews.interviewer_id', $filters['interviewer_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('interviews.scheduled_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('interviews.scheduled_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Apply the interviewer filter when provided as a regular filter.
     *
     * For non-interview reports this scopes applicants to those who have an
     * interview with the selected interviewer.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyInterviewerFilter(Builder $query, array $filters): void
    {
        if (empty($filters['interviewer_id'])) {
            return;
        }

        $interviewerId = $filters['interviewer_id'];

        $query->whereExists(function ($q) use ($interviewerId) {
            $q->select(DB::raw(1))
                ->from('interviews')
                ->whereColumn('interviews.applicant_id', 'applicants.id')
                ->whereColumn('interviews.vacancy_id', 'vacancies.id')
                ->where('interviews.interviewer_id', $interviewerId);
        });
    }
}
