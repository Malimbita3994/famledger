@extends('layouts.metronic')

@section('title', 'Family Properties')
@section('page_title', 'Family Properties')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.overview') }}">
        Back to {{ $family->name }}
    </x-fin-back-link>

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
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
        align-items: flex-end !important;
        width: 100% !important;
    }

    .properties-filter-row .kt-input,
    .properties-filter-row .kt-select {
        width: 100% !important;
    }

    @media (min-width: 900px) {
        .properties-filter-row {
            grid-template-columns: minmax(220px, 2fr) repeat(3, minmax(160px, 1fr)) auto !important;
        }
    }
    </style>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="font-medium text-lg text-mono">Assets list</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Track all properties owned by this family, including value and status.
            </p>
        </div>
        <a href="{{ route('families.properties.create') }}" class="kt-btn kt-btn-primary">
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
                    <a href="{{ route('families.properties.create') }}" class="kt-btn kt-btn-primary mt-4">Add property</a>
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

                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto mt-2 hidden md:block">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[140px]">Code</th>
                                <th class="min-w-[200px]">Property</th>
                                <th class="min-w-[140px]">Category</th>
                                <th class="min-w-[140px] text-right">Price</th>
                                <th class="min-w-[140px]">Status</th>
                                <th class="w-[60px]">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($properties as $property)
                                <tr>
                                    <td class="text-sm text-muted-foreground">
                                        <button
                                            type="button"
                                            class="hover:text-primary underline-offset-2 hover:underline bg-transparent border-0 p-0 cursor-pointer font-inherit text-left text-inherit"
                                            data-property-modal="{{ $property->id }}"
                                            title="{{ __('View details') }}"
                                        >
                                            {{ $property->property_code }}
                                        </button>
                                    </td>
                                    <td class="text-sm font-medium text-foreground">{{ $property->name }}</td>
                                    <td class="text-sm text-secondary-foreground">
                                        {{ $property->category->name ?? '—' }}
                                        @if ($property->subcategory)
                                            <span class="text-xs text-muted-foreground">/ {{ $property->subcategory->name }}</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-right tabular-nums">
                                        {{ number_format($property->purchase_price ?? 0, 0) }} {{ $family->currency_code }}
                                    </td>
                                    <td class="text-sm">
                                        <span class="kt-badge kt-badge-sm kt-badge-outline {{ $property->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="kt-menu flex-inline" data-kt-menu="true">
                                            <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button" aria-label="{{ __('Actions') }}">
                                                    <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                </button>
                                                <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                    <div class="kt-menu-item">
                                                        <button type="button" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer" data-property-modal="{{ $property->id }}">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                            <span class="kt-menu-title">{{ __('View') }}</span>
                                                        </button>
                                                    </div>
                                                    <div class="kt-menu-item">
                                                        <a class="kt-menu-link" href="{{ route('families.properties.edit', $property) }}">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                            <span class="kt-menu-title">Edit</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden p-4 space-y-4">
                    @foreach ($properties as $property)
                        <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-4 flex flex-col gap-3">
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
                                        @if ($property->subcategory)
                                            / {{ $property->subcategory->name }}
                                        @endif
                                        <span class="block mt-0.5">
                                            <span class="text-[11px] text-muted-foreground">Price:</span>
                                            <span class="text-[11px] font-semibold text-foreground tabular-nums">
                                                {{ number_format($property->purchase_price ?? 0, 0) }} {{ $family->currency_code }}
                                            </span>
                                        </span>
                                    </span>
                                </div>
                                <span class="kt-badge kt-badge-sm kt-badge-outline {{ $property->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} shrink-0">
                                    {{ ucfirst($property->status) }}
                                </span>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2 pt-1 border-t border-border">
                                <button type="button" class="kt-btn kt-btn-primary kt-btn-sm" data-property-modal="{{ $property->id }}">
                                    {{ __('View') }}
                                </button>
                                <a href="{{ route('families.properties.edit', $property) }}" class="kt-btn kt-btn-primary kt-btn-sm inline-flex items-center gap-1">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-5 py-3 border-t border-border">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<x-famledger.entity-detail-modal
    id="property_details_modal"
    :title="__('Property details')"
    :payloads="$propertyModalPayloads"
    :open-on-load="$openPropertyModalId"
    variant="grid4"
    trigger-attribute="data-property-modal"
/>
@endsection

