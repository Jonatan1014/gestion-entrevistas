<?php

namespace App\Exports;

use App\Services\SelectionQueryService;
use Illuminate\Contracts\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComparisonExport implements FromQuery, WithHeadings, WithMapping
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
        return $this->queryService->comparisonQuery($this->filters);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Applicant',
            'Vacancy',
            'Test',
            'Score',
            'Max Score',
            'Weight',
            'Weighted Average',
            'Status',
        ];
    }

    /**
     * @param  \stdClass  $row
     * @return list<mixed>
     */
    public function map($row): array
    {
        return [
            $row->applicant_name,
            $row->vacancy_position,
            $row->test_name ?? 'N/A',
            $row->score ?? 'N/A',
            $row->max_score ?? 'N/A',
            $row->weight ?? 'N/A',
            $row->weighted_avg ?? 'N/A',
            $row->status,
        ];
    }
}
