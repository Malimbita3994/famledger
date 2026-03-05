<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Audit Trail – {{ $family->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #333; margin: 12px; }
        h1 { font-size: 14px; margin: 0 0 4px 0; }
        .meta { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
        .type-app { color: #2563eb; }
        .type-db { color: #059669; }
        .empty { text-align: center; padding: 24px; color: #666; }
    </style>
</head>
<body>
    <h1>Audit Trail – {{ $family->name }}</h1>
    <p class="meta">Generated at {{ $generatedAt }} · {{ $logs->count() }} entries</p>

    <table>
        <thead>
            <tr>
                <th style="width:12%">When</th>
                <th style="width:14%">Who</th>
                <th style="width:8%">Type</th>
                <th style="width:8%">Action</th>
                <th style="width:12%">Area</th>
                <th style="width:34%">Description</th>
                <th style="width:12%">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $log->user ? $log->user->name . ' (' . $log->user->email . ')' : '—' }}</td>
                <td class="{{ $log->type === 'database' ? 'type-db' : 'type-app' }}">{{ $log->type === 'database' ? 'Database' : 'Application' }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->area }}</td>
                <td>{{ Str::limit($log->description ?? '—', 80) }}</td>
                <td>{{ $log->ip ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">No audit entries in this range.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
