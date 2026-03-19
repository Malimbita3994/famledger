<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Reports Overview – {{ $family->name }}</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; margin: 16px 20px; }
h1 { font-size: 15px; margin: 0 0 2px 0; font-weight: bold; }
.subtitle { color: #555; font-size: 9px; margin-bottom: 12px; }
.meta { color: #777; font-size: 8px; margin-bottom: 14px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
.section-title { font-size: 10px; font-weight: bold; color: #374151; margin: 14px 0 6px; text-transform: uppercase; letter-spacing: 0.5px; }
table { width: 100%; border-collapse: collapse; margin-top: 6px; }
th { background: #f3f4f6; font-weight: bold; padding: 5px 7px; text-align: left; border: 1px solid #d1d5db; font-size: 8px; }
td { border: 1px solid #e5e7eb; padding: 6px 8px; font-size: 8px; }
td.right { text-align: right; font-variant-numeric: tabular-nums; }
tr:nth-child(even) { background: #f9fafb; }
.kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.kpi-table td { border: 1px solid #e5e7eb; padding: 8px 10px; }
.kpi-label { font-size: 8px; color: #6b7280; text-transform: uppercase; margin-bottom: 3px; }
.kpi-value { font-size: 13px; font-weight: bold; color: #111827; }
.positive { color: #16a34a; } .negative { color: #dc2626; }
.footer { margin-top: 18px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
<h1>Reports Overview</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">
    Period: {{ $dateFrom }} to {{ $dateTo }}
    &nbsp;·&nbsp; Currency: {{ $currency }}
    &nbsp;·&nbsp; Generated {{ $generatedAt }}
</div>

<div class="section-title">Financial Summary</div>
<table class="kpi-table">
<tr>
    <td style="width:25%"><div class="kpi-label">Total Income</div><div class="kpi-value positive">{{ number_format($totalIncome, 0) }} {{ $currency }}</div></td>
    <td style="width:25%"><div class="kpi-label">Total Expenses</div><div class="kpi-value negative">{{ number_format($totalExpenses, 0) }} {{ $currency }}</div></td>
    <td style="width:25%"><div class="kpi-label">Net Savings</div><div class="kpi-value {{ $savings >= 0 ? 'positive' : 'negative' }}">{{ number_format($savings, 0) }} {{ $currency }}</div></td>
    <td style="width:25%"><div class="kpi-label">Active Projects</div><div class="kpi-value">{{ $activeProjects }}</div></td>
</tr>
</table>

<div class="section-title">Budget Summary</div>
<table>
    <tbody>
        <tr><td style="width:60%">Total Budget (period)</td><td class="right">{{ number_format($totalBudget, 0) }} {{ $currency }}</td></tr>
        <tr><td>Total Used</td><td class="right {{ $totalBudgetUsed > $totalBudget ? 'negative' : '' }}">{{ number_format($totalBudgetUsed, 0) }} {{ $currency }}</td></tr>
        <tr><td>Usage %</td><td class="right">{{ $budgetUsedPercent }}%</td></tr>
    </tbody>
</table>

@if($budgetRows)
<div class="section-title">Budget Breakdown</div>
<table>
    <thead><tr><th>Budget</th><th class="right">Planned</th><th class="right">Used</th><th class="right">Over?</th></tr></thead>
    <tbody>
        @foreach($budgetRows as $row)
        <tr>
            <td>{{ $row['budget']->name }}</td>
            <td class="right">{{ number_format($row['planned'], 0) }}</td>
            <td class="right {{ $row['over'] ? 'negative' : '' }}">{{ number_format($row['used'], 0) }}</td>
            <td class="right">{{ $row['over'] ? 'Yes' : 'No' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">FamLedger · {{ $family->name }} · Generated {{ $generatedAt }}</div>
</body>
</html>
