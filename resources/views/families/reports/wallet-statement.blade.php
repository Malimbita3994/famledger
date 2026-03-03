@extends('layouts.metronic')

@section('title', 'Wallet Statement')
@section('page_title', 'Wallet Statement')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Wallet Statement</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Bank-like report: date, description, income, expense, running balance. Essential for reconciliation.</p>
        </div>
    </div>

    {{-- Filter report card (standard) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Filter report</h3>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.wallet-statement', $family) }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Wallet</label>
                    <select name="wallet_id" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[180px]">
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ $wallet && $wallet->id === $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">From</label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">To</label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                <a href="{{ route('families.reports.wallet-statement', $family) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    @if(!$wallet)
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card p-8 text-center text-muted-foreground">
            No wallet selected. Create a wallet for this family to see a statement.
        </div>
    @else
        <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header flex flex-wrap items-center justify-between gap-3">
                <h3 class="kt-card-title text-sm">{{ $wallet->name }} — {{ $dateFrom }} to {{ $dateTo }}</h3>
                <button type="button" onclick="window.print()" class="kt-btn kt-btn-sm kt-btn-ghost"><i class="ki-filled ki-printer text-sm mr-1"></i> Print</button>
            </div>
            <div class="kt-card-content p-0">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[100px]">Date</th>
                                <th class="min-w-[180px]">Description</th>
                                <th class="min-w-[120px] text-right">Income</th>
                                <th class="min-w-[120px] text-right">Expense</th>
                                <th class="min-w-[120px] text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                            <tr>
                                <td class="text-foreground">{{ $row['date']->format('M j, Y') }}</td>
                                <td class="text-foreground">{{ $row['description'] }}</td>
                                <td class="text-right tabular-nums {{ $row['income'] ? 'text-green-600 font-medium' : 'text-muted-foreground' }}">
                                    {{ $row['income'] !== null ? number_format($row['income'], 0) . ' ' . $currency : '—' }}
                                </td>
                                <td class="text-right tabular-nums {{ $row['expense'] ? 'text-red-600 font-medium' : 'text-muted-foreground' }}">
                                    {{ $row['expense'] !== null ? number_format($row['expense'], 0) . ' ' . $currency : '—' }}
                                </td>
                                <td class="text-right tabular-nums font-medium">{{ number_format($row['balance'], 0) }} {{ $currency }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 px-4 text-center text-muted-foreground text-sm">No transactions in this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
