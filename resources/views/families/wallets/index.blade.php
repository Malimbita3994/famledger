@extends('layouts.metronic')

@section('title', 'Wallets')
@section('page_title', 'Wallets')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12 w-full max-w-full min-w-0">
    <a href="{{ route('families.overview') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 w-full min-w-0">
        <h1 class="font-medium text-lg text-mono shrink-0">Family Wallets</h1>
        <div class="flex flex-wrap justify-end gap-1.5 w-full sm:w-auto sm:ms-auto min-w-0">
            <x-famledger.pulse-button variant="outline" :href="route('families.incomes.create')">
                <i class="ki-filled ki-arrow-up"></i>
                Record income
            </x-famledger.pulse-button>
            <x-famledger.pulse-button variant="outline" :href="route('families.expenses.create')">
                <i class="ki-filled ki-arrow-down"></i>
                Record expense
            </x-famledger.pulse-button>
            <x-famledger.pulse-button variant="outline" :href="route('families.transfers.create')">
                <i class="ki-filled ki-arrow-right-left"></i>
                Transfer
            </x-famledger.pulse-button>
            <x-famledger.pulse-button variant="outline" :href="route('families.reconciliations.create')">
                <i class="ki-filled ki-check-circle"></i>
                Reconcile
            </x-famledger.pulse-button>
            <x-famledger.pulse-button variant="outline" :href="route('families.savings-goals.index')">
                <i class="ki-filled ki-safe"></i>
                Savings goals
            </x-famledger.pulse-button>
            <x-famledger.pulse-button variant="primary" :href="route('families.wallets.create')">
                <i class="ki-filled ki-plus"></i>
                Add wallet
            </x-famledger.pulse-button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($wallets->isEmpty())
        <div class="kt-card">
            <div class="kt-card-content py-12 text-center">
                <i class="ki-filled ki-wallet text-5xl text-muted-foreground mb-4"></i>
                <p class="font-semibold text-foreground">No wallets yet</p>
                <p class="text-sm text-secondary-foreground mt-1">Create a wallet to start tracking income, expenses, and transfers for this family.</p>
                <x-famledger.pulse-button variant="primary" class="mt-6" :href="route('families.wallets.create')">Add wallet</x-famledger.pulse-button>
            </div>
        </div>
    @else
        <div class="kt-card kt-card-grid w-full min-w-0 max-w-full mt-4">
            <div class="kt-card-content p-0">
                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto hidden md:block">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[160px]">Wallet</th>
                                <th class="min-w-[220px]">Description</th>
                                <th class="min-w-[120px]">Type</th>
                                <th class="min-w-[100px]">Currency</th>
                                <th class="min-w-[120px]">Balance</th>
                                <th class="min-w-[80px]">Shared</th>
                                <th class="min-w-[80px]">Main</th>
                                <th class="min-w-[80px]">Status</th>
                                <th class="w-[60px]">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wallets as $wallet)
                            <tr>
                                <td>
                                    <div class="flex flex-col gap-0.5 min-w-0">
                                        <a href="{{ route('families.wallets.show', $wallet) }}" class="flex items-center gap-2.5 hover:opacity-90">
                                            <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">
                                                <i class="ki-filled ki-wallet text-lg"></i>
                                            </span>
                                            <span class="text-sm font-medium text-mono hover:text-primary min-w-0 truncate">{{ $wallet->name }}</span>
                                        </a>
                                        @if ($wallet->dedicatedProject)
                                            <p class="text-xs text-muted-foreground mt-0.5 ps-11">
                                                <span class="text-muted-foreground">Holds funds for</span>
                                                <a href="{{ route('families.projects.show', $wallet->dedicatedProject) }}" class="text-primary hover:underline font-medium">{{ $wallet->dedicatedProject->name }}</a>
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-top text-sm text-secondary-foreground">
                                    @if (filled($wallet->description))
                                        <span class="line-clamp-3 max-w-[22rem] break-words" title="{{ e($wallet->description) }}">{{ $wallet->description }}</span>
                                    @else
                                        <span class="text-muted-foreground">—</span>
                                    @endif
                                </td>
                                <td class="text-foreground font-normal">{{ $walletTypes[$wallet->type] ?? $wallet->type }}</td>
                                <td class="text-foreground font-normal">{{ $wallet->currency_code }}</td>
                                <td class="font-medium tabular-nums">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}</td>
                                <td>
                                    @if ($wallet->is_shared)
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Shared</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Personal</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($wallet->is_primary)
                                        <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">Main wallet</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">{{ ucfirst($wallet->status) }}</span>
                                </td>
                                <td>
                                    <div class="kt-menu flex-inline" data-kt-menu="true">
                                        <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                            <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="{{ route('families.wallets.show', $wallet) }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                        <span class="kt-menu-title">View</span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="{{ route('families.wallets.edit', $wallet) }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                        <span class="kt-menu-title">Edit</span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-separator"></div>
                                                <div class="kt-menu-item">
                                                    <form action="{{ route('families.wallets.destroy', $wallet) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="Remove this wallet?" data-confirm-message="This cannot be undone.">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                            <span class="kt-menu-title">Remove</span>
                                                        </button>
                                                    </form>
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
                <div class="md:hidden p-4 space-y-4">
                    @foreach ($wallets as $wallet)
                        <div class="rounded-2xl border border-border bg-background shadow-sm p-4 flex flex-col gap-3">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground">
                                        <i class="ki-filled ki-wallet text-lg"></i>
                                    </span>
                                    <div class="flex flex-col min-w-0">
                                        <a href="{{ route('families.wallets.show', $wallet) }}" class="text-sm font-semibold text-foreground hover:text-primary truncate">
                                            {{ $wallet->display_name }}
                                        </a>
                                        @if (filled($wallet->description))
                                            <span class="text-[10px] font-medium uppercase tracking-wide text-muted-foreground mt-1">{{ __('Description') }}</span>
                                            <span class="text-[11px] text-secondary-foreground line-clamp-3">
                                                {{ $wallet->description }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">
                                        {{ ucfirst($wallet->status) }}
                                    </span>
                                    @if ($wallet->is_primary)
                                        <span class="kt-badge kt-badge-xs kt-badge-primary kt-badge-outline">Main wallet</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Key numbers --}}
                            <div class="flex items-center justify-between gap-3 border border-border/60 rounded-xl px-3 py-2 bg-muted/40">
                                <div class="flex flex-col">
                                    <span class="text-[11px] text-muted-foreground uppercase tracking-wide">Balance</span>
                                    <span class="text-sm font-semibold text-foreground tabular-nums">
                                        {{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[11px] text-muted-foreground uppercase tracking-wide">Type</span>
                                    <span class="text-xs font-medium text-foreground">
                                        {{ $walletTypes[$wallet->type] ?? $wallet->type }}
                                    </span>
                                </div>
                            </div>

                            {{-- Meta --}}
                            <div class="flex items-center justify-between text-[11px] text-muted-foreground">
                                <span>Currency: <span class="font-medium text-foreground">{{ $wallet->currency_code }}</span></span>
                                <span>
                                    @if ($wallet->is_shared)
                                        <span class="kt-badge kt-badge-xs kt-badge-success kt-badge-outline">Shared</span>
                                    @else
                                        <span class="kt-badge kt-badge-xs kt-badge-secondary kt-badge-outline">Personal</span>
                                    @endif
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-wrap justify-end gap-2 pt-1">
                                <x-famledger.pulse-button variant="outline" size="sm" :href="route('families.wallets.show', $wallet)">
                                    View
                                </x-famledger.pulse-button>
                                <x-famledger.pulse-button variant="outline" size="sm" :href="route('families.wallets.edit', $wallet)">
                                    Edit
                                </x-famledger.pulse-button>
                                <form action="{{ route('families.wallets.destroy', $wallet) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="Remove this wallet?" data-confirm-message="This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <x-famledger.pulse-button variant="danger" size="sm" type="submit">Remove</x-famledger.pulse-button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

