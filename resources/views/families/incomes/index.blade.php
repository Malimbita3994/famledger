@extends('layouts.metronic')

@section('title', 'Income')
@section('page_title', 'Income')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.overview') }}">
        Back to {{ $family->name }}
    </x-fin-back-link>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Income</h1>
            <p class="text-sm text-muted-foreground mt-0.5">All income is recorded into a wallet. No wallet → no valid income.</p>
        </div>
        <a href="{{ route('families.incomes.create') }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            Record income
        </a>
    </div>
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
            <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($wallets->isNotEmpty())
        <div class="kt-card mb-4">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Wallet current balance</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-4">
                    @foreach ($wallets as $w)
                        <a href="{{ route('families.wallets.show', $w) }}" class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 hover:bg-muted/50 transition-colors">
                            <i class="ki-filled ki-wallet text-muted-foreground"></i>
                            <span class="font-medium text-sm">{{ $w->name }}</span>
                            <span class="tabular-nums text-sm {{ $w->balance >= 0 ? 'text-success' : 'text-destructive' }}">{{ number_format($w->balance, 2) }} {{ $w->currency_code }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="kt-card kt-card-grid min-w-full mt-4">
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">Income records</h3>
            <form method="get" action="{{ route('families.incomes.index') }}" class="flex flex-wrap items-center gap-2 justify-end w-full md:w-auto">
                <label for="wallet_id" class="text-sm text-muted-foreground">Wallet</label>
                <div class="inline-flex">
                    <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm" style="width: 180px;" onchange="this.form.submit()">
                        <option value="">All wallets</option>
                        @foreach ($wallets as $w)
                            <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($incomes->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrow-up text-4xl mb-2"></i>
                    <p class="text-sm">No income recorded yet.</p>
                    <a href="{{ route('families.incomes.create') }}" class="kt-btn kt-btn-primary mt-4">Record income</a>
                </div>
            @else
                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto hidden md:block">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[100px]">Date</th>
                                <th class="min-w-[140px]">Wallet</th>
                                <th class="min-w-[100px]">Category</th>
                                <th class="min-w-[120px]">Amount</th>
                                <th class="min-w-[140px]">Source</th>
                                <th class="min-w-[100px]">Recorded by</th>
                                <th class="w-[72px] text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incomes as $income)
                            <tr>
                                <td class="text-foreground">{{ $income->received_date->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('families.wallets.show', $income->wallet) }}" class="text-primary hover:underline">{{ $income->wallet->name }}</a>
                                    <span class="text-muted-foreground text-xs">({{ $income->wallet->currency_code }})</span>
                                </td>
                                <td class="text-foreground">
                                    @if ($income->category)
                                        {{ $income->category->parent ? $income->category->parent->name.' › '.$income->category->name : $income->category->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="font-medium tabular-nums text-success">+ {{ number_format($income->amount, 2) }} {{ $income->currency_code }}</td>
                                <td class="text-foreground">{{ $income->source ?? '—' }}</td>
                                <td class="text-muted-foreground text-sm">{{ $income->createdBy?->name ?? '—' }}</td>
                                <td class="text-center">
                                    <div class="kt-menu flex-inline justify-center" data-kt-menu="true">
                                        <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                            <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button" aria-label="{{ __('Actions') }}">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="{{ route('families.incomes.show', $income) }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                        <span class="kt-menu-title">{{ __('View') }}</span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="{{ route('families.incomes.edit', $income) }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                        <span class="kt-menu-title">{{ __('Edit') }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden p-4 mt-2 space-y-5">
                    @foreach ($incomes as $income)
                        <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-5 flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $income->wallet->name }}
                                    </span>
                                    <span class="text-[11px] text-muted-foreground">
                                        {{ $income->received_date->format('M j, Y') }}
                                    </span>
                                    @if ($income->category)
                                        <span class="text-[11px] text-secondary-foreground mt-0.5">
                                            {{ $income->category->parent ? $income->category->parent->name.' › '.$income->category->name : $income->category->name }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-success tabular-nums shrink-0">
                                    + {{ number_format($income->amount, 2) }} {{ $income->currency_code }}
                                </span>
                            </div>

                            <div class="text-[11px] text-muted-foreground">
                                @if ($income->source)
                                    <span class="font-medium text-foreground">Source:</span>
                                    <span>{{ $income->source }}</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-muted-foreground">
                                <span>
                                    {{ __('Recorded by:') }}
                                    <span class="font-medium text-foreground">{{ $income->createdBy?->name ?? '—' }}</span>
                                </span>
                                <div class="flex flex-wrap items-center gap-1.5 justify-end">
                                    <a href="{{ route('families.incomes.show', $income) }}" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('View') }}</a>
                                    <a href="{{ route('families.incomes.edit', $income) }}" class="kt-btn kt-btn-xs kt-btn-primary">{{ __('Edit') }}</a>
                                    <a href="{{ route('families.wallets.show', $income->wallet) }}" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Wallet') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-border">
                    {{ $incomes->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
