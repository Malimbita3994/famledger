@extends('layouts.metronic')

@section('title', 'Family Properties')
@section('page_title', 'Family Properties')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <style>
    .properties-kpi-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 1.25rem !important;
        width: 100% !important;
        margin-bottom: 1.5rem !important;
    }

    .properties-filter-row {
        display: grid !important;
        grid-template-columns: minmax(220px, 2fr) repeat(3, minmax(160px, 1fr)) auto !important;
        gap: 0.75rem !important;
        align-items: flex-end !important;
        width: 100% !important;
    }

    .properties-filter-row .kt-input,
    .properties-filter-row .kt-select {
        width: 100% !important;
    }
    </style>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="font-medium text-lg text-mono">Assets list</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Track all properties owned by this family, including value and status.
            </p>
        </div>
        <a href="{{ route('families.properties.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            Add property
        </a>
    </div>

    @if (!empty($kpis))
    <div class="properties-kpi-grid">
        <div class="rounded-xl border border-border bg-card px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-muted-foreground uppercase tracking-wide">Total estimated value</span>
                <span class="text-sm font-semibold text-foreground tabular-nums">{{ number_format($kpis['total_value'] ?? 0, 0) }} {{ $family->currency_code }}</span>
            </div>
            <span class="inline-flex items-center justify-center size-9 rounded-full bg-primary/5">
                <i class="ki-filled ki-home-3 text-primary"></i>
            </span>
        </div>
        <div class="rounded-xl border border-border bg-card px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-muted-foreground uppercase tracking-wide">Active properties</span>
                <span class="text-sm font-semibold text-foreground tabular-nums">{{ $kpis['active_count'] ?? 0 }}</span>
            </div>
            <span class="inline-flex items-center justify-center size-9 rounded-full bg-success/5">
                <i class="ki-filled ki-chart-line-up text-success"></i>
            </span>
        </div>
        <div class="rounded-xl border border-border bg-card px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-muted-foreground uppercase tracking-wide">Sold properties</span>
                <span class="text-sm font-semibold text-foreground tabular-nums">{{ $kpis['sold_count'] ?? 0 }}</span>
            </div>
            <span class="inline-flex items-center justify-center size-9 rounded-full bg-muted">
                <i class="ki-filled ki-arrow-down text-muted-foreground"></i>
            </span>
        </div>
    </div>
    @endif

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($properties->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-home-3 text-4xl mb-2"></i>
                    <p class="text-sm">No properties recorded yet.</p>
                    <a href="{{ route('families.properties.create', $family) }}" class="kt-btn kt-btn-outline mt-4">Add property</a>
                </div>
            @else
                <div class="px-5 pt-4">
                    <form method="GET" class="properties-filter-row">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search code or name" class="kt-input">
                        <select name="category_id" class="kt-select">
                            <option value="">All categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? null) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <select name="ownership_type" class="kt-select">
                            <option value="">All ownership</option>
                            @foreach (['individual' => 'Individual', 'joint' => 'Joint', 'family_trust' => 'Family Trust'] as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['ownership_type'] ?? null) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="kt-select">
                            <option value="">All status</option>
                            @foreach (['active' => 'Active', 'sold' => 'Sold', 'under_mortgage' => 'Under mortgage', 'under_maintenance' => 'Under maintenance', 'disposed' => 'Disposed'] as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['status'] ?? null) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="kt-btn kt-btn-outline kt-btn-sm">Filter</button>
                    </form>
                </div>

                <div class="kt-scrollable-x-auto mt-2">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[140px]">Code</th>
                                <th class="min-w-[200px]">Property</th>
                                <th class="min-w-[140px]">Category</th>
                                <th class="min-w-[140px]">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($properties as $property)
                                <tr>
                                    <td class="text-sm text-muted-foreground">
                                        <a href="{{ route('families.properties.show', [$family, $property]) }}" class="hover:text-primary underline-offset-2 hover:underline">
                                            {{ $property->property_code }}
                                        </a>
                                    </td>
                                    <td class="text-sm font-medium text-foreground">{{ $property->name }}</td>
                                    <td class="text-sm text-secondary-foreground">
                                        {{ $property->category->name ?? '—' }}
                                        @if ($property->subcategory)
                                            <span class="text-xs text-muted-foreground">/ {{ $property->subcategory->name }}</span>
                                        @endif
                                    </td>
                                    <td class="text-sm">
                                        <span class="kt-badge kt-badge-sm kt-badge-outline {{ $property->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-border">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

