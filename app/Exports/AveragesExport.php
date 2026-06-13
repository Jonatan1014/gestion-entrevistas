<?php

namespace App\Exports;

use App\Services\SelectionQueryService;
use Illuminate\Contracts\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AveragesExport implements FromQuery, WithHeadings, WithMapping
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
        return $this->queryService->averagesQuery($this->filters);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Test',
            'Vacancy',
            'Average Score',
            'Scored Applicants',
        ];
    }

    /**
     * @param  \stdClass  $row
     * @return list<mixed>
     */
    public function map($row): array
    {
        return [
            $row->test_name,
            $row->vacancy_position,
            round($row->avg_score, 2),
            $row->scored_count,
        ];
    }
}
