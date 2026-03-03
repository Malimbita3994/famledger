@extends('layouts.metronic')

@section('title', 'Transfers')
@section('page_title', 'Transfers')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Transfers</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Move money between wallets. Family wealth stays the same; only distribution changes.</p>
        </div>
        <a href="{{ route('families.transfers.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-arrow-right-left"></i>
            New transfer
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

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">Transfer history</h3>
            <form method="get" action="{{ route('families.transfers.index', $family) }}" class="flex items-center gap-2">
                <label for="wallet_id" class="text-sm text-muted-foreground">Wallet</label>
                <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">All wallets</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($transfers->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrow-right-left text-4xl mb-2"></i>
                    <p class="text-sm">No transfers yet.</p>
                    <a href="{{ route('families.transfers.create', $family) }}" class="kt-btn kt-btn-outline mt-4">New transfer</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[100px]">Date</th>
                                <th class="min-w-[140px]">From</th>
                                <th class="min-w-[40px]"></th>
                                <th class="min-w-[140px]">To</th>
                                <th class="min-w-[120px]">Amount</th>
                                <th class="min-w-[160px]">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfers as $transfer)
                            <tr>
                                <td class="text-foreground">{{ $transfer->transfer_date->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $transfer->fromWallet]) }}" class="text-primary hover:underline">{{ $transfer->fromWallet->name }}</a>
                                </td>
                                <td class="text-muted-foreground text-center"><i class="ki-filled ki-arrow-right text-sm"></i></td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $transfer->toWallet]) }}" class="text-primary hover:underline">{{ $transfer->toWallet->name }}</a>
                                </td>
                                <td class="font-medium tabular-nums">{{ number_format($transfer->amount, 2) }} {{ $transfer->currency_code }}</td>
                                <td class="text-foreground">{{ Str::limit($transfer->description ?? '—', 30) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $transfers->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
