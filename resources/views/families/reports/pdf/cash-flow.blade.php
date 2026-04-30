<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Finance – {{ $family->name }}</title>
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
@include('partials.pdf-brand-header')
<h1>Finance</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">
    Period: {{ $dateFrom }} to {{ $dateTo }}
    &nbsp;·&nbsp; Currency: {{ $currency }}
    &nbsp;·&nbsp; Generated {{ $generatedAt }}
</div>

<div class="section-title">Cash Flow Summary</div>
<table class="kpi-table">
<tr>
    <td style="width:20%"><div class="kpi-label">Opening Balance</div><div class="kpi-value">{{ number_format($openingBalance, 0) }} {{ $currency }}</div></td>
    <td style="width:20%"><div class="kpi-label">Total Income</div><div class="kpi-value positive">{{ number_format($totalIncome, 0) }} {{ $currency }}</div></td>
    <td style="width:20%"><div class="kpi-label">Total Expenses</div><div class="kpi-value negative">{{ number_format($totalExpenses, 0) }} {{ $currency }}</div></td>
    <td style="width:20%"><div class="kpi-label">Net Flow</div><div class="kpi-value {{ $netFlow >= 0 ? 'positive' : 'negative' }}">{{ number_format($netFlow, 0) }} {{ $currency }}</div></td>
    <td style="width:20%"><div class="kpi-label">Closing Balance</div><div class="kpi-value">{{ number_format($closingBalance, 0) }} {{ $currency }}</div></td>
</tr>
</table>

@if($bySource->isNotEmpty())
<div class="section-title">Income by Source</div>
<table>
    <thead><tr><th>Source</th><th class="right">Amount ({{ $currency }})</th><th class="right">%</th></tr></thead>
    <tbody>
        @foreach($bySource as $row)
        <tr><td>{{ $row['name'] }}</td><td class="right">{{ number_format($row['total'], 0) }}</td><td class="right">{{ $row['percent'] }}%</td></tr>
        @endforeach
    </tbody>
</table>
@endif

@if($byCategory->isNotEmpty())
<div class="section-title">Expenses by Category</div>
<table>
    <thead><tr><th>Category</th><th class="right">Amount ({{ $currency }})</th><th class="right">%</th></tr></thead>
    <tbody>
        @foreach($byCategory as $row)
        <tr><td>{{ $row['name'] }}</td><td class="right">{{ number_format($row['total'], 0) }}</td><td class="right">{{ $row['percent'] }}%</td></tr>
        @endforeach
    </tbody>
</table>
@endif

@if($transfers->isNotEmpty())
<div class="section-title">Transfers ({{ $transfers->count() }} · Total: {{ number_format($totalTransferred, 0) }} {{ $currency }})</div>
<table>
    <thead><tr><th>Date</th><th>From</th><th>To</th><th class="right">Amount</th></tr></thead>
    <tbody>
        @foreach($transfers as $t)
        <tr>
            <td>{{ $t->transfer_date->format('M j, Y') }}</td>
            <td>{{ $t->fromWallet?->name ?? '—' }}</td>
            <td>{{ $t->toWallet?->name ?? '—' }}</td>
            <td class="right">{{ number_format($t->amount, 0) }} {{ $currency }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(!empty($budgetRows))
<div class="section-title">Budget Overview</div>
<table>
    <thead><tr><th>Budget</th><th class="right">Planned</th><th class="right">Used</th><th class="right">%</th></tr></thead>
    <tbody>
        @foreach($budgetRows as $row)
        <tr>
            <td>{{ $row['budget']->name }}</td>
            <td class="right">{{ number_format($row['planned'], 0) }}</td>
            <td class="right {{ $row['over'] ? 'negative' : '' }}">{{ number_format($row['used'], 0) }}</td>
            <td class="right">{{ $row['percent'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(!empty($savingsRows))
<div class="section-title">Savings Goals</div>
<table>
    <thead><tr><th>Goal</th><th class="right">Target</th><th class="right">Saved</th><th class="right">%</th></tr></thead>
    <tbody>
        @foreach($savingsRows as $row)
        <tr>
            <td>{{ $row['goal']->name }}</td>
            <td class="right">{{ number_format($row['target'], 0) }}</td>
            <td class="right">{{ number_format($row['saved'], 0) }}</td>
            <td class="right">{{ $row['percent'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">FamLedger · {{ $family->name }} · Generated {{ $generatedAt }}</div>
</body>
</html>
