@extends('layouts.metronic')

@section('title', 'Family Liabilities')
@section('page_title', 'Family Liabilities')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Family Liabilities</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Loans, debts and obligations that reduce {{ $family->name }}'s net wealth.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('families.liabilities.create', $family) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i>
                New liability
            </a>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="kt-card rounded-2xl border border-border bg-card p-4">
            <div class="text-[11px] text-muted-foreground uppercase tracking-wide mb-1.5">Total outstanding</div>
            <div class="text-xl font-semibold tabular-nums">
                {{ number_format($totals['total_outstanding'], 0) }} {{ $family->currency_code ?? config('currencies.default', 'TZS') }}
            </div>
        </div>
        <div class="kt-card rounded-2xl border border-border bg-card p-4">
            <div class="text-[11px] text-muted-foreground uppercase tracking-wide mb-1.5">Active liabilities</div>
            <div class="text-lg font-semibold tabular-nums">{{ $totals['active_count'] }}</div>
        </div>
        <div class="kt-card rounded-2xl border border-border bg-card p-4">
            <div class="text-[11px] text-muted-foreground uppercase tracking-wide mb-1.5">Closed liabilities</div>
            <div class="text-lg font-semibold tabular-nums">{{ $totals['closed_count'] }}</div>
        </div>
    </div>

    {{-- List --}}
    <div class="kt-card rounded-2xl border border-border bg-card">
        <div class="kt-card-header border-b border-border flex items-center justify-between gap-3">
            <h3 class="kt-card-title text-sm">Liabilities</h3>
        </div>
        <div class="kt-card-content p-0">
            @if ($liabilities->isEmpty())
                <div class="py-10 text-center text-muted-foreground text-sm">
                    No liabilities recorded yet. Record loans, mortgages or debts so Wealth can calculate net wealth accurately.
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border text-xs">
                        <thead>
                            <tr>
                                <th class="min-w-[180px]">Liability</th>
                                <th class="min-w-[90px]">Type</th>
                                <th class="min-w-[120px] text-right">Outstanding</th>
                                <th class="min-w-[120px]">Linked to</th>
                                <th class="min-w-[100px]">Status</th>
                                <th class="min-w-[110px]">Due date</th>
                                <th class="min-w-[80px] text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liabilities as $liability)
                                <tr>
                                    <td>
                                        <a href="{{ route('families.liabilities.show', [$family, $liability]) }}" class="font-semibold text-foreground hover:text-primary">
                                            {{ $liability->name }}
                                        </a>
                                        <div class="text-[11px] text-muted-foreground">
                                            Principal: {{ number_format($liability->principal_amount, 0) }} {{ $family->currency_code ?? config('currencies.default', 'TZS') }}
                                        </div>
                                    </td>
                                    <td>{{ ucfirst($liability->type) }}</td>
                                    <td class="text-right tabular-nums">
                                        {{ number_format($liability->outstanding_balance, 0) }} {{ $family->currency_code ?? config('currencies.default', 'TZS') }}
                                    </td>
                                    <td>
                                        @if($liability->property)
                                            <span class="text-[11px] text-muted-foreground">Property:</span>
                                            <span class="text-[11px] font-medium">{{ $liability->property->name }}</span>
                                        @elseif($liability->project)
                                            <span class="text-[11px] text-muted-foreground">Project:</span>
                                            <span class="text-[11px] font-medium">{{ $liability->project->name }}</span>
                                        @elseif($liability->wallet)
                                            <span class="text-[11px] text-muted-foreground">Wallet:</span>
                                            <span class="text-[11px] font-medium">{{ $liability->wallet->name }}</span>
                                        @else
                                            <span class="text-[11px] text-muted-foreground">Unlinked</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($liability->status) {
                                                'active' => 'kt-badge-primary',
                                                'overdue' => 'kt-badge-destructive',
                                                'closed' => 'kt-badge-success',
                                                default => 'kt-badge-outline',
                                            };
                                        @endphp
                                        <span class="kt-badge kt-badge-sm {{ $badgeClass }} kt-badge-outline">
                                            {{ ucfirst($liability->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $liability->due_date ? $liability->due_date->format('M j, Y') : '—' }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('families.liabilities.edit', [$family, $liability]) }}" class="kt-btn kt-btn-xs kt-btn-ghost">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($liabilities->hasPages())
                    <div class="flex justify-center pt-4 pb-5">
                        {{ $liabilities->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

