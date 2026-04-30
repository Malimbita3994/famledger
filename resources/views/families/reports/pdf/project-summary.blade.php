<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Project – {{ $family->name }}</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; margin: 16px 20px; }
h1 { font-size: 15px; margin: 0 0 2px 0; font-weight: bold; }
.subtitle { color: #555; font-size: 9px; margin-bottom: 12px; }
.meta { color: #777; font-size: 8px; margin-bottom: 14px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
.section-title { font-size: 10px; font-weight: bold; color: #374151; margin: 14px 0 6px; text-transform: uppercase; letter-spacing: 0.5px; }
table { width: 100%; border-collapse: collapse; margin-top: 6px; }
th { background: #f3f4f6; font-weight: bold; padding: 5px 7px; text-align: left; border: 1px solid #d1d5db; font-size: 8px; }
td { border: 1px solid #e5e7eb; padding: 5px 7px; font-size: 8px; }
td.right { text-align: right; font-variant-numeric: tabular-nums; }
tr:nth-child(even) { background: #f9fafb; }
.kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.kpi-table td { border: 1px solid #e5e7eb; padding: 8px 10px; }
.kpi-label { font-size: 8px; color: #6b7280; text-transform: uppercase; margin-bottom: 3px; }
.kpi-value { font-size: 12px; font-weight: bold; color: #111827; }
.footer { margin-top: 18px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
@include('partials.pdf-brand-header')
<h1>Project</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">Currency: {{ $currency }} &nbsp;·&nbsp; Generated {{ $generatedAt }}</div>

<div class="section-title">Overview</div>
<table class="kpi-table">
<tr>
    <td style="width:25%"><div class="kpi-label">Total Projects</div><div class="kpi-value">{{ $totalProjects }}</div></td>
    <td style="width:25%"><div class="kpi-label">Active</div><div class="kpi-value">{{ $activeCount }}</div></td>
    <td style="width:25%"><div class="kpi-label">Completed</div><div class="kpi-value">{{ $completedCount }}</div></td>
    <td style="width:25%"><div class="kpi-label">Total Budget</div><div class="kpi-value">{{ number_format($projects->sum(fn($p) => (float)($p->budget?->amount ?? 0)), 0) }} {{ $currency }}</div></td>
</tr>
</table>

<div class="section-title">Projects ({{ $projects->count() }})</div>
@if($projects->isEmpty())
<p style="color:#6b7280">No projects found.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Project Name</th>
            <th>Status</th>
            <th class="right">Budget ({{ $currency }})</th>
            <th class="right">Funded</th>
            <th class="right">Spent</th>
            <th class="right">Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projects as $i => $project)
        @php
            $budgetAmt = (float) ($project->budget?->amount ?? 0);
            $funded    = (float) ($project->fundings_sum_amount ?? 0);
            $spent     = (float) ($project->expenses_sum_amount ?? 0);
            $balance   = $funded - $spent;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td style="font-weight:bold">{{ $project->name }}</td>
            <td>{{ ucfirst($project->status) }}</td>
            <td class="right">{{ $budgetAmt > 0 ? number_format($budgetAmt, 0) : '—' }}</td>
            <td class="right">{{ number_format($funded, 0) }}</td>
            <td class="right">{{ number_format($spent, 0) }}</td>
            <td class="right" style="{{ $balance < 0 ? 'color:#dc2626' : '' }}">{{ number_format($balance, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">FamLedger · {{ $family->name }} · Generated {{ $generatedAt }}</div>
</body>
</html>
