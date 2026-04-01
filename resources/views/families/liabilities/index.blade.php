@extends('layouts.metronic')

@section('title', 'Family Liabilities')
@section('page_title', 'Family Liabilities')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.overview') }}">
        Back to {{ $family->name }}
    </x-fin-back-link>

    @php
        $currency = $family->currency_code ?? config('currencies.default', 'TZS');
    @endphp

    <style>
    .liability-kpi-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        width: 100%;
    }
    @media (min-width: 768px) {
        .liability-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    </style>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Family Liabilities</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Loans, debts and obligations that reduce {{ $family->name }}'s net wealth.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('families.liabilities.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i>
                New liability
            </a>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="liability-kpi-grid mb-6">
        <x-famledger.pulse-stat-card
            label="Total outstanding"
            :value="number_format($totals['total_outstanding'], 0) . ' ' . $currency"
        />

        <x-famledger.pulse-stat-card
            label="Active liabilities"
            :value="(string) $totals['active_count']"
        />

        <x-famledger.pulse-stat-card
            label="Closed liabilities"
            :value="(string) $totals['closed_count']"
        />
    </div>

    {{-- List --}}
    <div class="mt-4 kt-card rounded-xl border border-border bg-card">
        <div class="kt-card-header border-b border-border flex items-center justify-between gap-3">
            <h3 class="kt-card-title text-sm">Liabilities</h3>
        </div>
        <div class="kt-card-content p-4 lg:p-5">
            @if ($liabilities->isEmpty())
                <div class="py-10 text-center text-muted-foreground text-sm">
                    No liabilities recorded yet. Record loans, mortgages or debts so Wealth can calculate net wealth accurately.
                </div>
            @else
                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto hidden md:block">
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
                                        <a href="{{ route('families.liabilities.show', $liability) }}" class="font-semibold text-foreground hover:text-primary">
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
                                        <a href="{{ route('families.liabilities.edit', $liability) }}" class="kt-btn kt-btn-xs kt-btn-ghost">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden space-y-4">
                    @foreach ($liabilities as $liability)
                        <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-4 flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex flex-col min-w-0">
                                    <a href="{{ route('families.liabilities.show', $liability) }}" class="text-sm font-semibold text-foreground hover:text-primary truncate">
                                        {{ $liability->name }}
                                    </a>
                                    <span class="text-[11px] text-muted-foreground mt-0.5">
                                        Principal: {{ number_format($liability->principal_amount, 0) }} {{ $family->currency_code ?? config('currencies.default', 'TZS') }}
                                    </span>
                                    <span class="text-[11px] text-secondary-foreground mt-0.5">
                                        Type: {{ ucfirst($liability->type) }}
                                    </span>
                                </div>
                                @php
                                    $badgeClass = match($liability->status) {
                                        'active' => 'kt-badge-primary',
                                        'overdue' => 'kt-badge-destructive',
                                        'closed' => 'kt-badge-success',
                                        default => 'kt-badge-outline',
                                    };
                                @endphp
                                <span class="kt-badge kt-badge-sm {{ $badgeClass }} kt-badge-outline shrink-0">
                                    {{ ucfirst($liability->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[11px] text-muted-foreground border border-border/60 rounded-xl px-3 py-2 bg-muted/30">
                                <div>
                                    <span class="uppercase tracking-wide block mb-0.5">Outstanding</span>
                                    <span class="text-sm font-semibold text-foreground tabular-nums">
                                        {{ number_format($liability->outstanding_balance, 0) }} {{ $family->currency_code ?? config('currencies.default', 'TZS') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="uppercase tracking-wide block mb-0.5">Due date</span>
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $liability->due_date ? $liability->due_date->format('M j, Y') : '—' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="uppercase tracking-wide block mb-0.5">Linked to</span>
                                    <span class="text-sm font-medium text-foreground">
                                        @if($liability->property)
                                            Property: {{ $liability->property->name }}
                                        @elseif($liability->project)
                                            Project: {{ $liability->project->name }}
                                        @elseif($liability->wallet)
                                            Wallet: {{ $liability->wallet->name }}
                                        @else
                                            Unlinked
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <a href="{{ route('families.liabilities.edit', $liability) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
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

