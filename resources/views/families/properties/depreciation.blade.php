@extends('layouts.metronic')

@section('title', 'Property Depreciation')
@section('page_title', 'Property Depreciation')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.properties.assets', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to assets list
    </a>

    <style>
    .depr-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .depr-main-row .depr-main-col {
        width: 100%;
    }

    .depr-filter-row {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    .depr-filter-row .depr-filter-col {
        width: 100%;
    }

    @media (min-width: 900px) {
        .depr-main-row {
            flex-direction: row;
        }

        .depr-main-row .depr-main-col {
            flex: 1 1 0;
        }

        .depr-filter-row {
            flex-direction: row;
            align-items: flex-end;
        }

        .depr-filter-row .depr-filter-col {
            flex: 1 1 0;
        }
    }
    </style>

    <div class="kt-card p-5 lg:p-7.5 mb-5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-lg font-semibold text-mono">Depreciation</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Model depreciation for properties to understand book value over time.
                </p>
            </div>
            <form method="GET" class="depr-filter-row">
                <div class="depr-filter-col">
                    <select name="property_id" class="kt-select w-full">
                        <option value="">All properties</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected(($filters['property_id'] ?? null) == $prop->id)>
                                {{ $prop->property_code }} · {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="depr-filter-col flex items-end gap-2">
                    <div class="flex flex-col flex-1">
                        <label class="kt-form-label text-xs" for="year">Year</label>
                        <input id="year" type="number" name="year" value="{{ $filters['year'] ?? '' }}" class="kt-input h-9" min="1900" max="{{ now()->year + 10 }}">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="kt-btn kt-btn-outline kt-btn-sm mt-2 lg:mt-0">
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <form method="POST" action="{{ route('families.properties.depreciation.store', $family) }}" class="grid gap-4 lg:gap-5">
            @csrf
            <div class="depr-main-row">
                <div class="depr-main-col grid gap-1.5">
                    <label for="depr_property_id" class="kt-form-label text-xs">Property <span class="text-destructive">*</span></label>
                    <select id="depr_property_id" name="property_id" class="kt-select">
                        <option value="">Select property</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected(old('property_id') == $prop->id)>
                                {{ $prop->property_code }} · {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('property_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="depr-main-col grid gap-1.5">
                    <label for="method" class="kt-form-label text-xs">Method <span class="text-destructive">*</span></label>
                    <select id="method" name="method" class="kt-select">
                        @foreach (['straight_line' => 'Straight line', 'declining_balance' => 'Declining balance', 'manual' => 'Manual entry'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('method') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('method')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="depr-main-col grid gap-1.5">
                    <label for="year_input" class="kt-form-label text-xs">Year <span class="text-destructive">*</span></label>
                    <input id="year_input" type="number" name="year" class="kt-input" value="{{ old('year', now()->year) }}" min="1900" max="{{ now()->year + 10 }}">
                    @error('year')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="depr-main-col grid gap-1.5">
                    <label for="depreciation_amount" class="kt-form-label text-xs">Depreciation amount <span class="text-destructive">*</span></label>
                    <input id="depreciation_amount" type="number" step="0.01" name="depreciation_amount" class="kt-input" value="{{ old('depreciation_amount') }}">
                    @error('depreciation_amount')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="depr-main-row">
                <div class="depr-main-col grid gap-1.5">
                    <label for="book_value" class="kt-form-label text-xs">Book value after depreciation <span class="text-destructive">*</span></label>
                    <input id="book_value" type="number" step="0.01" name="book_value" class="kt-input" value="{{ old('book_value') }}">
                    @error('book_value')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">
                    <i class="ki-filled ki-plus me-1"></i>
                    Save depreciation
                </button>
            </div>
        </form>
    </div>

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($depreciations->isEmpty())
                <div class="py-10 text-center text-muted-foreground">
                    <i class="ki-filled ki-graph-down text-4xl mb-2"></i>
                    <p class="text-sm">No depreciation records yet.</p>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[170px]">Property</th>
                                <th class="min-w-[120px]">Year</th>
                                <th class="min-w-[130px]">Method</th>
                                <th class="min-w-[140px]">Depreciation</th>
                                <th class="min-w-[140px]">Book value</th>
                                <th class="min-w-[120px]">Recorded on</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($depreciations as $dep)
                                <tr>
                                    <td class="text-sm">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-foreground">{{ $dep->property->name ?? '—' }}</span>
                                            <span class="text-xs text-muted-foreground">{{ $dep->property->property_code ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $dep->year }}
                                    </td>
                                    <td class="text-sm">
                                        @php
                                            $labels = ['straight_line' => 'Straight line', 'declining_balance' => 'Declining balance', 'manual' => 'Manual entry'];
                                        @endphp
                                        {{ $labels[$dep->method] ?? ucfirst(str_replace('_', ' ', $dep->method)) }}
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ number_format($dep->depreciation_amount, 2) }} {{ $dep->property->currency_code ?? $family->currency_code }}
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ number_format($dep->book_value, 2) }} {{ $dep->property->currency_code ?? $family->currency_code }}
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $dep->created_at?->format('Y-m-d') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-border">
                    {{ $depreciations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

