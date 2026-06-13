<?php

namespace App\Exports;

use App\Services\SelectionQueryService;
use Illuminate\Contracts\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PipelineExport implements FromQuery, WithHeadings, WithMapping
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
        return $this->queryService->pipelineQuery($this->filters);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Vacancy',
            'Status',
            'Applicants',
        ];
    }

    /**
     * @param  \stdClass  $row
     * @return list<mixed>
     */
    public function map($row): array
    {
        return [
            $row->vacancy_position,
            $row->status,
            $row->count,
        ];
    }
}
