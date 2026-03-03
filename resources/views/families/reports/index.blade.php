@extends('layouts.metronic')

@section('title', 'General Report')
@section('page_title', 'General Report')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">General Report</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Overview of family finances, budgets, savings, and projects. Filter by date and wallet.</p>
        </div>
    </div>

    {{-- Filter report card (standard) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Filter report</h3>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.index', $family) }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">From</label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">To</label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Wallet</label>
                    <select name="wallet_id" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[160px]">
                        <option value="">All wallets</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ $wallet && $wallet->id === $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                <a href="{{ route('families.reports.index', $family) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- Summary KPI cards (standard 4-col) --}}
    <div class="report-kpi-grid">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total Income</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-arrow-up"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">{{ $formatAmount($totalIncome) }}</div>
            <div class="text-muted-foreground text-sm mt-2">Selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total Expenses</span>
                <span class="text-red-500 text-lg shrink-0"><i class="ki-filled ki-arrow-down"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-red-600">{{ $formatAmount($totalExpenses) }}</div>
            <div class="text-muted-foreground text-sm mt-2">Selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Net (Savings)</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-safe"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-blue-600">{{ $formatAmount($savings) }}</div>
            <div class="text-muted-foreground text-sm mt-2">Income − Expenses</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Active Projects</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-briefcase"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $activeProjects }}</div>
            <div class="text-muted-foreground text-sm mt-2">Budget used: {{ $budgetUsedPercent }}%</div>
        </div>
    </div>

    {{-- Family snapshot tables: basic info, members, projects, finances, budgets, savings --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-8">
        {{-- Family basic information --}}
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Family basic information</h3>
            </div>
            <div class="kt-card-content p-5">
                <dl class="space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Name</dt>
                        <dd class="text-foreground font-medium">{{ $family->name }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Currency</dt>
                        <dd class="text-foreground">{{ $family->currency_code }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Timezone</dt>
                        <dd class="text-foreground">{{ $family->timezone }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Country</dt>
                        <dd class="text-foreground">{{ $family->country ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Members</dt>
                        <dd class="text-foreground">{{ $family->familyMembers->count() }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Status</dt>
                        <dd class="text-foreground capitalize">{{ $family->status }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <dt class="text-muted-foreground">Created</dt>
                        <dd class="text-foreground">{{ optional($family->created_at)->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Family members table --}}
        <div class="kt-card kt-card-grid rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header flex-wrap gap-2">
                <h3 class="kt-card-title text-sm">Family members ({{ $family->familyMembers->count() }})</h3>
            </div>
            <div class="kt-card-content p-0">
                @if ($family->familyMembers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center text-sm text-muted-foreground">
                        No members have been added to this family yet.
                    </div>
                @else
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table table-auto kt-table-border">
                            <thead>
                                <tr>
                                    <th class="min-w-[220px]">Member</th>
                                    <th class="min-w-[120px]">Role</th>
                                    <th class="min-w-[120px]">Status</th>
                                    <th class="min-w-[100px]">Primary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($family->familyMembers as $member)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">
                                                {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                                            </span>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-sm font-medium text-mono">{{ $member->member_name ?? $member->user->name ?? 'Unknown' }}</span>
                                                <span class="text-sm text-secondary-foreground font-normal truncate">{{ $member->user->email ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-foreground font-normal">{{ $member->role->name ?? '—' }}</td>
                                    <td class="text-foreground font-normal capitalize">{{ $member->status ?? 'active' }}</td>
                                    <td class="text-foreground font-normal">
                                        @if ($member->is_primary)
                                            <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">Primary</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Projects summary table --}}
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Projects snapshot</h3>
            </div>
            <div class="kt-card-content p-0">
                @php $projects = $family->projects ?? collect(); @endphp
                @if ($projects->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center text-sm text-muted-foreground">
                        No projects have been created for this family yet.
                    </div>
                @else
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table table-auto kt-table-border">
                            <thead>
                                <tr>
                                    <th class="min-w-[220px]">Project</th>
                                    <th class="min-w-[120px]">Status</th>
                                    <th class="min-w-[140px]" style="text-align:right;">Budget</th>
                                    <th class="min-w-[140px]" style="text-align:right;">Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projects as $project)
                                @php
                                    $planned = (float) ($project->planned_budget ?? $project->budget_amount ?? 0);
                                    $spent   = (float) ($project->expenses_sum_amount ?? $project->expenses()->sum('amount') ?? 0);
                                @endphp
                                <tr>
                                    <td class="text-sm font-medium text-mono">{{ $project->name }}</td>
                                    <td class="text-sm capitalize text-foreground">{{ $project->status ?? 'active' }}</td>
                                    <td class="text-sm text-foreground text-right tabular-nums">
                                        {{ $planned > 0 ? number_format($planned, 0) . ' ' . $family->currency_code : '—' }}
                                    </td>
                                    <td class="text-sm text-foreground text-right tabular-nums">
                                        {{ $spent > 0 ? number_format($spent, 0) . ' ' . $family->currency_code : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Financial summary table --}}
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Financial summary</h3>
            </div>
            <div class="kt-card-content p-5">
                <table class="kt-table table-auto kt-table-border text-sm w-full">
                    <tbody>
                        <tr>
                            <td class="py-2 text-muted-foreground">Total income (selected period)</td>
                            <td class="py-2 text-right font-medium text-green-600">{{ $formatAmount($totalIncome) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">Total expenses (selected period)</td>
                            <td class="py-2 text-right font-medium text-red-600">{{ $formatAmount($totalExpenses) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">Net savings (income − expenses)</td>
                            <td class="py-2 text-right font-medium text-blue-600">{{ $formatAmount($savings) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">Active projects</td>
                            <td class="py-2 text-right font-medium">{{ $activeProjects }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Budgets snapshot table --}}
        @php
            /** @var array<int,array{budget:mixed,planned:float,used:float,over:bool}>|null $budgetRows */
            $budgetRows = $budgetRows ?? null;
            $currencyForBudget = $currency ?? $family->currency_code;
        @endphp
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Budgets snapshot</h3>
            </div>
            <div class="kt-card-content p-0">
                @php
                    $hasBudgetRows = !empty($budgetRows);
                    $hasBudgetTotals = isset($totalBudget) && isset($totalBudgetUsed) && $totalBudget > 0;
                @endphp
                @if ($hasBudgetRows || $hasBudgetTotals)
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table table-auto kt-table-border text-sm w-full">
                            <thead>
                                <tr>
                                    <th class="min-w-[60px]">S/N</th>
                                    <th class="min-w-[200px]">Budget</th>
                                    <th class="min-w-[140px] text-right">Planned ({{ $currencyForBudget }})</th>
                                    <th class="min-w-[140px] text-right">Used ({{ $currencyForBudget }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rowsToRender = $hasBudgetRows
                                        ? $budgetRows
                                        : [[
                                            'budget'  => (object) ['name' => 'All budgets'],
                                            'planned' => $totalBudget ?? 0,
                                            'used'    => $totalBudgetUsed ?? 0,
                                            'over'    => ($totalBudgetUsed ?? 0) > ($totalBudget ?? 0),
                                        ]];
                                @endphp
                                @foreach($rowsToRender as $sn => $row)
                                    <tr>
                                        <td class="text-muted-foreground tabular-nums">{{ $sn + 1 }}</td>
                                        <td class="font-medium text-foreground">{{ $row['budget']->name ?? '—' }}</td>
                                        <td class="text-right tabular-nums">{{ number_format($row['planned'] ?? 0, 0) }}</td>
                                        <td class="text-right tabular-nums {{ !empty($row['over']) ? 'text-red-600 font-medium' : '' }}">
                                            {{ number_format($row['used'] ?? 0, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 px-4 text-sm text-muted-foreground">
                        No budgets snapshot is available on this page. View full details in the
                        <a href="{{ route('families.reports.budget-vs-actual', $family) }}" class="text-primary hover:underline">
                            Budget vs Actual report
                        </a>.
                    </div>
                @endif
            </div>
        </div>

        {{-- Savings goals snapshot table --}}
        @php
            /** @var array<int,array{goal:mixed,saved:float,target:float,percent:float,remaining:float}>|null $savingsRows */
            $savingsRows = $savingsRows ?? null;
            $currencyForSavings = $currency ?? $family->currency_code;
        @endphp
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Savings snapshot</h3>
            </div>
            <div class="kt-card-content p-0">
                @if (!empty($savingsRows))
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table table-auto kt-table-border text-sm w-full">
                            <thead>
                                <tr>
                                    <th class="min-w-[200px]">Goal</th>
                                    <th class="min-w-[120px] text-right">Saved ({{ $currencyForSavings }})</th>
                                    <th class="min-w-[120px] text-right">Target ({{ $currencyForSavings }})</th>
                                    <th class="min-w-[100px] text-right">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($savingsRows as $row)
                                    <tr>
                                        <td class="font-medium text-foreground">{{ $row['goal']->name ?? '—' }}</td>
                                        <td class="text-right tabular-nums text-green-600">{{ number_format($row['saved'] ?? 0, 0) }}</td>
                                        <td class="text-right tabular-nums">{{ number_format($row['target'] ?? 0, 0) }}</td>
                                        <td class="text-right tabular-nums">{{ $row['percent'] ?? 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 px-4 text-sm text-muted-foreground">
                        No savings snapshot is available on this page. View full details in the
                        <a href="{{ route('families.reports.savings', $family) }}" class="text-primary hover:underline">
                            Savings report
                        </a>.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick links (standard card) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header border-b border-border">
                <h3 class="kt-card-title text-sm">Financial Reports</h3>
                <p class="text-sm text-muted-foreground mt-0.5">Income, expenses, cash flow, budgets, savings, and wallet statement</p>
            </div>
            <div class="kt-card-content p-5 space-y-2">
                <a href="{{ route('families.reports.wallet-statement', $family) }}?from={{ $dateFrom }}&to={{ $dateTo }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Wallet Statement</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
                <a href="{{ route('families.reports.expense', $family) }}?from={{ $dateFrom }}&to={{ $dateTo }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Expense Report</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
                <a href="{{ route('families.reports.income', $family) }}?from={{ $dateFrom }}&to={{ $dateTo }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Income Report</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
                <a href="{{ route('families.reports.cash-flow', $family) }}?from={{ $dateFrom }}&to={{ $dateTo }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Cash Flow</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
                <a href="{{ route('families.reports.budget-vs-actual', $family) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Budget vs Actual</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
                <a href="{{ route('families.reports.savings', $family) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Savings Report</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
            </div>
        </div>
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="kt-card-header border-b border-border">
                <h3 class="kt-card-title text-sm">Project Reports</h3>
                <p class="text-sm text-muted-foreground mt-0.5">Project summary, funding, expenses, and timeline</p>
            </div>
            <div class="kt-card-content p-5 space-y-2">
                <a href="{{ route('families.reports.project-summary', $family) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-muted/50 transition-colors">
                    <span class="text-sm font-medium">Project Summary</span>
                    <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                </a>
                <div class="py-2 px-3 rounded-lg text-sm text-muted-foreground">
                    Funding Report — <span class="text-xs">(coming soon)</span>
                </div>
                <div class="py-2 px-3 rounded-lg text-sm text-muted-foreground">
                    Project Expenses — <span class="text-xs">(coming soon)</span>
                </div>
                <div class="py-2 px-3 rounded-lg text-sm text-muted-foreground">
                    Project Timeline — <span class="text-xs">(coming soon)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
