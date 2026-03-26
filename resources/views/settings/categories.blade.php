@extends('layouts.metronic')

@section('title', __('Categories'))
@section('page_title', __('Categories'))

@push('styles')
<style>
    .cat-pulse-page.admin-pulse-page {
        --ap-accent: #009ef7;
        --ap-accent-2: #0ea5e9;
        --ap-soft: #f0f9ff;
        --ap-ring: rgba(0, 158, 247, 0.28);
    }
    /* Inner section cards (Currencies, roles, categories, custom lookups, quick-add panel) */
    .cat-pulse-page .cat-pulse-lookup-card {
        border-radius: 16px !important;
        border: 1px solid rgba(14, 165, 233, 0.2) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .cat-pulse-page .cat-pulse-lookup-card:hover {
        border-color: rgba(0, 158, 247, 0.35) !important;
        box-shadow: 0 8px 24px rgba(0, 158, 247, 0.1);
    }
    .dark .cat-pulse-page .cat-pulse-lookup-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.55) 0%, rgb(15 23 42 / 0.72) 100%) !important;
        border-color: rgba(14, 165, 233, 0.22) !important;
    }
    .cat-pulse-page .cat-pulse-lookup-card .kt-card-header {
        border-bottom: 1px solid rgba(14, 165, 233, 0.14);
        background: linear-gradient(180deg, rgba(240, 249, 255, 0.65) 0%, rgba(248, 252, 255, 0.2) 100%);
    }
    .dark .cat-pulse-page .cat-pulse-lookup-card .kt-card-header {
        border-bottom-color: rgba(14, 165, 233, 0.18);
        background: linear-gradient(180deg, rgba(14, 165, 233, 0.1) 0%, transparent 100%);
    }
    .cat-pulse-page .cat-pulse-lookup-card .kt-card-title {
        color: var(--ap-accent);
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    /* Text inputs (incl. datalist) — use background-color to avoid clearing Metronic background-image on selects if added later */
    .cat-pulse-page .kt-input {
        border-radius: 12px !important;
        background-color: var(--ap-soft) !important;
        border: 1px solid transparent !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    }
    .cat-pulse-page .kt-input:hover:not([readonly]):not(:disabled) {
        background-color: #e0f2fe !important;
    }
    .cat-pulse-page .kt-input:focus {
        outline: none;
        border-color: var(--ap-accent) !important;
        box-shadow: 0 0 0 3px var(--ap-ring) !important;
        background-color: #fff !important;
    }
    .cat-pulse-page .kt-input[readonly],
    .cat-pulse-page .kt-input:disabled {
        background-color: rgba(241, 245, 249, 0.75) !important;
        color: var(--foreground, #334155);
        opacity: 1;
    }
    .dark .cat-pulse-page .kt-input[readonly],
    .dark .cat-pulse-page .kt-input:disabled {
        background-color: rgba(30, 41, 59, 0.55) !important;
        color: var(--foreground, #e2e8f0);
    }
    /* Solid primary actions inside cards (Add / Save where Metronic uses kt-btn-primary without ghost) */
    .cat-pulse-page .kt-btn.kt-btn-primary:not(.kt-btn-ghost) {
        background: linear-gradient(135deg, var(--ap-accent) 0%, var(--ap-accent-2) 100%) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(0, 158, 247, 0.32);
    }
    .cat-pulse-page .kt-btn.kt-btn-primary:not(.kt-btn-ghost):hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 18px rgba(0, 158, 247, 0.4);
    }
    /* Outline row actions: accent on hover */
    .cat-pulse-page .kt-btn.kt-btn-outline {
        border-radius: 10px;
        transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .cat-pulse-page .kt-btn.kt-btn-outline:hover {
        border-color: var(--ap-accent);
        background-color: rgba(0, 158, 247, 0.06);
    }
    .admin-pulse-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .admin-pulse-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--ap-accent);
    }
    .cat-pulse-page .cat-pulse-intro {
        font-size: 0.875rem;
        line-height: 1.55;
        color: #64748b;
        margin-top: 0.35rem;
        max-width: 42rem;
    }
    .dark .cat-pulse-page .cat-pulse-intro {
        color: #94a3b8;
    }
    .admin-pulse-btn-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.15rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        background: rgba(255, 255, 255, 0.95);
        color: #334155 !important;
        text-decoration: none !important;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .admin-pulse-btn-outline:hover {
        border-color: var(--ap-accent);
        background: rgba(0, 158, 247, 0.06);
        box-shadow: 0 0 0 1px rgba(0, 158, 247, 0.12);
    }
    .dark .admin-pulse-btn-outline {
        background: rgba(30, 41, 59, 0.9);
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.35);
    }
    .admin-pulse-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.25rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        color: #fff !important;
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, var(--ap-accent) 0%, var(--ap-accent-2) 100%);
        box-shadow: 0 4px 14px rgba(0, 158, 247, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .admin-pulse-btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 20px rgba(0, 158, 247, 0.42);
        transform: translateY(-1px);
    }
    .cat-pulse-page .cat-pulse-shell.admin-pulse-frame {
        max-width: none;
        margin-left: 0;
        margin-right: 0;
    }
    .admin-pulse-frame {
        padding: 3px;
        border-radius: 24px;
        background: linear-gradient(
            135deg,
            rgba(0, 158, 247, 0.42) 0%,
            rgba(255, 255, 255, 0.96) 46%,
            rgba(14, 165, 233, 0.3) 100%
        );
        box-shadow:
            0 4px 24px rgba(0, 158, 247, 0.12),
            0 24px 48px rgba(15, 23, 42, 0.08);
        width: 100%;
    }
    .admin-pulse-card-inner {
        background: #fff;
        border-radius: 21px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }
    .dark .admin-pulse-card-inner {
        background: rgb(15 23 42 / 0.96);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .lookup-inline-row {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        align-items: stretch;
    }
    @media (min-width: 900px) {
        .lookup-inline-row {
            flex-direction: row;
            align-items: center;
        }
        .lookup-inline-row .lookup-type {
            flex: 0 0 200px;
        }
        .lookup-inline-row .lookup-name {
            flex: 1 1 160px;
        }
        .lookup-inline-row .lookup-desc {
            flex: 2 1 240px;
        }
        .lookup-inline-row .lookup-actions {
            flex: 0 0 auto;
        }
    }
    .lookup-card {
        max-height: 420px;
        display: flex;
        flex-direction: column;
    }
    .lookup-card .lookup-card-body {
        flex: 1 1 auto;
        overflow-y: auto;
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-pulse-btn-primary:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-pulse cat-pulse-page admin-pulse-page">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="admin-pulse-eyebrow mb-1.5">{{ __('Settings') }}</p>
                <h1 class="admin-pulse-title">{{ __('Categories') }}</h1>
                <p class="cat-pulse-intro">
                    {{ __('Define income and expense categories to keep reports, budgets and savings goals organised.') }}
                </p>
            </div>
            <div class="shrink-0 ms-auto flex flex-wrap items-center gap-2 justify-end">
                <button type="button" class="admin-pulse-btn-primary" id="open_add_lookup">
                    <i class="ki-filled ki-plus text-base"></i>
                    {{ __('Add lookup') }}
                </button>
                <a href="{{ route('settings.index') }}" class="admin-pulse-btn-outline inline-flex">
                    <i class="ki-filled ki-left text-base"></i>
                    {{ __('Back to settings') }}
                </a>
            </div>
        </div>
    </div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
 @if (session('success'))
  <div class="mb-4 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-sm text-green-800 dark:text-green-200">
   {{ session('success') }}
  </div>
 @endif
 @if (session('error'))
  <div class="mb-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-200">
   {{ session('error') }}
  </div>
 @endif

  <div class="admin-pulse-frame cat-pulse-shell min-w-0 max-w-full">
   <div class="admin-pulse-card-inner min-w-0 overflow-hidden p-4 sm:p-5 lg:p-6 flex flex-col gap-5">

 {{-- Quick add lookup panel (hidden by default) --}}
 <div id="add_lookup_panel" class="kt-card cat-pulse-lookup-card mb-0 hidden">
  <div class="kt-card-content settings-lookup-panel-inner flex flex-wrap items-center gap-3">
   <form method="POST" action="{{ route('settings.categories.lookup.store') }}" class="lookup-inline-row w-full">
    @csrf
    <div class="lookup-type flex items-center gap-2">
     <label for="lookup_type" class="text-xs text-secondary-foreground font-medium me-1">
      {{ __('Lookup type') }}
     </label>
     <input
      id="lookup_type"
      name="type"
      list="lookup_type_options"
      placeholder="{{ __('e.g. Income category, Expense category, Family role') }}"
      class="kt-input text-xs min-w-40"
     />
     <datalist id="lookup_type_options">
      <option value="Income category"></option>
      <option value="Expense category"></option>
      <option value="Family role"></option>
     </datalist>
    </div>
    <input
     type="text"
     name="name"
     placeholder="{{ __('Name') }}"
     class="lookup-name kt-input w-full text-xs"
     required
    />
    <input
     type="text"
     name="description"
     placeholder="{{ __('Description (optional, used for roles)') }}"
     class="lookup-desc kt-input w-full text-xs"
    />
    <div class="lookup-actions ms-auto flex items-center gap-2">
     <button type="submit" class="admin-pulse-btn-primary !py-2 !px-4 !text-xs">
      {{ __('Save') }}
     </button>
     <button type="button" class="admin-pulse-btn-outline !py-2 !px-3 !text-xs" id="close_add_lookup">
      {{ __('Cancel') }}
     </button>
    </div>
   </form>
  </div>
 </div>

 <div class="settings-grid-2">
  {{-- Currencies lookup --}}
  <div class="kt-card lookup-card cat-pulse-lookup-card">
   <div class="kt-card-header items-center justify-between">
    <h3 class="kt-card-title text-sm">
     {{ __('Currencies') }}
    </h3>
    @if (! empty($defaultCurrency))
     <span class="text-xs text-muted-foreground">
      {{ __('Default: :code', ['code' => $defaultCurrency]) }}
     </span>
    @endif
   </div>
   <div class="kt-card-table kt-scrollable-x-auto lookup-card-body">
    <table class="kt-table align-middle text-xs text-secondary-foreground">
     <thead>
      <tr class="bg-accent/40">
       <th class="text-start font-medium min-w-16 px-4 py-2">
        {{ __('Code') }}
       </th>
       <th class="text-start font-medium min-w-40 px-4 py-2">
        {{ __('Name') }}
       </th>
      </tr>
     </thead>
     <tbody>
      @foreach ($currencies as $code => $label)
       @continue($code === 'default')
       <tr>
        <td class="px-4 py-1.5 text-foreground font-medium">
         {{ $code }}
         @if ($defaultCurrency === $code)
          <span class="kt-badge kt-badge-xs kt-badge-primary kt-badge-outline ms-1">
           {{ __('Default') }}
          </span>
         @endif
        </td>
        <td class="px-4 py-1.5">
         {{ $label }}
        </td>
       </tr>
      @endforeach
     </tbody>
    </table>
   </div>
  </div>

  {{-- Family roles lookup --}}
  <div class="kt-card lookup-card cat-pulse-lookup-card">
   <div class="kt-card-header items-center justify-between">
    <h3 class="kt-card-title text-sm">
     {{ __('Family roles') }}
    </h3>
   </div>
   <div class="kt-card-table kt-scrollable-x-auto lookup-card-body">
    <div class="px-4 pt-3 pb-2 border-b border-border">
     <form method="POST" action="{{ route('settings.categories.roles.store') }}" class="flex flex-wrap items-center gap-2">
      @csrf
      <input
       type="text"
       name="name"
       placeholder="{{ __('New role name') }}"
       class="kt-input w-full sm:w-40"
      />
      <input
       type="text"
       name="description"
       placeholder="{{ __('Description (optional)') }}"
       class="kt-input w-full sm:flex-1"
      />
      <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
       {{ __('Add') }}
      </button>
     </form>
    </div>
    <table class="kt-table align-middle text-xs text-secondary-foreground">
     <thead>
      <tr class="bg-accent/40">
       <th class="text-start font-medium min-w-32 px-4 py-2">
        {{ __('Role') }}
       </th>
       <th class="text-start font-medium min-w-56 px-4 py-2">
        {{ __('Description') }}
       </th>
       <th class="w-28 px-4 py-2 text-end"></th>
      </tr>
     </thead>
     <tbody>
      @foreach ($familyRoles as $role)
       <tr>
        <td class="px-4 py-1.5 text-foreground font-medium align-top">
         <form method="POST" action="{{ route('settings.categories.roles.update', $role) }}" class="flex items-center gap-2">
          @csrf
          @method('PATCH')
          <input
           type="text"
           name="name"
           value="{{ old('name', $role->name) }}"
           @if($role->is_system) readonly @endif
           class="kt-input w-full text-xs"
          />
         </form>
        </td>
        <td class="px-4 py-1.5 align-top">
         <form method="POST" action="{{ route('settings.categories.roles.update', $role) }}">
          @csrf
          @method('PATCH')
          <input
           type="hidden"
           name="name"
           value="{{ $role->name }}"
          />
          <input
           type="text"
           name="description"
           value="{{ old('description', $role->description) }}"
           class="kt-input w-full text-xs mt-1"
          />
          <div class="flex justify-end mt-1">
           <button type="submit" class="kt-btn kt-btn-xs kt-btn-outline">
            {{ __('Save') }}
           </button>
          </div>
         </form>
        </td>
        <td class="px-4 py-1.5 text-end align-top">
         @if (! $role->is_system)
          <form
           method="POST"
           action="{{ route('settings.categories.roles.destroy', $role) }}"
           class="js-confirm-delete inline-block"
           data-confirm-title="{{ __('Delete role?') }}"
           data-confirm-message="{{ __('This role will be removed from the system.') }}"
          >
           @csrf
           @method('DELETE')
           <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
            {{ __('Delete') }}
           </button>
          </form>
         @endif
        </td>
       </tr>
      @endforeach
     </tbody>
    </table>
   </div>
  </div>

  {{-- Income categories lookup --}}
  <div class="kt-card lookup-card cat-pulse-lookup-card">
   <div class="kt-card-header items-center justify-between">
    <h3 class="kt-card-title text-sm">
     {{ __('Income categories') }}
    </h3>
   </div>
   <div class="kt-card-content px-5 py-5 lookup-card-body">
    @php
     $incomeCount = $incomeCategories->count();
     $incomePreview = $incomeCategories->take(5);
    @endphp
    <form method="POST" action="{{ route('settings.categories.income.store') }}" class="flex flex-wrap items-center gap-2 mb-4">
     @csrf
     <input
      type="text"
      name="name"
      placeholder="{{ __('New income category') }}"
      class="kt-input w-full sm:w-72"
     />
     <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
      {{ __('Add') }}
     </button>
    </form>

    @if ($incomeCategories->isEmpty())
     <p class="text-xs text-muted-foreground">
      {{ __('No system income categories found. Run the IncomeCategorySeeder to populate defaults.') }}
     </p>
    @else
     {{-- Preview small list when there are many --}}
     <ul class="list-disc ps-4 text-xs text-secondary-foreground space-y-1 mb-3">
      @foreach ($incomePreview as $cat)
       <li>{{ $cat->name }}</li>
      @endforeach
     </ul>
     @if ($incomeCount > 5)
      <p class="text-[11px] text-muted-foreground mb-2">
       {{ __('…and :count more income categories.', ['count' => $incomeCount - 5]) }}
      </p>
     @endif

     <button
      type="button"
      class="kt-btn kt-btn-xs kt-btn-outline mb-3"
      data-toggle-target="#income_categories_full"
      data-toggle-open-label="{{ __('Hide all') }}"
      data-toggle-closed-label="{{ __('View all (:count)', ['count' => $incomeCount]) }}"
     >
      {{ __('View all (:count)', ['count' => $incomeCount]) }}
     </button>

     <div id="income_categories_full" class="kt-scrollable-x-auto hidden mt-2">
      <table class="kt-table align-middle text-xs text-secondary-foreground">
       <thead>
        <tr class="bg-accent/40">
         <th class="text-start font-medium min-w-56 px-3 py-1.5">
          {{ __('Name') }}
         </th>
         <th class="w-24 px-3 py-1.5"></th>
        </tr>
       </thead>
       <tbody>
        @foreach ($incomeCategories as $cat)
         <tr>
          <td class="px-3 py-1.5">
           <form method="POST" action="{{ route('settings.categories.income.update', $cat) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <input
             type="text"
             name="name"
             value="{{ old('name', $cat->name) }}"
             class="kt-input w-full text-xs"
            />
            <button type="submit" class="kt-btn kt-btn-xs kt-btn-outline">
             {{ __('Save') }}
            </button>
           </form>
          </td>
          <td class="px-3 py-1.5 text-end">
           <form
            method="POST"
            action="{{ route('settings.categories.income.destroy', $cat) }}"
            class="js-confirm-delete inline-block"
            data-confirm-title="{{ __('Delete income category?') }}"
            data-confirm-message="{{ __('Existing records that use this category will keep it, but it will no longer appear as a choice.') }}"
           >
            @csrf
            @method('DELETE')
            <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
             {{ __('Delete') }}
            </button>
           </form>
          </td>
         </tr>
        @endforeach
       </tbody>
      </table>
     </div>
    @endif
   </div>
  </div>

  {{-- Expense categories lookup --}}
  <div class="kt-card lookup-card cat-pulse-lookup-card" id="expense_categories_card">
   <div class="kt-card-header items-center justify-between">
    <h3 class="kt-card-title text-sm">
     {{ __('Expense categories') }}
    </h3>
   </div>
   <div class="kt-card-content px-5 py-5 lookup-card-body">
    @php
     $expenseCount = $expenseCategories->count();
     $expensePreview = $expenseCategories->take(5);
    @endphp
    <form method="POST" action="{{ route('settings.categories.expense.store') }}" class="flex flex-wrap items-center gap-2 mb-4">
     @csrf
     <input
      type="text"
      name="name"
      placeholder="{{ __('New expense category') }}"
      class="kt-input w-full sm:w-72"
     />
     <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
      {{ __('Add') }}
     </button>
    </form>

    @if ($expenseCategories->isEmpty())
     <p class="text-xs text-muted-foreground">
      {{ __('No system expense categories found. Run the ExpenseCategorySeeder to populate defaults.') }}
     </p>
    @else
     {{-- Preview small list when there are many --}}
     <ul class="list-disc ps-4 text-xs text-secondary-foreground space-y-1 mb-3">
      @foreach ($expensePreview as $cat)
       <li>{{ $cat->name }}</li>
      @endforeach
     </ul>
     @if ($expenseCount > 5)
      <p class="text-[11px] text-muted-foreground mb-2">
       {{ __('…and :count more expense categories.', ['count' => $expenseCount - 5]) }}
      </p>
     @endif

     <button
      type="button"
      class="kt-btn kt-btn-xs kt-btn-outline mb-3"
      data-toggle-target="#expense_categories_full"
      data-toggle-open-label="{{ __('Hide all') }}"
      data-toggle-closed-label="{{ __('View all (:count)', ['count' => $expenseCount]) }}"
     >
      {{ __('View all (:count)', ['count' => $expenseCount]) }}
     </button>

     <div id="expense_categories_full" class="kt-scrollable-x-auto hidden mt-2">
      <table class="kt-table align-middle text-xs text-secondary-foreground">
       <thead>
        <tr class="bg-accent/40">
         <th class="text-start font-medium min-w-56 px-3 py-1.5">
          {{ __('Name') }}
         </th>
         <th class="w-24 px-3 py-1.5"></th>
        </tr>
       </thead>
       <tbody>
        @foreach ($expenseCategories as $cat)
         <tr>
          <td class="px-3 py-1.5">
           <form method="POST" action="{{ route('settings.categories.expense.update', $cat) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <input
             type="text"
             name="name"
             value="{{ old('name', $cat->name) }}"
             class="kt-input w-full text-xs"
            />
            <button type="submit" class="kt-btn kt-btn-xs kt-btn-outline">
             {{ __('Save') }}
            </button>
           </form>
          </td>
          <td class="px-3 py-1.5 text-end">
           <form
            method="POST"
            action="{{ route('settings.categories.expense.destroy', $cat) }}"
            class="js-confirm-delete inline-block"
            data-confirm-title="{{ __('Delete expense category?') }}"
            data-confirm-message="{{ __('Existing records that use this category will keep it, but it will no longer appear as a choice.') }}"
           >
            @csrf
            @method('DELETE')
            <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
             {{ __('Delete') }}
            </button>
           </form>
          </td>
         </tr>
        @endforeach
       </tbody>
      </table>
     </div>
    @endif
   </div>
  </div>
 </div>

 {{-- Other custom lookups (generic groups as their own cards) --}}
 @if (! empty($customLookups) && $customLookups->isNotEmpty())
  <div class="settings-grid-2 settings-grid-2-mt">
   @foreach ($customLookups as $group => $items)
   <div class="kt-card lookup-card cat-pulse-lookup-card">
    <div class="kt-card-header items-center justify-between">
     <h3 class="kt-card-title text-sm">
      {{ $group }}
     </h3>
    </div>
    <div class="kt-card-content px-5 py-5 lookup-card-body">
     {{-- Add value to this lookup group --}}
     <form method="POST" action="{{ route('settings.categories.lookup.store') }}" class="flex flex-wrap items-center gap-2 mb-4">
      @csrf
      <input type="hidden" name="type" value="{{ $group }}">
      <input
       type="text"
       name="name"
       placeholder="{{ __('New :group value', ['group' => strtolower($group)]) }}"
       class="kt-input w-full sm:w-72 text-xs"
      />
      <input
       type="text"
       name="description"
       placeholder="{{ __('Description (optional)') }}"
       class="kt-input w-full sm:flex-1 text-xs"
      />
      <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
       {{ __('Add') }}
      </button>
     </form>

     <div class="kt-scrollable-x-auto">
      <table class="kt-table align-middle text-xs text-secondary-foreground">
       <thead>
        <tr class="bg-accent/40">
         <th class="text-start font-medium min-w-40 px-3 py-1.5">
          {{ __('Name') }}
         </th>
         <th class="text-start font-medium min-w-56 px-3 py-1.5">
          {{ __('Description') }}
         </th>
         <th class="w-24 px-3 py-1.5"></th>
        </tr>
       </thead>
       <tbody>
        @foreach ($items as $lookup)
         <tr>
          <td class="px-3 py-1.5 align-top">
           <form method="POST" action="{{ route('settings.categories.lookup.update', $lookup) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="group" value="{{ $lookup->group }}" />
            <input
             type="text"
             name="name"
             value="{{ old('name', $lookup->name) }}"
             class="kt-input w-full text-xs"
            />
          </td>
          <td class="px-3 py-1.5 align-top">
            <input
             type="text"
             name="description"
             value="{{ old('description', $lookup->description) }}"
             class="kt-input w-full text-xs mt-1"
            />
            <div class="flex justify-end mt-1">
             <button type="submit" class="kt-btn kt-btn-xs kt-btn-outline">
              {{ __('Save') }}
             </button>
            </div>
           </form>
          </td>
          <td class="px-3 py-1.5 text-end align-top">
           <form
            method="POST"
            action="{{ route('settings.categories.lookup.destroy', $lookup) }}"
            class="js-confirm-delete inline-block"
            data-confirm-title="{{ __('Delete lookup?') }}"
            data-confirm-message="{{ __('This lookup value will be removed from the system.') }}"
           >
            @csrf
            @method('DELETE')
            <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
             {{ __('Delete') }}
            </button>
           </form>
          </td>
         </tr>
        @endforeach
       </tbody>
      </table>
     </div>
    </div>
   </div>
   @endforeach
  </div>
 @endif
   </div>
  </div>
</div>
</div>

@push('scripts')
<script>
(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-toggle-target]');
        if (!btn) return;
        var targetSelector = btn.getAttribute('data-toggle-target');
        if (!targetSelector) return;
        var target = document.querySelector(targetSelector);
        if (!target) return;
        var isHidden = target.classList.contains('hidden');
        if (isHidden) {
            target.classList.remove('hidden');
            btn.textContent = btn.getAttribute('data-toggle-open-label') || 'Hide';
        } else {
            target.classList.add('hidden');
            btn.textContent = btn.getAttribute('data-toggle-closed-label') || 'View all';
        }
    });

    // Toggle Add lookup panel
    var openBtn = document.getElementById('open_add_lookup');
    var closeBtn = document.getElementById('close_add_lookup');
    var panel = document.getElementById('add_lookup_panel');
    if (openBtn && panel) {
        openBtn.addEventListener('click', function () {
            panel.classList.remove('hidden');
        });
    }
    if (closeBtn && panel) {
        closeBtn.addEventListener('click', function () {
            panel.classList.add('hidden');
        });
    }
})();
</script>
@endpush

@endsection
