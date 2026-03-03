@extends('layouts.metronic')

@section('title', 'Property Details')
@section('page_title', 'Property Details')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.properties.assets', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to assets list
    </a>

    <style>
    .property-detail-row {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .property-detail-row .property-detail-col {
        width: 100%;
        transition: transform 160ms ease-out, box-shadow 160ms ease-out, border-color 160ms ease-out, background-color 160ms ease-out;
    }

    .property-detail-row .property-detail-col:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
        border-color: rgba(59, 130, 246, 0.6);
        background-color: rgba(249, 250, 251, 0.95);
    }

    @media (min-width: 900px) {
        .property-detail-row {
            flex-direction: row;
        }

        .property-detail-row .property-detail-col {
            flex: 1 1 0;
        }
    }
    </style>

    <div class="kt-card p-5 lg:p-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-lg font-semibold text-mono">{{ $property->name }}</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    {{ $property->property_code }} · {{ $property->category->name ?? 'Uncategorized' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="kt-badge kt-badge-lg kt-badge-outline {{ $property->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                    {{ ucfirst($property->status ?? 'unknown') }}
                </span>
                <a href="{{ route('families.properties.edit', [$family, $property]) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-pencil me-1"></i>
                    Edit
                </a>
            </div>
        </div>

        <div class="grid gap-5 lg:gap-7.5">
            <div class="property-detail-row">
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Code</div>
                    <div class="text-sm font-medium text-foreground">{{ $property->property_code }}</div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Category</div>
                    <div class="text-sm font-medium text-foreground">
                        {{ $property->category->name ?? '—' }}
                        @if ($property->subcategory)
                            <span class="text-xs text-muted-foreground">/ {{ $property->subcategory->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Ownership type</div>
                    <div class="text-sm font-medium text-foreground">
                        {{ $property->ownership_type ? ucfirst(str_replace('_', ' ', $property->ownership_type)) : '—' }}
                    </div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Currency</div>
                    <div class="text-sm font-medium text-foreground">{{ $property->currency_code ?? $family->currency_code }}</div>
                </div>
            </div>

            <div class="property-detail-row">
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Acquisition date</div>
                    <div class="text-sm font-medium text-foreground">
                        {{ $property->acquisition_date ? $property->acquisition_date->format('Y-m-d') : '—' }}
                    </div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Acquisition method</div>
                    <div class="text-sm font-medium text-foreground">
                        {{ $property->acquisition_method ? ucfirst(str_replace('_', ' ', $property->acquisition_method)) : '—' }}
                    </div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Purchase price</div>
                    <div class="text-sm font-medium text-foreground tabular-nums">
                        {{ $property->purchase_price !== null ? number_format($property->purchase_price, 2) . ' ' . ($property->currency_code ?? $family->currency_code) : '—' }}
                    </div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Current estimated value</div>
                    <div class="text-sm font-medium text-foreground tabular-nums">
                        {{ $property->current_estimated_value !== null ? number_format($property->current_estimated_value, 2) . ' ' . ($property->currency_code ?? $family->currency_code) : '—' }}
                    </div>
                </div>
            </div>

            <div class="property-detail-row">
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Country</div>
                    <div class="text-sm font-medium text-foreground">{{ $property->country ?? $family->country ?? '—' }}</div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Region / City</div>
                    <div class="text-sm font-medium text-foreground">{{ $property->region_city ?? '—' }}</div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Address</div>
                    <div class="text-sm font-medium text-foreground">{{ $property->address ?? '—' }}</div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">GPS</div>
                    <div class="text-sm font-medium text-foreground">
                        @if ($property->gps_lat && $property->gps_lng)
                            {{ $property->gps_lat }}, {{ $property->gps_lng }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

            <div class="property-detail-row">
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Title number / registration</div>
                    <div class="text-sm font-medium text-foreground">{{ $property->title_number ?? '—' }}</div>
                </div>
                <div class="property-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Notes</div>
                    <div class="text-sm text-foreground whitespace-pre-line">{{ $property->notes ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

