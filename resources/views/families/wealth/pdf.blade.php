<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Family Wealth – {{ $family->name }}</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; margin: 16px 20px; }
h1 { font-size: 15px; margin: 0 0 2px 0; font-weight: bold; }
.subtitle { color: #555; font-size: 9px; margin-bottom: 12px; }
.meta { color: #777; font-size: 8px; margin-bottom: 14px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
.section-title { font-size: 10px; font-weight: bold; color: #374151; margin: 14px 0 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-row { width: 100%; margin-bottom: 12px; }
.kpi-table { width: 100%; border-collapse: collapse; }
.kpi-table td { border: 1px solid #e5e7eb; padding: 8px 10px; width: 25%; }
.kpi-label { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 3px; }
.kpi-value { font-size: 13px; font-weight: bold; color: #111827; }
.kpi-main { background: #f0f9ff; }
.alloc-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
.alloc-table td { border: 1px solid #e5e7eb; padding: 6px 10px; }
table { width: 100%; border-collapse: collapse; margin-top: 6px; }
th { background: #f3f4f6; font-weight: bold; padding: 5px 7px; text-align: left; border: 1px solid #d1d5db; font-size: 8px; }
td { border: 1px solid #e5e7eb; padding: 5px 7px; font-size: 8px; }
td.right { text-align: right; font-variant-numeric: tabular-nums; }
tr:nth-child(even) { background: #f9fafb; }
.footer { margin-top: 18px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
@include('partials.pdf-brand-header')
<h1>Family Wealth Overview</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">Generated {{ $generatedAt }} &nbsp;·&nbsp; Currency: {{ $currency }}</div>

<div class="section-title">Net Worth Summary</div>
<table class="kpi-table">
<tr>
    <td class="kpi-main">
        <div class="kpi-label">Total Family Wealth</div>
        <div class="kpi-value">{{ number_format($overview['net_wealth'], 0) }} {{ $currency }}</div>
    </td>
    <td>
        <div class="kpi-label">Wallets</div>
        <div class="kpi-value">{{ number_format($overview['wallet_total'], 0) }} {{ $currency }}</div>
    </td>
    <td>
        <div class="kpi-label">Properties</div>
        <div class="kpi-value">{{ number_format($overview['property_total'], 0) }} {{ $currency }}</div>
    </td>
    <td>
        <div class="kpi-label">Projects</div>
        <div class="kpi-value">{{ number_format($overview['project_total'], 0) }} {{ $currency }}</div>
    </td>
</tr>
</table>

<div class="section-title">Asset Allocation</div>
<table>
    <thead><tr><th>Asset Class</th><th class="right">Allocation %</th></tr></thead>
    <tbody>
        <tr><td>Wallets</td><td class="right">{{ $allocation['wallet_pct'] }}%</td></tr>
        <tr><td>Properties</td><td class="right">{{ $allocation['property_pct'] }}%</td></tr>
        <tr><td>Projects</td><td class="right">{{ $allocation['project_pct'] }}%</td></tr>
        <tr style="font-weight:bold"><td>Liabilities (deducted)</td><td class="right">{{ number_format($overview['liability_total'], 0) }} {{ $currency }}</td></tr>
    </tbody>
</table>

@if($trend->isNotEmpty())
<div class="section-title">Wealth Trend</div>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th class="right">Wallets</th>
            <th class="right">Properties</th>
            <th class="right">Projects</th>
            <th class="right">Liabilities</th>
            <th class="right">Net Wealth</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trend as $row)
        <tr>
            <td>{{ $row->snapshot_date->format('M j, Y') }}</td>
            <td class="right">{{ number_format($row->wallet_total, 0) }}</td>
            <td class="right">{{ number_format($row->property_total, 0) }}</td>
            <td class="right">{{ number_format($row->project_total, 0) }}</td>
            <td class="right">{{ number_format($row->liability_total, 0) }}</td>
            <td class="right" style="font-weight:bold">{{ number_format($row->net_wealth, 0) }} {{ $currency }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">FamLedger · {{ $family->name }} · Generated {{ $generatedAt }}</div>
</body>
</html>
