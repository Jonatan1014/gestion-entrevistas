<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Interviews Report</title>
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
    <h1>Completed Interviews Report</h1>
    <div class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Applicant</th>
                <th>Interviewer</th>
                <th>Vacancy</th>
                <th>Observations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->scheduled_at }}</td>
                    <td>{{ $row->applicant_name }}</td>
                    <td>{{ $row->interviewer_name }}</td>
                    <td>{{ $row->vacancy_position }}</td>
                    <td>{{ $row->observations ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
