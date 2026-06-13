<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Score Averages Report</title>
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
    <h1>Score Averages Report</h1>
    <div class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Test</th>
                <th>Vacancy</th>
                <th>Average Score</th>
                <th>Scored Applicants</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->test_name }}</td>
                    <td>{{ $row->vacancy_position }}</td>
                    <td>{{ number_format($row->avg_score, 2) }}</td>
                    <td>{{ $row->scored_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
