<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Audit log') }} — FamLedger</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #333; margin: 12px; }
        h1 { font-size: 14px; margin: 0 0 4px 0; }
        .meta { color: #666; margin-bottom: 10px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
        .type-app { color: #2563eb; }
        .type-db { color: #059669; }
        .empty { text-align: center; padding: 24px; color: #666; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>{{ __('FamLedger audit log') }}</h1>
    <p class="meta">{{ $filtersSummary }}<br>
        {{ __('Generated at') }} {{ $generatedAt }} · {{ $logs->count() }} {{ __('entries') }} ({{ __('max 500') }})</p>

    <table>
        <thead>
            <tr>
                <th style="width:10%">{{ __('When') }}</th>
                <th style="width:14%">{{ __('Who') }}</th>
                <th style="width:10%">{{ __('Family') }}</th>
                <th style="width:7%">{{ __('Type') }}</th>
                <th style="width:10%">{{ __('Area') }}</th>
                <th style="width:35%">{{ __('Action / Description') }}</th>
                <th style="width:8%">{{ __('IP') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $log->user ? $log->user->name . ' (' . $log->user->email . ')' : '—' }}</td>
                <td>{{ $log->family ? $log->family->name : '—' }}</td>
                <td class="{{ $log->type === 'database' ? 'type-db' : 'type-app' }}">{{ $log->type === 'database' ? __('Database') : __('Application') }}</td>
                <td>{{ $log->area }}</td>
                <td>
                    <strong>{{ $log->action }}</strong>
                    @if($log->description)
                        <br>{{ \Illuminate\Support\Str::limit(strip_tags($log->description), 120) }}
                    @endif
                </td>
                <td class="nowrap">{{ $log->ip ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">{{ __('No audit entries in this range.') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
