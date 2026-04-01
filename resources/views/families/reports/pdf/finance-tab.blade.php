<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $tabLabel }} – {{ $family->name }}</title>
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
.positive { color: #16a34a; }
.footer { margin-top: 18px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
@include('partials.pdf-brand-header')
<h1>{{ $tabLabel }}</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">
    Period: {{ $dateFrom }} to {{ $dateTo }}
    &nbsp;·&nbsp; Currency: {{ $currency }}
    &nbsp;·&nbsp; Generated {{ $generatedAt }}
</div>

@if($tab === 'income')
<div class="section-title">Summary</div>
<table class="kpi-table">
<tr>
    <td><div class="kpi-label">{{ __('Total income') }}</div><div class="kpi-value positive">{{ number_format($totalIncome ?? 0, 0) }} {{ $currency }}</div></td>
</tr>
</table>
@if($bySource->isNotEmpty())
<div class="section-title">{{ __('By source') }}</div>
<table>
<thead><tr><th>{{ __('Source') }}</th><th class="right">{{ __('Amount') }}</th><th class="right">%</th></tr></thead>
<tbody>
@foreach($bySource as $row)
<tr>
    <td>{{ $row['name'] }}</td>
    <td class="right">{{ number_format($row['total'], 0) }} {{ $currency }}</td>
    <td class="right">{{ $row['percent'] }}%</td>
</tr>
@endforeach
</tbody>
</table>
@endif
@endif

@if($tab === 'expense')
<div class="section-title">Summary</div>
<table class="kpi-table">
<tr>
    <td><div class="kpi-label">{{ __('Total expenses') }}</div><div class="kpi-value">{{ number_format($totalExpenses ?? 0, 0) }} {{ $currency }}</div></td>
</tr>
</table>
@if($byCategory->isNotEmpty())
<div class="section-title">{{ __('By category') }}</div>
<table>
<thead><tr><th>{{ __('Category') }}</th><th class="right">{{ __('Amount') }}</th><th class="right">%</th></tr></thead>
<tbody>
@foreach($byCategory as $row)
<tr>
    <td>{{ $row['name'] }}</td>
    <td class="right">{{ number_format($row['total'], 0) }} {{ $currency }}</td>
    <td class="right">{{ $row['percent'] }}%</td>
</tr>
@endforeach
</tbody>
</table>
@endif
@endif

@if($tab === 'transfer')
<div class="section-title">Summary</div>
<table class="kpi-table">
<tr>
    <td><div class="kpi-label">{{ __('Total transferred (listed)') }}</div><div class="kpi-value">{{ number_format($totalTransferred ?? 0, 0) }} {{ $currency }}</div></td>
</tr>
</table>
@if($transfers->isNotEmpty())
<div class="section-title">{{ __('Transfers') }}</div>
<table>
<thead><tr><th>{{ __('Date') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th class="right">{{ __('Amount') }}</th></tr></thead>
<tbody>
@foreach($transfers as $t)
<tr>
    <td>{{ $t->transfer_date?->format('Y-m-d') }}</td>
    <td>{{ $t->fromWallet->name ?? '—' }}</td>
    <td>{{ $t->toWallet->name ?? '—' }}</td>
    <td class="right">{{ number_format((float) $t->amount, 0) }} {{ $currency }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif
@endif

@if($tab === 'savings')
@if(count($savingsRows ?? []) > 0)
<div class="section-title">{{ __('Savings goals') }}</div>
<table>
<thead><tr><th>{{ __('Goal') }}</th><th>{{ __('Wallet') }}</th><th class="right">{{ __('Target') }}</th><th class="right">{{ __('Saved') }}</th><th class="right">%</th><th class="right">{{ __('Remaining') }}</th></tr></thead>
<tbody>
@foreach($savingsRows as $row)
<tr>
    <td>{{ $row['goal']->name }}</td>
    <td>{{ $row['goal']->wallet->name ?? '—' }}</td>
    <td class="right">{{ number_format($row['target'], 0) }}</td>
    <td class="right">{{ number_format($row['saved'], 0) }}</td>
    <td class="right">{{ $row['percent'] }}%</td>
    <td class="right">{{ number_format($row['remaining'], 0) }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p style="color:#6b7280;font-size:9px;">{{ __('No savings goals yet.') }}</p>
@endif
@endif

<div class="footer">{{ config('app.name') }} — {{ $tabLabel }}</div>
</body>
</html>
