@props(['family' => null])

@php
    $inputClass = 'w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 shadow-sm transition duration-150 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none disabled:opacity-50';
@endphp

<div class="space-y-10">
    {{-- Basic details --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 shadow-sm overflow-hidden">
        <div class="px-8 sm:px-10 py-6 border-b border-gray-100 dark:border-gray-700/50">
            <h4 class="text-base font-semibold text-gray-900 dark:text-white">Basic details</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Name and description for your household.</p>
        </div>
        <div class="p-8 sm:p-10 grid grid-cols-1 md:grid-cols-2 gap-7">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Family name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $family?->name) }}" required
                       class="{{ $inputClass }} min-h-[44px]"
                       placeholder="e.g. Smith Family">
                @error('name')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          class="{{ $inputClass }} min-h-[96px] resize-y"
                          placeholder="Optional short description">{{ old('description', $family?->description) }}</textarea>
                @error('description')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Region & currency --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 shadow-sm overflow-hidden">
        <div class="px-8 sm:px-10 py-6 border-b border-gray-100 dark:border-gray-700/50">
            <h4 class="text-base font-semibold text-gray-900 dark:text-white">Region & currency</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Defaults for this family’s transactions.</p>
        </div>
        <div class="p-8 sm:p-10 grid grid-cols-1 md:grid-cols-2 gap-7">
            <div>
                <label for="currency_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Currency <span class="text-red-500">*</span>
                </label>
                <input type="text" name="currency_code" id="currency_code" value="{{ old('currency_code', $family?->currency_code ?? 'USD') }}" required maxlength="3"
                       class="{{ $inputClass }} min-h-[44px] uppercase tracking-wider"
                       placeholder="USD">
                @error('currency_code')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Timezone <span class="text-red-500">*</span>
                </label>
                <input type="text" name="timezone" id="timezone" value="{{ old('timezone', $family?->timezone ?? 'UTC') }}" required
                       class="{{ $inputClass }} min-h-[44px]"
                       placeholder="e.g. America/New_York">
                @error('timezone')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Country
                </label>
                <input type="text" name="country" id="country" value="{{ old('country', $family?->country) }}"
                       class="{{ $inputClass }} min-h-[44px]"
                       placeholder="e.g. United States">
                @error('country')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            @if ($family)
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status" id="status" class="{{ $inputClass }} min-h-[44px]">
                    <option value="active" {{ old('status', $family->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ old('status', $family->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            @endif
        </div>
    </div>
</div>
