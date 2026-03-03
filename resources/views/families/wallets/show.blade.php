@extends('layouts.metronic')

@section('title', $wallet->name)
@section('page_title', $wallet->name)

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.wallets.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }} wallets
    </a>

    <div class="grid gap-5 lg:gap-7.5 max-w-4xl">
        <div class="kt-card">
            <div class="kt-card-content flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center rounded-full size-14 shrink-0 bg-muted text-foreground">
                        <i class="ki-filled ki-wallet text-2xl"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-semibold text-foreground">{{ $wallet->name }}</h1>
                        <p class="text-sm text-muted-foreground">{{ $walletTypes[$wallet->type] ?? $wallet->type }} · {{ $wallet->currency_code }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-muted-foreground">Current balance</p>
                    <p class="text-2xl font-semibold tabular-nums">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}</p>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Details</h3>
                <a href="{{ route('families.wallets.edit', [$family, $wallet]) }}" class="kt-btn kt-btn-sm kt-btn-ghost">Edit</a>
            </div>
            <div class="kt-card-content">
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Type</dt>
                        <dd class="mt-0.5 text-foreground">{{ $walletTypes[$wallet->type] ?? $wallet->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Currency</dt>
                        <dd class="mt-0.5 text-foreground">{{ $wallet->currency_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Shared</dt>
                        <dd class="mt-0.5">{{ $wallet->is_shared ? 'Yes — all members can use' : 'No — personal wallet' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Status</dt>
                        <dd class="mt-0.5">
                            <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">{{ ucfirst($wallet->status) }}</span>
                        </dd>
                    </div>
                    @if ($wallet->description)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Description</dt>
                        <dd class="mt-0.5 text-foreground">{{ $wallet->description }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        @if ($outgoingTransfers->isNotEmpty() || $incomingTransfers->isNotEmpty())
        <div class="kt-card">
            <div class="kt-card-header flex-wrap gap-2">
                <h3 class="kt-card-title text-sm">Transfers</h3>
                <a href="{{ route('families.transfers.index', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-sm kt-btn-ghost">View all</a>
            </div>
            <div class="kt-card-content">
                @if ($outgoingTransfers->isNotEmpty())
                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">Outgoing</p>
                <ul class="space-y-1.5 mb-4">
                    @foreach ($outgoingTransfers as $t)
                    <li class="flex items-center justify-between text-sm">
                        <span>To <a href="{{ route('families.wallets.show', [$family, $t->toWallet]) }}" class="text-primary hover:underline">{{ $t->toWallet->name }}</a></span>
                        <span class="tabular-nums text-destructive">− {{ number_format($t->amount, 2) }} {{ $t->currency_code }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
                @if ($incomingTransfers->isNotEmpty())
                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">Incoming</p>
                <ul class="space-y-1.5">
                    @foreach ($incomingTransfers as $t)
                    <li class="flex items-center justify-between text-sm">
                        <span>From <a href="{{ route('families.wallets.show', [$family, $t->fromWallet]) }}" class="text-primary hover:underline">{{ $t->fromWallet->name }}</a></span>
                        <span class="tabular-nums text-success">+ {{ number_format($t->amount, 2) }} {{ $t->currency_code }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
