<?php

namespace App\Exports;

use App\Services\SelectionQueryService;
use Illuminate\Contracts\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InterviewsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        private array $filters,
        private SelectionQueryService $queryService = new SelectionQueryService
    ) {
        //
    }

    public function query(): Builder
    {
        return $this->queryService->completedInterviewsQuery($this->filters);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Date',
            'Applicant',
            'Interviewer',
            'Vacancy',
            'Observations',
        ];
    }

    /**
     * @param  \stdClass  $row
     * @return list<mixed>
     */
    public function map($row): array
    {
        return [
            $row->scheduled_at,
            $row->applicant_name,
            $row->interviewer_name,
            $row->vacancy_position,
            $row->observations ?? '',
        ];
    }
}
