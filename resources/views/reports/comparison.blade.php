<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Comparison Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background-color: #fafafa; }
    </style>
</head>
<body>
    <h1>Candidate Comparison Report</h1>
    <div class="meta">
        @if(!empty($rows->first()->vacancy_position))
            Vacancy: {{ $rows->first()->vacancy_position }}<br>
        @endif
        Generated: {{ now()->format('Y-m-d H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Applicant</th>
                <th>Vacancy</th>
                <th>Test</th>
                <th>Score</th>
                <th>Max Score</th>
                <th>Weight</th>
                <th>Weighted Avg</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->applicant_name }}</td>
                    <td>{{ $row->vacancy_position }}</td>
                    <td>{{ $row->test_name ?? 'N/A' }}</td>
                    <td>{{ $row->score ?? 'N/A' }}</td>
                    <td>{{ $row->max_score ?? 'N/A' }}</td>
                    <td>{{ $row->weight ?? 'N/A' }}</td>
                    <td>{{ $row->weighted_avg ?? 'N/A' }}</td>
                    <td>{{ $row->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
