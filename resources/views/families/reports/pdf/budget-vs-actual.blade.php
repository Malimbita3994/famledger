<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Budget vs Actual – {{ $family->name }}</title>
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
.over { color: #dc2626; font-weight: bold; }
.kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.kpi-table td { border: 1px solid #e5e7eb; padding: 8px 10px; width: 50%; }
.kpi-label { font-size: 8px; color: #6b7280; text-transform: uppercase; margin-bottom: 3px; }
.kpi-value { font-size: 13px; font-weight: bold; color: #111827; }
.footer { margin-top: 18px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
@include('partials.pdf-brand-header')
<h1>Budget vs Actual</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">
    Period: {{ $dateFrom }} to {{ $dateTo }}
    &nbsp;·&nbsp; Currency: {{ $currency }}
    &nbsp;·&nbsp; Generated {{ $generatedAt }}
</div>

@php
    $budgetCount = count($rows ?? []);
    $budgetTotalPlanned = collect($rows ?? [])->sum('planned');
    $budgetTotalUsed = collect($rows ?? [])->sum('used');
@endphp

<div class="section-title">Summary</div>
<table class="kpi-table">
<tr>
    <td>
        <div class="kpi-label">Total Planned</div>
        <div class="kpi-value">{{ number_format($budgetTotalPlanned, 0) }} {{ $currency }}</div>
    </td>
    <td>
        <div class="kpi-label">Total Spent</div>
        <div class="kpi-value {{ $budgetTotalUsed > $budgetTotalPlanned ? 'over' : '' }}">{{ number_format($budgetTotalUsed, 0) }} {{ $currency }}</div>
    </td>
</tr>
</table>

@if($motherBudget)
<div class="section-title">Main Family Budget: {{ $motherBudget->name }}</div>
<table>
    <tbody>
        <tr><td style="width:50%">Allocated</td><td class="right">{{ number_format($motherBudget->amount, 0) }} {{ $motherBudget->currency_code ?? $currency }}</td></tr>
        <tr><td>Spent</td><td class="right {{ $motherBudget->is_exceeded ? 'over' : '' }}">{{ number_format($motherBudget->used_amount, 0) }} {{ $motherBudget->currency_code ?? $currency }}</td></tr>
        <tr><td>Remaining</td><td class="right {{ $motherBudget->is_exceeded ? 'over' : '' }}">{{ number_format($motherBudget->remaining_amount, 0) }} {{ $motherBudget->currency_code ?? $currency }}</td></tr>
    </tbody>
</table>
@endif

<div class="section-title">Budget Breakdown ({{ $budgetCount }} budgets)</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Budget Name</th>
            <th>Period</th>
            <th class="right">Planned ({{ $currency }})</th>
            <th class="right">Spent ({{ $currency }})</th>
            <th class="right">Remaining</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $sn => $row)
        <tr>
            <td>{{ $sn + 1 }}</td>
            <td style="font-weight: {{ ($motherBudget && $row['budget']->id === $motherBudget->id) ? 'bold' : 'normal' }}">{{ $row['budget']->name }}</td>
            <td>{{ $budgetRecurrences[$row['budget']->recurrence] ?? 'Single period' }}</td>
            <td class="right">{{ number_format($row['planned'], 0) }}</td>
            <td class="right {{ $row['over'] ? 'over' : '' }}">{{ number_format($row['used'], 0) }}</td>
            <td class="right {{ $row['over'] ? 'over' : '' }}">{{ number_format($row['remaining'], 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center; color:#6b7280">No budgets in this period.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">FamLedger · {{ $family->name }} · Generated {{ $generatedAt }}</div>
</body>
</html>
