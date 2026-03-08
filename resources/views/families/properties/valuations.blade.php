@extends('layouts.metronic')

@section('title', 'Valuation History')
@section('page_title', 'Valuation History')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.properties.assets', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to assets list
    </a>

    <style>
    .valuations-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .valuations-main-row .valuations-main-col {
        width: 100%;
    }

    .valuations-filter-row {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    .valuations-filter-row .valuations-filter-col {
        width: 100%;
    }

    @media (min-width: 900px) {
        .valuations-main-row {
            flex-direction: row;
        }

        .valuations-main-row .valuations-main-col {
            flex: 1 1 0;
        }

        .valuations-filter-row {
            flex-direction: row;
            align-items: flex-end;
        }

        .valuations-filter-row .valuations-filter-col {
            flex: 1 1 0;
        }
    }
    </style>

    <div class="kt-card p-5 lg:p-7.5 mb-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-mono">Valuation history</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Derived from depreciation (book value by year). No manual entry — values come from the depreciation records.
                </p>
            </div>
            <form method="GET" class="valuations-filter-row">
                <div class="valuations-filter-col">
                    <select name="property_id" class="kt-select w-full">
                        <option value="">All properties</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected(($filters['property_id'] ?? null) == $prop->id)>
                                {{ $prop->property_code }} · {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="valuations-filter-col">
                    <div class="flex items-center gap-2">
                        <div class="flex flex-col flex-1">
                            <label class="kt-form-label text-xs" for="from">From</label>
                            <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="kt-input h-9">
                        </div>
                        <div class="flex flex-col flex-1">
                            <label class="kt-form-label text-xs" for="to">To</label>
                            <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="kt-input h-9">
                        </div>
                    </div>
                </div>
                <div class="valuations-filter-col flex justify-end">
                    <button type="submit" class="kt-btn kt-btn-outline kt-btn-sm mt-2 lg:mt-0">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($valuations->isEmpty())
                <div class="py-10 text-center text-muted-foreground">
                    <i class="ki-filled ki-graph-up text-4xl mb-2"></i>
                    <p class="text-sm">No valuation history yet. Values are derived from depreciation — add depreciation records to see them here.</p>
                    <a href="{{ route('families.properties.depreciation', $family) }}" class="kt-btn kt-btn-outline mt-4">Depreciation</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[170px]">Property</th>
                                <th class="min-w-[120px]">Valuation date</th>
                                <th class="min-w-[140px]">Estimated value</th>
                                <th class="min-w-[160px]">Valuator</th>
                                <th class="min-w-[120px]">Recorded on</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($valuations as $val)
                                <tr>
                                    <td class="text-sm">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-foreground">{{ $val->property->name ?? '—' }}</span>
                                            <span class="text-xs text-muted-foreground">{{ $val->property->property_code ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $val->year ?? '' }}-12-31
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ number_format($val->book_value ?? 0, 2) }} {{ $val->property->currency_code ?? $family->currency_code }}
                                    </td>
                                    <td class="text-sm text-muted-foreground">
                                        Depreciation (auto)
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $val->created_at?->format('Y-m-d') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-border">
                    {{ $valuations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

