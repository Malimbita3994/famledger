<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Property Report – {{ $family->name }}</title>
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
.footer { margin-top: 18px; font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
@include('partials.pdf-brand-header')
<h1>Property Report</h1>
<div class="subtitle">{{ $family->name }}</div>
<div class="meta">
    Currency: {{ $currency }}
    &nbsp;·&nbsp; {{ $properties->count() }} properties
    &nbsp;·&nbsp; Generated {{ $generatedAt }}
</div>

<div class="section-title">Properties ({{ $properties->count() }})</div>
@if($properties->isEmpty())
<p style="color:#6b7280">No properties recorded.</p>
@else
<table>
    <thead>
        <tr>
            <th>Property</th>
            <th>Code</th>
            <th>Category</th>
            <th>Status</th>
            <th class="right">Purchase Price</th>
            <th class="right">Latest Valuation</th>
            <th class="right">Book Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($properties as $property)
        @php
            $latestVal = $latestValuations[$property->id] ?? null;
            $latestDep = $latestDepreciations[$property->id] ?? null;
            $purchase  = (float) ($property->purchase_price ?? 0);
            $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($property->current_estimated_value ?? 0);
            $book      = $latestDep ? (float) $latestDep->book_value : $valuation;
        @endphp
        <tr>
            <td style="font-weight:bold">{{ $property->name }}</td>
            <td>{{ $property->property_code }}</td>
            <td>{{ $property->category->name ?? '—' }}</td>
            <td>{{ ucfirst($property->status ?? 'unknown') }}</td>
            <td class="right">{{ $purchase > 0 ? number_format($purchase, 0) . ' ' . $currency : '—' }}</td>
            <td class="right">{{ $valuation > 0 ? number_format($valuation, 0) . ' ' . $currency : '—' }}</td>
            <td class="right">{{ $book > 0 ? number_format($book, 0) . ' ' . $currency : '—' }}</td>
        </tr>
        @endforeach
        <tr style="font-weight:bold; background:#f3f4f6">
            <td colspan="4">Total</td>
            <td class="right">{{ number_format($properties->sum(fn($p) => (float)($p->purchase_price ?? 0)), 0) }} {{ $currency }}</td>
            <td class="right">—</td>
            <td class="right">{{ number_format($properties->sum(function($p) use ($latestValuations, $latestDepreciations) {
                $latestVal = $latestValuations[$p->id] ?? null;
                $latestDep = $latestDepreciations[$p->id] ?? null;
                $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($p->current_estimated_value ?? 0);
                return $latestDep ? (float) $latestDep->book_value : $valuation;
            }), 0) }} {{ $currency }}</td>
        </tr>
    </tbody>
</table>
@endif

<div class="footer">FamLedger · {{ $family->name }} · Generated {{ $generatedAt }}</div>
</body>
</html>
