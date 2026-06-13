<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selection Pipeline Report</title>
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
    <h1>Selection Pipeline Report</h1>
    <div class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Vacancy</th>
                <th>Status</th>
                <th>Applicants</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->vacancy_position }}</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
