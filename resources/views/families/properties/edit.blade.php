@extends('layouts.metronic')

@section('title', 'Edit Property')
@section('page_title', 'Edit Property')

@section('content')
<style>
.property-main-row {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.property-main-row .property-main-col {
    width: 100%;
}

@media (min-width: 900px) {
    .property-main-row {
        flex-direction: row;
    }

    .property-main-row .property-main-col {
        flex: 1 1 0;
    }
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.properties.assets', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to assets list
    </a>

    <form action="{{ route('families.properties.update', [$family, $property]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="kt-card p-5 lg:p-7.5">
            <div class="mb-5">
                <h1 class="text-lg font-semibold text-mono">Edit property</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Update core attributes and category-specific fields for this property.
                </p>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                {{-- Basic info row (4 cols on desktop) --}}
                <div class="property-main-row">
                    <div class="property-main-col grid gap-1.5">
                        <label for="category_id" class="kt-form-label">Category</label>
                        <select
                            id="category_id"
                            name="category_id"
                            class="kt-select"
                            disabled
                        >
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="name" class="kt-form-label">Property name <span class="text-destructive">*</span></label>
                        <input id="name" type="text" name="name" class="kt-input" value="{{ old('name', $property->name) }}" required>
                        @error('name') <p class="kt-form-message">{{ $message }}</p> @enderror
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="ownership_type" class="kt-form-label">Ownership type</label>
                        <select id="ownership_type" name="ownership_type" class="kt-select">
                            <option value="">Select</option>
                            @foreach (['individual' => 'Individual', 'joint' => 'Joint', 'family_trust' => 'Family Trust'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('ownership_type', $property->ownership_type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="owner_family_member_id" class="kt-form-label">Owner (family member)</label>
                        <select id="owner_family_member_id" name="owner_family_member_id" class="kt-select">
                            <option value="">Select</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected(old('owner_family_member_id', $property->owner_family_member_id) == $member->id)>
                                    {{ $member->member_name ?? $member->user->name ?? 'Member #'.$member->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Financial & status --}}
                <div class="property-main-row">
                    <div class="property-main-col grid gap-1.5">
                        <label for="acquisition_date" class="kt-form-label">Acquisition date</label>
                        <input id="acquisition_date" type="date" name="acquisition_date" class="kt-input" value="{{ old('acquisition_date', optional($property->acquisition_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="acquisition_method" class="kt-form-label">Acquisition method</label>
                        <select id="acquisition_method" name="acquisition_method" class="kt-select">
                            <option value="">Select</option>
                            @foreach (['purchase' => 'Purchase', 'inheritance' => 'Inheritance', 'gift' => 'Gift', 'exchange' => 'Exchange'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('acquisition_method', $property->acquisition_method) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="purchase_price" class="kt-form-label">Purchase price</label>
                        <input id="purchase_price" type="number" step="0.01" name="purchase_price" class="kt-input" value="{{ old('purchase_price', $property->purchase_price) }}">
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="current_estimated_value" class="kt-form-label">Current estimated value</label>
                        <input id="current_estimated_value" type="number" step="0.01" name="current_estimated_value" class="kt-input" value="{{ old('current_estimated_value', $property->current_estimated_value) }}">
                    </div>
                </div>

                {{-- Location --}}
                <div class="property-main-row">
                    <div class="property-main-col grid gap-1.5">
                        <label for="country" class="kt-form-label">Country</label>
                        <input id="country" type="text" name="country" class="kt-input" value="{{ old('country', $property->country ?? $family->country) }}">
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="region_city" class="kt-form-label">Region / City</label>
                        <input id="region_city" type="text" name="region_city" class="kt-input" value="{{ old('region_city', $property->region_city) }}">
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="address" class="kt-form-label">Physical address</label>
                        <input id="address" type="text" name="address" class="kt-input" value="{{ old('address', $property->address) }}">
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label class="kt-form-label">GPS coordinates</label>
                        <div class="flex gap-2">
                            <input type="number" step="0.0000001" name="gps_lat" class="kt-input" placeholder="Lat" value="{{ old('gps_lat', $property->gps_lat) }}">
                            <input type="number" step="0.0000001" name="gps_lng" class="kt-input" placeholder="Lng" value="{{ old('gps_lng', $property->gps_lng) }}">
                        </div>
                    </div>
                </div>

                {{-- Documentation --}}
                <div class="property-main-row">
                    <div class="property-main-col grid gap-1.5">
                        <label for="title_number" class="kt-form-label">Title number / registration</label>
                        <input id="title_number" type="text" name="title_number" class="kt-input" value="{{ old('title_number', $property->title_number) }}">
                    </div>
                    <div class="property-main-col grid gap-1.5">
                        <label for="notes" class="kt-form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="kt-textarea resize-y">{{ old('notes', $property->notes) }}</textarea>
                    </div>
                </div>

                {{-- Dynamic attributes --}}
                @php
                    $existingValues = $property->attributeValues->keyBy('attribute_id');
                @endphp
                @if ($attributes->isNotEmpty())
                    <div class="border-t border-border pt-4 mt-2">
                        <h2 class="text-sm font-semibold text-foreground mb-3">Category-specific attributes</h2>
                        @foreach ($attributes->chunk(4) as $chunk)
                            <div class="property-main-row mb-3">
                                @foreach ($chunk as $attr)
                                    @php
                                        $fieldName = 'attr_'.$attr->id;
                                        $current = old($fieldName, optional($existingValues->get($attr->id))->value);
                                    @endphp
                                    <div class="property-main-col grid gap-1.5">
                                        <label class="kt-form-label text-xs">
                                            {{ $attr->name }}
                                            @if ($attr->is_required)
                                                <span class="text-destructive">*</span>
                                            @endif
                                        </label>
                                        @switch($attr->data_type)
                                            @case('number')
                                                <input type="number" name="{{ $fieldName }}" class="kt-input" value="{{ $current }}">
                                                @break
                                            @case('date')
                                                <input type="date" name="{{ $fieldName }}" class="kt-input" value="{{ $current }}">
                                                @break
                                            @case('dropdown')
                                                <select name="{{ $fieldName }}" class="kt-select">
                                                    <option value="">Select</option>
                                                    @foreach ($attr->options as $opt)
                                                        <option value="{{ $opt->value }}" @selected($current == $opt->value)>{{ $opt->label ?? $opt->value }}</option>
                                                    @endforeach
                                                </select>
                                                @break
                                            @case('boolean')
                                                <select name="{{ $fieldName }}" class="kt-select">
                                                    <option value="">Select</option>
                                                    <option value="1" @selected($current === '1' || $current === 1)>Yes</option>
                                                    <option value="0" @selected($current === '0' || $current === 0)>No</option>
                                                </select>
                                                @break
                                            @default
                                                <input type="text" name="{{ $fieldName }}" class="kt-input" value="{{ $current }}">
                                        @endswitch
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('families.properties.show', [$family, $property]) }}" class="kt-btn kt-btn-outline">Cancel</a>
                <button type="submit" class="kt-btn kt-btn-primary">Update property</button>
            </div>
        </div>
    </form>
</div>
@endsection

