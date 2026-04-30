@extends('layouts.metronic')

@section('title', 'Property Maintenance')
@section('page_title', 'Property Maintenance')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.properties.assets') }}">
        Back to assets list
    </x-fin-back-link>

    <style>
    .maintenance-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .maintenance-main-row .maintenance-main-col {
        width: 100%;
    }

    @media (min-width: 900px) {
        .maintenance-main-row {
            flex-direction: row;
        }

        .maintenance-main-row .maintenance-main-col {
            flex: 1 1 0;
        }
    }

    .maintenance-filter-row {
        display: grid !important;
        grid-template-columns: minmax(220px, 2fr) repeat(2, minmax(160px, 1fr)) auto !important;
        gap: 0.75rem !important;
        align-items: flex-end !important;
        width: 100% !important;
    }

    @media (max-width: 768px) {
        .maintenance-filter-row {
            grid-template-columns: 1fr !important;
        }
    }
    </style>

    <div class="kt-card p-5 lg:p-7.5 mb-5">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-lg font-semibold text-mono">{{ __('Maintenance') }}</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    {{ __('Log repairs, inspections, and service for buildings and vehicles. Irrelevant asset types are filtered out automatically.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                <form method="GET" class="maintenance-filter-row">
                    <div class="flex flex-col">
                        <label class="kt-form-label text-xs mb-1" for="filter_property_id">{{ __('Property') }}</label>
                        <select name="property_id" id="filter_property_id" class="kt-select min-w-[180px]" @disabled($properties->isEmpty())>
                            <option value="">{{ __('All eligible properties') }}</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}" @selected(($filters['property_id'] ?? null) == $prop->id)>
                                    {{ $prop->property_code }} · {{ $prop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="kt-form-label text-xs mb-1" for="from">{{ __('From') }}</label>
                        <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="kt-input h-9">
                    </div>
                    <div class="flex flex-col">
                        <label class="kt-form-label text-xs mb-1" for="to">{{ __('To') }}</label>
                        <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="kt-input h-9">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="kt-btn kt-btn-outline kt-btn-sm">
                            {{ __('Filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($properties->isEmpty())
            <div class="rounded-lg border border-dashed border-border bg-muted/20 px-4 py-6 text-center text-sm text-muted-foreground">
                <p class="font-medium text-foreground">{{ __('No eligible assets for this maintenance log') }}</p>
                <p class="mt-2 max-w-lg mx-auto">
                    @if ($hasPropertiesButNoneEligible ?? false)
                        {{ __('Everything in your property list is land, farm, agricultural, or investment-type—those are intentionally not listed here.') }}
                    @else
                        {{ __('Add a building or vehicle under Property assets first.') }}
                    @endif
                </p>
                <a href="{{ route('families.properties.assets') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-4">{{ __('Open property assets') }}</a>
            </div>
        @else
        <form method="POST" action="{{ route('families.properties.maintenance.store') }}" class="grid gap-4 lg:gap-5">
            @csrf
            <div class="maintenance-main-row">
                <div class="maintenance-main-col grid gap-1.5">
                    <label for="maintenance_property_id" class="kt-form-label text-xs">{{ __('Property') }} <span class="text-destructive">*</span></label>
                    <select id="maintenance_property_id" name="property_id" class="kt-select">
                        <option value="">{{ __('Select property') }}</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected(old('property_id') == $prop->id)>
                                {{ $prop->property_code }} · {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('property_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="maintenance-main-col grid gap-1.5">
                    <label for="service_date" class="kt-form-label text-xs">{{ __('Service date') }} <span class="text-destructive">*</span></label>
                    <input id="service_date" type="date" name="service_date" class="kt-input" value="{{ old('service_date') }}">
                    @error('service_date')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="maintenance-main-col grid gap-1.5">
                    <label for="cost" class="kt-form-label text-xs">{{ __('Cost') }}</label>
                    <input id="cost" type="number" step="0.01" name="cost" class="kt-input" value="{{ old('cost') }}">
                    @error('cost')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="maintenance-main-col grid gap-1.5">
                    <label for="service_provider" class="kt-form-label text-xs">{{ __('Service provider') }}</label>
                    <input id="service_provider" type="text" name="service_provider" class="kt-input" value="{{ old('service_provider') }}">
                    @error('service_provider')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="maintenance-main-row">
                <div class="maintenance-main-col grid gap-1.5">
                    <label for="description" class="kt-form-label text-xs">{{ __('Description') }}</label>
                    <textarea id="description" name="description" rows="2" class="kt-textarea resize-y">{{ old('description') }}</textarea>
                    @error('description')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="maintenance-main-col grid gap-1.5">
                    <label for="next_due_date" class="kt-form-label text-xs">{{ __('Next due date') }}</label>
                    <input id="next_due_date" type="date" name="next_due_date" class="kt-input" value="{{ old('next_due_date') }}">
                    @error('next_due_date')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">
                    <i class="ki-filled ki-plus me-1"></i>
                    {{ __('Add maintenance') }}
                </button>
            </div>
        </form>
        @endif
    </div>

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($maintenances->isEmpty())
                <div class="py-10 text-center text-muted-foreground px-4">
                    <i class="ki-filled ki-setting-3 text-4xl mb-2 opacity-80"></i>
                    <p class="text-sm font-medium text-foreground">{{ __('No maintenance records yet.') }}</p>
                    @if ($properties->isNotEmpty())
                        <p class="text-xs mt-1 max-w-md mx-auto">{{ __('Add a row above when you complete a repair or inspection.') }}</p>
                    @endif
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[160px]">Property</th>
                                <th class="min-w-[110px]">Service date</th>
                                <th class="min-w-[110px]">Cost</th>
                                <th class="min-w-[160px]">Service provider</th>
                                <th class="min-w-[240px]">Description</th>
                                <th class="min-w-[110px]">Next due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($maintenances as $m)
                                <tr>
                                    <td class="text-sm">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-foreground">{{ $m->property->name ?? '—' }}</span>
                                            <span class="text-xs text-muted-foreground">{{ $m->property->property_code ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $m->service_date?->format('Y-m-d') }}
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        @if (!is_null($m->cost))
                                            {{ number_format($m->cost, 2) }} {{ $m->property->currency_code ?? $family->currency_code }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-sm">
                                        {{ $m->service_provider ?? '—' }}
                                    </td>
                                    <td class="text-sm">
                                        {{ \Illuminate\Support\Str::limit($m->description, 80) ?? '—' }}
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $m->next_due_date?->format('Y-m-d') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-border">
                    {{ $maintenances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

