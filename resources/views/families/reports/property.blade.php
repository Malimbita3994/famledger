@extends('layouts.metronic')

@section('title', 'Property Reports')
@section('page_title', 'Property Reports')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <style>
    .property-report-filter-row {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    .property-report-filter-col {
        width: 100%;
    }

    @media (min-width: 900px) {
        .property-report-filter-row {
            flex-direction: row;
            align-items: flex-end;
        }

        .property-report-filter-col {
            flex: 1 1 0;
        }
    }
    </style>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-medium text-lg text-mono">Property reports</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Snapshot of this family&rsquo;s properties with purchase vs. latest valuation and book value.
            </p>
        </div>
        <form method="GET" class="property-report-filter-row mb-3">
            <div class="property-report-filter-col">
                <select name="category_id" class="kt-select w-full">
                    <option value="">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? null) == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="property-report-filter-col">
                <select name="status" class="kt-select w-full">
                    <option value="">All status</option>
                    @foreach (['active' => 'Active', 'sold' => 'Sold', 'under_mortgage' => 'Under mortgage', 'under_maintenance' => 'Under maintenance', 'disposed' => 'Disposed'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="property-report-filter-col flex justify-end">
                <button type="submit" class="kt-btn kt-btn-outline kt-btn-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="kt-card kt-card-grid min-w-full mt-4">
        <div class="kt-card-content p-0">
            @if ($properties->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-home-3 text-4xl mb-2"></i>
                    <p class="text-sm">No properties recorded yet. Add properties to see them in this report.</p>
                </div>
            @else
                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto hidden md:block">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[220px]">Property</th>
                                <th class="min-w-[140px]">Category</th>
                                <th class="min-w-[130px]">Status</th>
                                <th class="min-w-[150px]">Purchase price</th>
                                <th class="min-w-[160px]">Latest valuation</th>
                                <th class="min-w-[160px]">Book value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($properties as $property)
                                @php
                                    $latestVal = $latestValuations[$property->id] ?? null;
                                    $latestDep = $latestDepreciations[$property->id] ?? null;
                                    $purchase = (float) ($property->purchase_price ?? 0);
                                    $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($property->current_estimated_value ?? 0);
                                    $book = $latestDep ? (float) $latestDep->book_value : $valuation;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-mono">{{ $property->name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ $property->property_code }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm">
                                        {{ $property->category->name ?? '—' }}
                                    </td>
                                    <td class="text-sm">
                                        <span class="kt-badge kt-badge-sm {{ $property->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">
                                            {{ ucfirst($property->status ?? 'unknown') }}
                                        </span>
                                    </td>
                                    <td class="tabular-nums text-sm">
                                        {{ $purchase > 0 ? number_format($purchase, 0) . ' ' . $currency : '—' }}
                                    </td>
                                    <td class="tabular-nums text-sm">
                                        {{ $valuation > 0 ? number_format($valuation, 0) . ' ' . $currency : '—' }}
                                    </td>
                                    <td class="tabular-nums text-sm">
                                        {{ $book > 0 ? number_format($book, 0) . ' ' . $currency : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden p-4 space-y-4">
                    @foreach ($properties as $property)
                        @php
                            $latestVal = $latestValuations[$property->id] ?? null;
                            $latestDep = $latestDepreciations[$property->id] ?? null;
                            $purchase = (float) ($property->purchase_price ?? 0);
                            $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($property->current_estimated_value ?? 0);
                            $book = $latestDep ? (float) $latestDep->book_value : $valuation;
                        @endphp
                        <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-4 flex flex-col gap-3">
                            {{-- Header: name, code, status --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-semibold text-foreground truncate">
                                        {{ $property->name }}
                                    </span>
                                    <span class="text-[11px] text-muted-foreground truncate mt-0.5">
                                        {{ $property->property_code }}
                                    </span>
                                    <span class="text-[11px] text-secondary-foreground mt-0.5">
                                        {{ $property->category->name ?? '—' }}
                                    </span>
                                </div>
                                <span class="kt-badge kt-badge-sm {{ $property->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline shrink-0">
                                    {{ ucfirst($property->status ?? 'unknown') }}
                                </span>
                            </div>

                            {{-- Amounts --}}
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[11px] text-muted-foreground border border-border/60 rounded-xl px-3 py-2 bg-muted/30">
                                <div>
                                    <span class="uppercase tracking-wide block mb-0.5">Purchase price</span>
                                    <span class="text-sm font-semibold text-foreground tabular-nums">
                                        {{ $purchase > 0 ? number_format($purchase, 0) . ' ' . $currency : '—' }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="uppercase tracking-wide block mb-0.5">Latest valuation</span>
                                    <span class="text-sm font-semibold text-foreground tabular-nums">
                                        {{ $valuation > 0 ? number_format($valuation, 0) . ' ' . $currency : '—' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="uppercase tracking-wide block mb-0.5">Book value</span>
                                    <span class="text-sm font-semibold text-foreground tabular-nums">
                                        {{ $book > 0 ? number_format($book, 0) . ' ' . $currency : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

