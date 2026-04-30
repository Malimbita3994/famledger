@extends('layouts.metronic')

@section('title', __('Search'))
@section('page_title', !empty($q) ? __('Search results') : __('Search'))

@php
    $entityGroups = $entityGroups ?? [];
    $entityTotal = (int) ($entityTotal ?? 0);
    $currentPage = (int) ($filters['page'] ?? 1);
    $perPage = (int) ($filters['per_page'] ?? 15);
    $lastPage = max(1, (int) ceil(max(0, $total) / max(1, $perPage)));
    $familyCurrency = strtoupper((string) ($family->currency_code ?? config('currencies.default', 'TZS')));
    $qDisplay = $q !== '' ? \Illuminate\Support\Str::limit($q, 80) : '';
    $flFlat = function ($v, string $emptyLabel = '—'): string {
        if ($v === null || $v === '') {
            return $emptyLabel;
        }
        if (is_array($v)) {
            $s = implode(', ', array_map(static fn ($x) => is_scalar($x) || $x === null ? (string) $x : json_encode($x), $v));

            return $s !== '' ? $s : $emptyLabel;
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        return (string) $v;
    };

    $flCompactAmount = static function (float $n): string {
        $a = abs($n);
        if ($a >= 1_000_000) {
            return number_format($n / 1_000_000, 1).'M';
        }
        if ($a >= 100_000) {
            return number_format($n / 1_000, 1).'K';
        }

        return number_format($n, 2);
    };

    $flSearchGroups = [];
    $flSearchGroupOrder = [];
    foreach ($hits ?? [] as $idx => $hit) {
        $dr = is_array($hit['date'] ?? null) ? '' : (string) ($hit['date'] ?? '');
        $gKey = '_nodate';
        $gLabel = __('Unknown date');
        if ($dr !== '') {
            try {
                $c = \Carbon\Carbon::parse($dr);
                $gKey = $c->format('Y-m');
                $gLabel = mb_strtoupper($c->format('F Y'));
            } catch (\Throwable $e) {
            }
        }
        if (! isset($flSearchGroups[$gKey])) {
            $flSearchGroups[$gKey] = ['label' => $gLabel, 'rows' => []];
            $flSearchGroupOrder[] = $gKey;
        }
        $flSearchGroups[$gKey]['rows'][] = ['hit' => $hit, 'idx' => $idx];
    }
@endphp

@push('styles')
<style>
  .fl-search-card {
    animation: fl-search-fade 0.45s ease-out both;
  }
  @keyframes fl-search-fade {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fl-pulse-soft {
    0%, 100% { opacity: 0.15; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(1.1); }
  }
  .animate-pulse-soft {
    animation: fl-pulse-soft 2.5s infinite ease-in-out;
  }
  /* Tiles: elevation via utilities on the element; no hard edge colors */
  /* ES / HTML highlight in titles */
  .fl-result-title mark,
  .fl-result-title em {
    background: rgb(59 130 246 / 0.18);
    color: inherit;
    font-weight: 700;
    font-style: normal;
    border-radius: 4px;
    padding: 0 0.2em;
    box-shadow: 0 0 0 1px rgb(59 130 246 / 0.1) inset;
  }
  .dark .fl-result-title mark,
  .dark .fl-result-title em {
    background: rgb(96 165 250 / 0.22);
  }
</style>
@endpush

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12 min-w-0">
 <x-fin-back-link href="{{ route('families.overview') }}">
  {{ __('Back to family') }}
 </x-fin-back-link>

 @if(!$enabled)
  <div class="mt-6 mb-6 rounded-2xl border border-amber-500/30 bg-amber-500/[0.07] px-4 py-3 text-sm text-amber-950 dark:text-amber-100">
   <span class="font-medium">{{ __('Ledger transaction search is offline.') }}</span>
   {{ __('People, projects, wallets, and other family records still match below. To index income and expense lines, enable Elasticsearch and reindex.') }}
  </div>
 @endif
 @if(!empty($error))
  @php
    $isFallback = str_contains($error, 'Fell back to SQL');
    $errClass = $isFallback ? 'border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-200' : 'border-destructive/30 bg-destructive/10 text-destructive';
    $errIcon = $isFallback ? 'ki-warning-2 text-amber-600 dark:text-amber-400' : 'ki-information-5 text-destructive';
    $displayError = str_replace('(Fell back to SQL search temporarily)', '<span class="font-semibold block mt-1.5 opacity-90"><i class="ki-filled ki-database relative -top-px mr-1"></i> Fell back to database search temporarily.</span>', $error);
  @endphp
  <div class="mt-6 mb-6 flex items-start gap-4 rounded-2xl border {{ $errClass }} px-5 py-4 text-sm shadow-sm transition-all duration-300 hover:shadow-md">
   <i class="ki-filled {{ $errIcon }} text-2xl shrink-0 mt-0.5" aria-hidden="true"></i>
   <div class="leading-relaxed whitespace-pre-wrap flex-1">
     {!! $displayError !!}
   </div>
  </div>
 @endif

 {{-- Search is opened from the top bar (SweetAlert2); this page only shows URL-driven results. --}}
 <form method="get" action="{{ route('families.search.index') }}" id="fl-search-form" class="mt-6 flex flex-col gap-5 lg:gap-7.5">
  <input type="hidden" name="q" value="{{ $q }}" />
  <input type="hidden" name="nl" value="{{ request()->boolean('nl', true) ? '1' : '0' }}" />

  @if($didYouMean)
   <div class="flex flex-wrap items-center gap-2 rounded-xl bg-muted/40 px-3 py-2.5 text-sm border border-dashed border-border">
    <i class="ki-filled ki-message-question text-muted-foreground"></i>
    <span class="text-muted-foreground">{{ __('Did you mean') }}</span>
    <a href="{{ request()->fullUrlWithQuery(['q' => $didYouMean]) }}" class="font-semibold text-primary hover:underline">{{ $didYouMean }}</a>
   </div>
  @endif

  <div class="flex flex-col gap-5 lg:gap-7.5">
   {{-- Results --}}
   <div class="grow min-w-0 flex flex-col gap-5 w-full">
    @php
     $flEntityGroupOrder = ['settings', 'people', 'projects', 'properties', 'wallets', 'liabilities', 'budgets', 'savings_goals'];
     $flEntityLabels = [
      'settings' => __('Settings & workspace'),
      'people' => __('People'),
      'projects' => __('Projects'),
      'properties' => __('Properties'),
      'wallets' => __('Wallets'),
      'liabilities' => __('Liabilities'),
      'budgets' => __('Budgets'),
      'savings_goals' => __('Savings goals'),
     ];
     $flEntityIcons = [
      'settings' => 'ki-setting-2',
      'people' => 'ki-user',
      'projects' => 'ki-abstract-26',
      'properties' => 'ki-home-2',
      'wallets' => 'ki-wallet',
      'liabilities' => 'ki-credit-cart',
      'budgets' => 'ki-chart-line',
      'savings_goals' => 'ki-safe-2',
     ];
    @endphp
    @if($entityTotal > 0)
     <div class="kt-card fin-pulse-kt-card rounded-2xl border border-border shadow-sm bg-card overflow-hidden">
      <div class="kt-card-header border-b border-border flex flex-wrap items-center justify-between gap-3 px-5 py-4">
       <div class="min-w-0">
        <h3 class="kt-card-title text-sm">{{ __('Family records') }}</h3>
        <p class="text-xs text-muted-foreground mt-0.5">{{ __('Settings shortcuts, people, projects, wallets, and other database matches.') }}</p>
       </div>
       <span class="shrink-0 text-xs font-medium tabular-nums text-muted-foreground">{{ number_format($entityTotal) }} {{ $entityTotal === 1 ? __('match') : __('matches') }}</span>
      </div>
      <div class="kt-card-content p-5 lg:p-6 space-y-6">
      @foreach($flEntityGroupOrder as $gKey)
       @if(!empty($entityGroups[$gKey]))
        <section class="min-w-0 space-y-3" aria-labelledby="fl-entity-{{ $gKey }}">
         <h4 id="fl-entity-{{ $gKey }}" class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          <i class="ki-filled {{ $flEntityIcons[$gKey] ?? 'ki-element-11' }} text-primary/80"></i>
          {{ $flEntityLabels[$gKey] ?? $gKey }}
         </h4>
         <div class="grid w-full gap-3 md:gap-4 [grid-template-columns:repeat(auto-fill,minmax(min(100%,260px),1fr))]">
          @foreach($entityGroups[$gKey] as $row)
           <a
            href="{{ $row['url'] }}"
            class="fl-search-card group block rounded-[14px] border border-border/80 bg-card p-3.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/25 hover:shadow-md dark:bg-card no-underline text-inherit focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/35 focus-visible:ring-offset-2 focus-visible:ring-offset-background"
            aria-label="{{ $flEntityLabels[$gKey] ?? '' }}: {{ $row['title'] }}"
           >
            <div class="flex gap-3">
             <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted/60 text-primary ring-1 ring-inset ring-border/50 group-hover:bg-primary/10">
              <i class="ki-filled {{ $flEntityIcons[$gKey] ?? 'ki-element-11' }} text-lg"></i>
             </span>
             <div class="min-w-0 flex-1">
              <p class="line-clamp-2 text-[15px] font-semibold leading-snug text-foreground">{{ $row['title'] }}</p>
              @if(!empty($row['subtitle']))
               <p class="mt-1 line-clamp-2 text-[12px] leading-tight text-muted-foreground sm:text-[13px]">{{ $row['subtitle'] }}</p>
              @endif
             </div>
            </div>
           </a>
          @endforeach
         </div>
        </section>
       @endif
      @endforeach
      </div>
     </div>
    @endif
    @if($entityTotal > 0 && $total === 0 && $q !== '')
     <p class="text-xs leading-relaxed text-muted-foreground px-0.5">
      @if($enabled)
       {{ __('No matching income or expense lines for this search in the ledger index.') }}
      @else
       {{ __('Income and expense lines are not included here while ledger search is offline.') }}
      @endif
     </p>
    @endif
    @php
     $flInc = (float) ($incomeTotal ?? 0);
     $flExp = (float) ($expenseTotal ?? 0);
     $flPool = $flInc + $flExp;
     $flExpensePct = $flPool > 0.0001 ? (int) round(100 * $flExp / $flPool) : null;
     $flSortKey = is_array(request('sort')) ? 'date_desc' : (string) request('sort', 'date_desc');
     if (! in_array($flSortKey, ['date_desc', 'date_asc', 'amount_desc', 'amount_asc'], true)) {
         $flSortKey = 'date_desc';
     }
     $flSortLabels = [
         'date_desc' => __('Newest first'),
         'date_asc' => __('Oldest first'),
         'amount_desc' => __('Highest amount'),
         'amount_asc' => __('Lowest amount'),
     ];
    @endphp
    @if($total > 0 || $entityTotal === 0)
    <div class="kt-card fin-pulse-kt-card rounded-2xl border border-border shadow-sm bg-card overflow-hidden">
     <div class="kt-card-header border-b border-border px-5 py-4 space-y-4">
      {{-- Grid keeps title and order control in separate columns (no flex overlap) --}}
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_14rem] lg:items-start lg:gap-x-6 lg:gap-y-0">
       <div class="min-w-0">
        <h3 class="kt-card-title text-sm">{{ __('Ledger results') }}</h3>
        @if($q !== '')
         <p class="text-xs text-muted-foreground mt-1">
          <span class="text-foreground font-medium">“{{ \Illuminate\Support\Str::limit($q, 120) }}”</span>
          @if($entityTotal > 0)
           <span class="text-muted-foreground"> — {{ __('with family records above') }}</span>
          @endif
         </p>
        @else
         <p class="text-xs text-muted-foreground mt-1">{{ __('Income and expense lines from your index.') }}</p>
        @endif
       </div>
       <div class="min-w-0 w-full lg:justify-self-end">
        <label for="fl-search-sort" class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground">{{ __('Order') }}</label>
        <select
         id="fl-search-sort"
         name="sort"
         form="fl-search-form"
         onchange="this.form?.submit()"
         class="kt-input mt-1 w-full min-h-10 cursor-pointer rounded-lg border-border bg-background py-2 ps-3 pe-9 text-sm font-medium text-foreground shadow-sm focus:border-primary/40 focus:ring-2 focus:ring-primary/15"
         aria-describedby="fl-search-sort-hint"
        >
         <option value="date_desc" @selected($flSortKey === 'date_desc')>{{ $flSortLabels['date_desc'] }}</option>
         <option value="date_asc" @selected($flSortKey === 'date_asc')>{{ $flSortLabels['date_asc'] }}</option>
         <option value="amount_desc" @selected($flSortKey === 'amount_desc')>{{ $flSortLabels['amount_desc'] }}</option>
         <option value="amount_asc" @selected($flSortKey === 'amount_asc')>{{ $flSortLabels['amount_asc'] }}</option>
        </select>
        <p id="fl-search-sort-hint" class="mt-1.5 text-[11px] leading-snug text-muted-foreground">{{ __('Applies to transaction rows below when the ledger index is enabled.') }}</p>
       </div>
      </div>
      <div class="flex flex-wrap items-center gap-2 mt-4 lg:mt-0">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-b from-muted to-muted/60 border border-muted/80 px-3 py-1.5 text-sm font-medium text-foreground shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
         <span class="tabular-nums font-bold">{{ number_format($total) }}</span>
         <span class="text-muted-foreground">{{ $entityTotal > 0 ? __('ledger rows') : ($total === 1 ? __('result') : __('results')) }}</span>
        </span>
        @if($entityTotal > 0)
         <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20 px-3 py-1.5 text-sm font-medium text-primary shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
          <i class="ki-filled ki-people text-base opacity-90" aria-hidden="true"></i>
          <span class="tabular-nums font-bold">{{ number_format($entityTotal) }}</span>
          <span>{{ __('family matches') }}</span>
         </span>
        @endif
        @if($totalAmount !== null && $total > 0)
         <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-muted/50 to-muted/30 border border-border/50 px-3 py-1.5 text-sm font-semibold text-foreground shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
          <i class="ki-filled ki-wallet text-base text-primary opacity-80" aria-hidden="true"></i>
          <span class="tabular-nums">{{ $flCompactAmount((float) $totalAmount) }}</span>
          <span class="text-[10px] uppercase font-bold text-muted-foreground">{{ $familyCurrency }}</span>
         </span>
         <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-emerald-500/10 to-emerald-500/5 border border-emerald-500/20 px-3 py-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
          <i class="ki-filled ki-arrow-down-left text-sm" aria-hidden="true"></i>
          <span>{{ __('Income') }}</span>
          <span class="tabular-nums font-bold">{{ $flCompactAmount($flInc) }}</span>
         </span>
         <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500/10 to-rose-500/5 border border-rose-500/20 px-3 py-1.5 text-sm font-medium text-rose-600 dark:text-rose-400 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
          <i class="ki-filled ki-arrow-up-right text-sm" aria-hidden="true"></i>
          <span>{{ __('Expense') }}</span>
          <span class="tabular-nums font-bold">{{ $flCompactAmount($flExp) }}</span>
         </span>
        @endif
       </div>
      @if($flExpensePct !== null && $enabled && $total > 0)
       <p class="flex items-start gap-2 text-xs leading-relaxed text-muted-foreground">
        <i class="ki-filled ki-abstract-26 mt-0.5 shrink-0 text-primary/70" aria-hidden="true"></i>
        <span>{{ __('Expenses are :pct% of income plus expenses in these results.', ['pct' => $flExpensePct]) }}</span>
       </p>
      @endif
     </div>

    <div class="kt-card-content px-5 py-8 sm:px-6 sm:py-10 lg:px-8">
    @if(count($hits) === 0)
     @if($entityTotal > 0)
      <div class="mx-auto max-w-md text-center py-10 fl-search-card">
       <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-tr from-muted/80 to-muted text-muted-foreground shadow-sm ring-4 ring-muted/20">
        <i class="ki-filled ki-chart-line-down text-4xl" aria-hidden="true"></i>
       </div>
       <h4 class="text-lg font-bold text-foreground tracking-tight">{{ __('No ledger transactions for this search') }}</h4>
       <p class="text-sm text-muted-foreground mt-3 leading-relaxed">{{ __('Try a different query, or open a match under Family records above.') }}</p>
      </div>
     @else
      <div class="mx-auto max-w-md text-center py-12 lg:py-16 fl-search-card">
       <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-tr from-primary/10 to-primary/5 text-primary shadow-inner ring-8 ring-primary/5 relative">
        <div class="absolute inset-0 rounded-full animate-pulse-soft bg-primary"></div>
        <i class="ki-filled ki-search-list text-5xl relative z-10" aria-hidden="true"></i>
       </div>
       <h4 class="text-xl font-bold text-foreground tracking-tight">{{ __('No results match your search') }}</h4>
       <p class="text-sm text-muted-foreground mt-4 leading-relaxed">{{ __('Use at least two characters to find people and records. Enable the ledger index to search income and expense lines.') }}</p>
      </div>
     @endif
    @else
     <div class="space-y-8">
      @foreach($flSearchGroupOrder as $gKey)
       @php $group = $flSearchGroups[$gKey]; @endphp
       <section class="min-w-0 space-y-3" aria-labelledby="fl-month-{{ \Illuminate\Support\Str::slug($gKey) }}">
        <h4 id="fl-month-{{ \Illuminate\Support\Str::slug($gKey) }}" class="border-s-2 border-primary ps-3 text-sm font-semibold uppercase tracking-wider text-primary">{{ $group['label'] }}</h4>
        {{-- auto-fill minmax(300px): multiple columns when space allows --}}
        <div class="grid w-full gap-3 md:gap-4 [grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr))]">
         @foreach($group['rows'] as $row)
          @php
           $hit = $row['hit'];
           $idx = (int) $row['idx'];
           $ht = $hit['highlight']['title'] ?? null;
           $titleIsHtml = false;
           $hlTitle = '';
           if (is_array($ht) && isset($ht[0]) && is_string($ht[0])) {
            $hlTitle = $ht[0];
            $titleIsHtml = true;
           } elseif (is_string($ht)) {
            $hlTitle = $ht;
            $titleIsHtml = true;
           }
           if ($hlTitle === '') {
            $rawT = $hit['title'] ?? '';
            $hlTitle = is_array($rawT) ? implode(' ', array_map(static fn ($x) => is_scalar($x) || $x === null ? (string) $x : '', $rawT)) : (string) $rawT;
            $titleIsHtml = false;
           }
           $amount = $hit['amount'] ?? 0;
           $cur = !empty($hit['currency_code']) ? strtoupper((string) $hit['currency_code']) : $familyCurrency;
           $cat = $flFlat($hit['category'] ?? null);
           $person = $flFlat($hit['person'] ?? null);
           $dateRaw = is_array($hit['date'] ?? null) ? '' : (string) ($hit['date'] ?? '');
           try {
            $dateLabel = $dateRaw !== '' ? \Carbon\Carbon::parse($dateRaw)->format('M j, Y') : '—';
           } catch (\Throwable $e) {
            $dateLabel = $dateRaw !== '' ? $dateRaw : '—';
           }
           $typeLabel = is_array($hit['type'] ?? null) ? '' : (string) ($hit['type'] ?? '');
           $isIncome = $typeLabel === 'income';
           $recordId = (int) ($hit['record_id'] ?? 0);
           $resultHref = null;
           if ($recordId > 0) {
            if ($typeLabel === 'income') {
             $resultHref = route('families.incomes.show', $recordId);
            } elseif ($typeLabel === 'expense') {
             $resultHref = route('families.expenses.show', $recordId);
            }
           }
           $delay = min(12, $idx);
           $metaLine = $cat.' • '.$person.' • '.$dateLabel;
           $cardTitlePlain = strip_tags($hlTitle);
          @endphp
          @if($resultHref)
          <a
           href="{{ $resultHref }}"
           class="fl-search-card block cursor-pointer rounded-xl border border-border bg-card shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md dark:bg-card no-underline text-inherit focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/35 focus-visible:ring-offset-2 focus-visible:ring-offset-background"
           style="animation-delay: {{ $delay * 30 }}ms"
           aria-label="{{ __('Open transaction') }}: {{ $cardTitlePlain }}"
          >
          @else
          <article
           class="fl-search-card rounded-xl border border-border bg-card shadow-sm transition-all duration-200 dark:bg-card"
           style="animation-delay: {{ $delay * 30 }}ms"
          >
          @endif
           <div class="px-3.5 py-3 sm:px-4 sm:py-3.5">
            {{-- Top: dot + amount --}}
            <div class="flex items-center justify-between gap-2">
             <span
              class="h-2 w-2 shrink-0 rounded-full ring-2 {{ $isIncome ? 'bg-primary ring-primary/35' : 'bg-destructive ring-destructive/35' }}"
              aria-hidden="true"
             ></span>
             <div class="min-w-0 text-end">
              <p class="text-[18px] font-bold tabular-nums leading-none text-foreground sm:text-[20px]">{{ number_format((float) $amount, 2) }}</p>
              <p class="mt-0.5 text-[10px] font-medium text-muted-foreground">{{ $cur }}</p>
             </div>
            </div>
            {{-- Title --}}
            <h3 class="fl-result-title mt-2.5 line-clamp-2 text-[15px] font-semibold leading-snug text-foreground sm:text-[16px]">
             @if(!empty($titleIsHtml))
              {!! $hlTitle !!}
             @else
              {{ $hlTitle }}
             @endif
            </h3>
            {{-- Metadata (single line) --}}
            <p class="mt-1.5 truncate text-[12px] leading-tight text-muted-foreground sm:text-[13px]" title="{{ $metaLine }}">
             {{ $metaLine }}
            </p>
           </div>
          @if($resultHref)</a>@else</article>@endif
         @endforeach
        </div>
       </section>
      @endforeach
     </div>
    @endif

    @if($lastPage > 1)
     <nav class="flex flex-wrap items-center justify-between gap-4 border-t border-border pt-4" aria-label="{{ __('Pagination Navigation') }}">
      <p class="text-sm text-muted-foreground">
       {{ __('Page :cur of :last', ['cur' => $currentPage, 'last' => $lastPage]) }}
      </p>
      <div class="flex items-center gap-2">
       @if($currentPage > 1)
        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="kt-btn kt-btn-sm kt-btn-outline rounded-full px-5">{{ __('Previous') }}</a>
       @endif
       @if($currentPage < $lastPage)
        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="kt-btn kt-btn-sm kt-btn-primary rounded-full px-5 shadow-md shadow-primary/20">{{ __('Next') }}</a>
       @endif
      </div>
     </nav>
    @endif
   </div>
  </div>
    @endif
 </div>
 </div>
</form>
</div>
@endsection
