@extends('layouts.metronic')

@section('title', __('Categories'))
@section('page_title', __('Categories'))

@section('content')
 <div class="pb-5">
  <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
   <div class="flex flex-col gap-1">
    <h1 class="font-medium text-lg text-foreground">
     {{ __('Categories') }}
    </h1>
    <p class="text-sm text-secondary-foreground">
     {{ __('Define income and expense categories to keep reports, budgets and savings goals organised.') }}
    </p>
   </div>
   <div class="flex items-center gap-2">
    <button type="button" class="kt-btn kt-btn-primary" id="open_add_lookup">
     <i class="ki-filled ki-plus text-base"></i>
     <span>{{ __('Add lookup') }}</span>
    </button>
    <a href="{{ route('settings.index') }}" class="kt-btn kt-btn-outline">
     <i class="ki-filled ki-left text-base"></i>
     <span>{{ __('Back to settings') }}</span>
    </a>
   </div>
  </div>
 </div>

 <style>
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
 </style>

<div class="kt-container-fixed pb-6">
 @if (session('success'))
  <div class="mb-4 text-xs text-green-700 bg-green-50 border border-green-200 rounded px-4 py-2">
   {{ session('success') }}
  </div>
 @endif
 @if (session('error'))
  <div class="mb-4 text-xs text-red-700 bg-red-50 border border-red-200 rounded px-4 py-2">
   {{ session('error') }}
  </div>
 @endif

 {{-- Quick add lookup panel (hidden by default) --}}
 <div id="add_lookup_panel" class="kt-card mb-5 hidden">
  <div class="kt-card-content px-5 py-4 flex flex-wrap items-center gap-3">
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
     <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
      {{ __('Save') }}
     </button>
     <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost" id="close_add_lookup">
      {{ __('Cancel') }}
     </button>
    </div>
   </form>
  </div>
 </div>

 <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-2">
  {{-- Currencies lookup --}}
  <div class="kt-card lookup-card">
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
  <div class="kt-card lookup-card">
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
  <div class="kt-card lookup-card">
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
  <div class="kt-card lookup-card" id="expense_categories_card">
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
  <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-2 mt-5">
   @foreach ($customLookups as $group => $items)
   <div class="kt-card lookup-card">
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
