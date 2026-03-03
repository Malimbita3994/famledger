@extends('layouts.metronic')

@section('title', 'Finance Reports')
@section('page_title', 'Finance Reports')

@php
    $report = $report ?? 'cash-flow';
    $tabs = [
        'cash-flow'  => ['label' => 'Cash flow', 'icon' => 'ki-arrow-right-left'],
        'income'     => ['label' => 'Income', 'icon' => 'ki-arrow-up'],
        'expense'    => ['label' => 'Expenses', 'icon' => 'ki-arrow-down'],
        'transfer'   => ['label' => 'Transfer', 'icon' => 'ki-arrow-right-left'],
        'budget'     => ['label' => 'Budget', 'icon' => 'ki-chart-pie-simple'],
        'savings'    => ['label' => 'Savings', 'icon' => 'ki-dollar'],
    ];
@endphp

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Finance Reports</h1>
        </div>
    </div>

    {{-- Filter report card with tabs --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header flex-wrap gap-2 border-b border-border">
            <h3 class="kt-card-title text-sm">Filter report</h3>
            <nav class="flex flex-wrap gap-1 mt-2 md:mt-0" role="tablist">
                @foreach($tabs as $key => $tab)
                    @php
                        $params = [$family, 'report' => $key, 'from' => $dateFrom ?? '', 'to' => $dateTo ?? ''];
                        if ($key !== 'budget' && $key !== 'savings') {
                            $params['wallet_id'] = $walletId ?? '';
                        }
                        if ($key === 'budget') {
                            $params['type'] = $filterType ?? '';
                            $params['status'] = $filterStatus ?? '';
                        }
                        $tabUrl = route('families.reports.cash-flow', $params);
                    @endphp
                    <a href="{{ $tabUrl }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $report === $key ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}">
                        <i class="ki-filled {{ $tab['icon'] }} text-base"></i>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.cash-flow', $family) }}" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="report" value="{{ $report }}">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">From</label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">To</label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                @if($report !== 'budget' && $report !== 'savings')
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Wallet</label>
                    <select name="wallet_id" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[160px]">
                        <option value="">All wallets</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ $walletId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($report === 'budget')
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Type</label>
                    <select name="type" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[160px]">
                        <option value="">All types</option>
                        @foreach($budgetTypes ?? [] as $value => $label)
                            <option value="{{ $value }}" {{ ($filterType ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Status</label>
                    <select name="status" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[120px]">
                        <option value="">All</option>
                        <option value="active" {{ ($filterStatus ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ ($filterStatus ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                @endif
                <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                <a href="{{ route('families.reports.cash-flow', [$family, 'report' => $report]) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- Cash flow report --}}
    @if($report === 'cash-flow')
    <div class="cash-flow-kpi-grid">
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Opening balance</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-wallet"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($openingBalance ?? 0, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Start of period</div>
        </div>
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total income</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-arrow-up"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">+ {{ number_format($totalIncome ?? 0, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">In period</div>
        </div>
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total expenses</span>
                <span class="text-red-500 text-lg shrink-0"><i class="ki-filled ki-arrow-down"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-red-600">− {{ number_format($totalExpenses ?? 0, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">In period</div>
        </div>
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Closing balance</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-safe"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($closingBalance ?? 0, 0) }} {{ $currency }}</div>
            @php $netFlow = $netFlow ?? 0; @endphp
            <div class="text-sm mt-2 {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">Net flow: {{ $netFlow >= 0 ? '+' : '' }}{{ number_format($netFlow, 0) }} {{ $currency }}</div>
        </div>
    </div>
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Cash flow summary</h3>
        </div>
        <div class="kt-card-content p-0">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Item</th>
                            <th class="min-w-[140px] text-right">Amount ({{ $currency }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-foreground">Opening balance (start of period)</td>
                            <td class="text-right tabular-nums font-medium">{{ number_format($openingBalance ?? 0, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="text-foreground">+ Total income</td>
                            <td class="text-right tabular-nums font-medium text-green-600">+ {{ number_format($totalIncome ?? 0, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="text-foreground">− Total expenses</td>
                            <td class="text-right tabular-nums font-medium text-red-600">− {{ number_format($totalExpenses ?? 0, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-foreground">= Net cash flow</td>
                            <td class="text-right tabular-nums font-bold {{ ($netFlow ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ ($netFlow ?? 0) >= 0 ? '+' : '' }}{{ number_format($netFlow ?? 0, 0) }}</td>
                        </tr>
                        <tr class="border-t-2 border-border">
                            <td class="font-semibold text-foreground">Closing balance (end of period)</td>
                            <td class="text-right tabular-nums font-bold">{{ number_format($closingBalance ?? 0, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Income report (cash-flow style cards) --}}
    @if($report === 'income')
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total Income</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-arrow-up"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">{{ number_format($totalIncome ?? 0, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Sources</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-chart-pie-simple"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ ($bySource ?? collect())->count() }}</div>
            <div class="text-muted-foreground text-sm mt-2">Income sources in period</div>
        </div>
    </div>
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Income by source (share)</h3>
        </div>
        <div class="kt-card-content p-5">
        <div class="space-y-3">
            @foreach($bySource ?? [] as $s)
                @php $pct = ($totalIncome ?? 0) > 0 ? min(100, ($s['total'] / $totalIncome) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $s['name'] }}</span>
                        <span class="tabular-nums">{{ number_format($s['total'], 0) }} {{ $currency }} ({{ $s['percent'] }}%)</span>
                    </div>
                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        </div>
    </div>
    @endif

    {{-- Expense report (cash-flow style cards) --}}
    @if($report === 'expense')
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total Expenses</span>
                <span class="text-red-500 text-lg shrink-0"><i class="ki-filled ki-arrow-down"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-red-600">{{ number_format($totalExpenses ?? 0, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Categories</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-chart-pie-simple"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ ($byCategory ?? collect())->count() }}</div>
            <div class="text-muted-foreground text-sm mt-2">Spending categories in period</div>
        </div>
    </div>
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Expenses by category (progress)</h3>
        </div>
        <div class="kt-card-content p-5">
            <div class="space-y-3">
            @foreach($byCategory ?? [] as $c)
                @php $pct = ($totalExpenses ?? 0) > 0 ? min(100, ($c['total'] / $totalExpenses) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-foreground">{{ $c['name'] }}</span>
                        <span class="tabular-nums">{{ number_format($c['total'], 0) }} {{ $currency }} ({{ $c['percent'] }}%)</span>
                    </div>
                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Transfer report (cash-flow style: KPI row + table card) --}}
    @if($report === 'transfer')
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total transferred</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-arrow-right-left"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($totalTransferred ?? 0, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">In selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Transfers</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-document"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ ($transfers ?? collect())->count() }}</div>
            <div class="text-muted-foreground text-sm mt-2">Transactions in period</div>
        </div>
    </div>
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Transfer history</h3>
        </div>
        <div class="kt-card-content p-0">
            @if(($transfers ?? collect())->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrow-right-left text-4xl mb-2"></i>
                    <p class="text-sm">No transfers in the selected period.</p>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[100px]">Date</th>
                                <th class="min-w-[120px]">From wallet</th>
                                <th class="min-w-[120px]">To wallet</th>
                                <th class="min-w-[100px]">Amount</th>
                                <th class="min-w-[140px]">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $t)
                            <tr>
                                <td class="text-foreground">{{ $t->transfer_date->format('M j, Y') }}</td>
                                <td>{{ $t->fromWallet->name ?? '—' }}</td>
                                <td>{{ $t->toWallet->name ?? '—' }}</td>
                                <td class="tabular-nums font-medium">{{ number_format($t->amount, 0) }} {{ $t->currency_code }}</td>
                                <td class="text-muted-foreground text-sm">{{ $t->description ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Budget report (cash-flow style: KPI row + content card) --}}
    @if($report === 'budget')
    @php
        $budgetCount = count($budgetRows ?? []);
        $budgetTotalPlanned = collect($budgetRows ?? [])->sum('planned');
        $budgetTotalUsed = collect($budgetRows ?? [])->sum('used');
    @endphp
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Budgets</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-chart-pie-simple"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $budgetCount }}</div>
            <div class="text-muted-foreground text-sm mt-2">In selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total planned</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-wallet"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($budgetTotalPlanned, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">{{ number_format($budgetTotalUsed, 0) }} {{ $currency }} used</div>
        </div>
    </div>
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Budgets</h3>
        </div>
        <div class="kt-card-content p-0">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[60px]">S/N</th>
                            <th class="min-w-[200px]">Budget Name</th>
                            <th class="min-w-[140px] text-right">Allocated amount ({{ $currency }})</th>
                            <th class="min-w-[140px] text-right">Spent amount ({{ $currency }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgetRows ?? [] as $sn => $row)
                            <tr>
                                <td class="text-muted-foreground tabular-nums">{{ $sn + 1 }}</td>
                                <td class="font-medium text-foreground">{{ $row['budget']->name }}</td>
                                <td class="text-right tabular-nums">{{ number_format($row['planned'], 0) }}</td>
                                <td class="text-right tabular-nums {{ $row['over'] ? 'text-red-600 font-medium' : '' }}">{{ number_format($row['used'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-muted-foreground text-sm">No budgets in the current period. Create a budget from the Budgets section.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Savings report (cash-flow style: KPI row + content card) --}}
    @if($report === 'savings')
    @php
        $savingsGoalCount = count($savingsRows ?? []);
        $savingsTotalSaved = collect($savingsRows ?? [])->sum('saved');
        $savingsTotalTarget = collect($savingsRows ?? [])->sum('target');
    @endphp
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total saved</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-dollar"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">{{ number_format($savingsTotalSaved, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Across all goals</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Goals</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-safe"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $savingsGoalCount }}</div>
            <div class="text-muted-foreground text-sm mt-2">Target: {{ number_format($savingsTotalTarget, 0) }} {{ $currency }}</div>
        </div>
    </div>
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Savings goals</h3>
        </div>
        <div class="kt-card-content p-5 space-y-6">
            @forelse($savingsRows ?? [] as $row)
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-medium text-foreground">{{ $row['goal']->name }}</p>
                            @if($row['goal']->target_date)
                                <p class="text-xs text-muted-foreground">Target: {{ $row['goal']->target_date->format('M j, Y') }}</p>
                            @endif
                        </div>
                        <div class="text-right text-sm">
                            <span class="tabular-nums text-green-600">{{ number_format($row['saved'], 0) }}</span>
                            <span class="text-muted-foreground"> / {{ number_format($row['target'], 0) }} {{ $currency }}</span>
                        </div>
                    </div>
                    <div class="h-3 rounded-full bg-muted overflow-hidden">
                        <div class="h-full rounded-full bg-green-500 transition-all" style="width: {{ min(100, $row['percent']) }}%"></div>
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">{{ $row['percent'] }}% · {{ number_format($row['remaining'], 0) }} {{ $currency }} remaining</p>
                </div>
            @empty
                <p class="text-muted-foreground text-sm">No savings goals yet. Create one from the Savings section.</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
