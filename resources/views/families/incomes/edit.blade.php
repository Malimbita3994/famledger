@extends('layouts.metronic')

@section('title', __('Edit income'))
@section('page_title', __('Edit income'))

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <x-fin-back-link href="{{ route('families.incomes.index') }}">
        {{ __('Back to income') }}
    </x-fin-back-link>

    <form action="{{ route('families.incomes.update', $income) }}" method="POST" id="income-form">
        @csrf
        @method('PUT')

        <div class="grid gap-5 lg:gap-7.5 w-full max-w-5xl mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">{{ __('Edit income') }}</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">
                        {{ __('Wallet for this entry:') }}
                        <span class="font-medium text-foreground">{{ $mainWallet->name }} ({{ $mainWallet->currency_code }})</span>.
                    </p>

                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-3">{{ __('Basic information') }}</h3>
                        <div class="famledger-form-row-3 gap-5 mb-5">
                            <div class="famledger-form-field grid gap-1.5">
                                <label class="kt-form-label">{{ __('Wallet') }}</label>
                                <div class="kt-input flex items-center justify-between min-h-[2.5rem]">
                                    <span class="text-sm font-medium text-foreground">{{ $mainWallet->name }}</span>
                                    <span class="text-xs text-muted-foreground uppercase tracking-wide">{{ $mainWallet->currency_code }}</span>
                                </div>
                            </div>

                            <div class="famledger-form-field grid gap-1.5">
                                <label for="amount" class="kt-form-label">{{ __('Amount') }} <span class="text-destructive">*</span></label>
                                <input
                                    type="number"
                                    name="amount"
                                    id="amount"
                                    value="{{ old('amount', $income->amount) }}"
                                    required
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    class="kt-input"
                                    aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}"
                                />
                                @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div class="famledger-form-field grid gap-1.5">
                                <label for="received_date" class="kt-form-label">{{ __('Received date') }} <span class="text-destructive">*</span></label>
                                <input
                                    type="date"
                                    name="received_date"
                                    id="received_date"
                                    value="{{ old('received_date', $income->received_date->format('Y-m-d')) }}"
                                    required
                                    class="kt-input"
                                    aria-invalid="{{ $errors->has('received_date') ? 'true' : 'false' }}"
                                />
                                @error('received_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        @if($categoryParents->isNotEmpty())
                            <div class="famledger-form-row-3 gap-5 mb-5">
                                <div class="famledger-form-field grid gap-1.5">
                                    <label for="income_category_group" class="kt-form-label">{{ __('Category') }} <span class="text-destructive">*</span></label>
                                    <select name="income_category_group" id="income_category_group" class="kt-select w-full" required>
                                        <option value="">{{ __('Select category') }}</option>
                                        @foreach ($categoryParents as $parent)
                                            <option value="{{ $parent->name }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="famledger-form-field grid gap-1.5 md:col-span-2">
                                    <label for="category_id" class="kt-form-label">{{ __('Subcategory') }} <span class="text-destructive">*</span></label>
                                    <select name="category_id" id="category_id" class="kt-select w-full" aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" required>
                                        <option value="">{{ __('Select subcategory') }}</option>
                                    </select>
                                    @error('category_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        @else
                            <div class="famledger-form-row-3 gap-5 mb-5">
                                <div class="famledger-form-field grid gap-1.5 md:col-span-3">
                                    <label for="category_id_flat" class="kt-form-label">{{ __('Category') }} <span class="text-destructive">*</span></label>
                                    <select name="category_id" id="category_id_flat" class="kt-select w-full" required>
                                        <option value="">{{ __('Select category') }}</option>
                                        @foreach ($flatCategories as $cat)
                                            <option value="{{ $cat->id }}" @selected((string) old('category_id', $income->category_id) === (string) $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground -mt-2 mb-2">
                                {{ __('Run') }} <code class="text-xs bg-muted px-1 rounded">php artisan db:seed --class=IncomeCategorySeeder</code> {{ __('to load the full category hierarchy.') }}
                            </p>
                        @endif
                    </div>

                    <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $income->currency_code) }}" />

                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-3">{{ __('Classification & source') }}</h3>
                        <div class="famledger-form-row-3 gap-5 mb-5">
                            <div class="famledger-form-field grid gap-1.5">
                                <span class="kt-form-label">{{ __('Recurring income?') }}</span>
                                <div class="flex flex-col gap-2 pt-0.5">
                                    <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="is_recurring" id="is_recurring" value="1" class="rounded border-input" {{ old('is_recurring', $income->is_recurring) ? 'checked' : '' }} />
                                        <span>{{ __('Yes, this repeats') }}</span>
                                    </label>
                                </div>
                                @error('is_recurring')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="famledger-form-field grid gap-1.5" id="js_recurring_frequency_wrap" style="display: none;">
                                <label for="recurring_frequency" class="kt-form-label">{{ __('Frequency') }} <span class="text-destructive">*</span></label>
                                <select name="recurring_frequency" id="recurring_frequency" class="kt-select w-full">
                                    <option value="">{{ __('Select frequency') }}</option>
                                    @foreach ($recurringFrequencies as $value => $label)
                                        <option value="{{ $value }}" @selected(old('recurring_frequency', $income->recurring_frequency) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-muted-foreground">{{ __('One-time entries leave recurring off.') }}</p>
                                @error('recurring_frequency')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="famledger-form-field grid gap-1.5">
                                <span class="kt-form-label">{{ __('Taxable?') }}</span>
                                <input type="hidden" name="is_taxable" value="0" />
                                <label class="inline-flex items-center gap-2 text-sm cursor-pointer pt-0.5">
                                    <input type="checkbox" name="is_taxable" id="is_taxable" value="1" class="rounded border-input" @checked(old('is_taxable', $income->is_taxable ? '1' : '0') === '1') />
                                    <span>{{ __('Counts as taxable income') }}</span>
                                </label>
                                @error('is_taxable')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="famledger-form-row-3 gap-5 mb-5">
                            <div class="famledger-form-field grid gap-1.5">
                                <label for="source_entity_type" class="kt-form-label">{{ __('Source type') }}</label>
                                <select name="source_entity_type" id="source_entity_type" class="kt-select w-full">
                                    <option value="">{{ __('— Not specified —') }}</option>
                                    @foreach ($sourceEntityTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('source_entity_type', $income->source_entity_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('source_entity_type')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="famledger-form-field grid gap-1.5 md:col-span-2">
                                <label for="source" class="kt-form-label">{{ __('Source name / entity') }}</label>
                                <input type="text" name="source" id="source" value="{{ old('source', $income->source) }}" placeholder="{{ __('e.g. Acme Corp, Jane Doe, Unit 4B tenant') }}" class="kt-input" aria-invalid="{{ $errors->has('source') ? 'true' : 'false' }}" />
                                @error('source')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    @if($projects->isNotEmpty() || $properties->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-3">{{ __('Linked asset or project') }}</h3>
                        <p class="text-xs text-muted-foreground mb-3">{{ __('Optional. Tie this income to a property (e.g. rental) or a project (e.g. construction milestone).') }}</p>
                        <div class="famledger-form-row-3 gap-5 mb-5">
                            @if($projects->isNotEmpty())
                            <div class="famledger-form-field grid gap-1.5">
                                <label for="linked_project_id" class="kt-form-label">{{ __('Project') }}</label>
                                <select name="linked_project_id" id="linked_project_id" class="kt-select w-full">
                                    <option value="">{{ __('— None —') }}</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" @selected((string) old('linked_project_id', $income->linked_project_id) === (string) $proj->id)>{{ $proj->name }}</option>
                                    @endforeach
                                </select>
                                @error('linked_project_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                            @endif
                            @if($properties->isNotEmpty())
                            <div class="famledger-form-field grid gap-1.5 {{ $projects->isEmpty() ? 'md:col-span-3' : 'md:col-span-2' }}">
                                <label for="linked_property_id" class="kt-form-label">{{ __('Property / asset') }}</label>
                                <select name="linked_property_id" id="linked_property_id" class="kt-select w-full">
                                    <option value="">{{ __('— None —') }}</option>
                                    @foreach($properties as $prop)
                                        <option value="{{ $prop->id }}" @selected((string) old('linked_property_id', $income->linked_property_id) === (string) $prop->id)>{{ $prop->name }}</option>
                                    @endforeach
                                </select>
                                @error('linked_property_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @php
                        $liabilities = $family->liabilities()->orderBy('name')->get();
                    @endphp
                    @if($liabilities->isNotEmpty())
                    <div class="grid gap-1.5">
                        <label for="family_liability_id" class="kt-form-label">{{ __('Linked liability') }}</label>
                        <select name="family_liability_id" id="family_liability_id" class="kt-select w-full max-w-xl">
                            <option value="">— {{ __('None') }} —</option>
                            @foreach($liabilities as $liab)
                                <option value="{{ $liab->id }}" {{ (string) old('family_liability_id', $income->family_liability_id) === (string) $liab->id ? 'selected' : '' }}>
                                    {{ $liab->name }} ({{ ucfirst($liab->type) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-muted-foreground">{{ __('Use when this income represents a loan drawdown or additional borrowing.') }}</p>
                        @error('family_liability_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif

                    <div class="grid gap-1.5">
                        <label for="notes" class="kt-form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="{{ __('Optional notes') }}" class="kt-textarea resize-y max-w-3xl" aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}">{{ old('notes', $income->notes) }}</textarea>
                        @error('notes')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.incomes.index') }}" class="kt-btn kt-btn-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            {{ __('Save changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var recurringCb = document.getElementById('is_recurring');
    var freqWrap = document.getElementById('js_recurring_frequency_wrap');
    var freqSelect = document.getElementById('recurring_frequency');

    function syncRecurring() {
        if (!freqWrap || !recurringCb) return;
        var on = recurringCb.checked;
        freqWrap.style.display = on ? '' : 'none';
        if (freqSelect) {
            freqSelect.required = on;
            if (!on) freqSelect.value = '';
        }
    }
    if (recurringCb) {
        recurringCb.addEventListener('change', syncRecurring);
        syncRecurring();
    }

    @if($categoryParents->isNotEmpty())
    var groupSelect = document.getElementById('income_category_group');
    var incomeSubSelect = document.getElementById('category_id');
    var incomeGroupMap = @json($incomeGroupToSubIds);
    var incomeIdToGroupSub = @json($incomeCategoryIdToGroupSub);
    var oldIncomeCategoryId = @json((string) old('category_id', $income->category_id));

    function populateIncomeSubcategories(group) {
        if (!incomeSubSelect) return;
        incomeSubSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = @json(__('Select subcategory'));
        incomeSubSelect.appendChild(placeholder);

        var subs = incomeGroupMap[group] || {};
        Object.keys(subs).sort().forEach(function (subLabel) {
            var opt = document.createElement('option');
            opt.value = subs[subLabel];
            opt.textContent = subLabel;
            incomeSubSelect.appendChild(opt);
        });
    }

    if (groupSelect) {
        groupSelect.addEventListener('change', function () {
            populateIncomeSubcategories(this.value);
        });
    }

    if (oldIncomeCategoryId && incomeIdToGroupSub[oldIncomeCategoryId]) {
        var info = incomeIdToGroupSub[oldIncomeCategoryId];
        if (groupSelect) {
            groupSelect.value = info.group;
            populateIncomeSubcategories(info.group);
        }
        if (incomeSubSelect) {
            incomeSubSelect.value = oldIncomeCategoryId;
        }
    }
    @endif
})();
</script>
@endpush
@endsection
