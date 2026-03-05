@extends('layouts.metronic')

@section('title', 'New Liability')
@section('page_title', 'New Liability')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.liabilities.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to liabilities
    </a>

    <form action="{{ route('families.liabilities.store', $family) }}" method="POST" class="space-y-6">
        @csrf

        <div class="kt-card p-5 lg:p-7.5 max-w-5xl mx-auto">
            <div class="mb-5">
                <h1 class="text-lg font-semibold text-mono">Record liability</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Capture loans, debts or obligations that reduce {{ $family->name }}'s net wealth. Wealth in FamLedger always follows
                    <span class="font-mono text-foreground">Assets = Liabilities + Equity</span>.
                </p>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                {{-- Basic details --}}
                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="grid gap-1.5">
                        <label for="name" class="kt-form-label">Liability name <span class="text-destructive">*</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="e.g. Home mortgage, Car loan"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                            required
                        />
                        @error('name')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-1.5">
                        <label for="type" class="kt-form-label">Type <span class="text-destructive">*</span></label>
                        <input
                            type="text"
                            name="type"
                            id="type"
                            value="{{ old('type') }}"
                            placeholder="e.g. Loan, Mortgage, Credit, Other"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}"
                            required
                        />
                        @error('type')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    <div class="grid gap-1.5">
                        <label for="principal_amount" class="kt-form-label">Principal amount <span class="text-destructive">*</span></label>
                        <input
                            type="number"
                            name="principal_amount"
                            id="principal_amount"
                            value="{{ old('principal_amount') }}"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('principal_amount') ? 'true' : 'false' }}"
                            required
                        />
                        @error('principal_amount')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-1.5">
                        <label for="interest_rate" class="kt-form-label">Interest rate (% per year)</label>
                        <input
                            type="number"
                            name="interest_rate"
                            id="interest_rate"
                            value="{{ old('interest_rate') }}"
                            step="0.01"
                            min="0"
                            placeholder="e.g. 12.5"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('interest_rate') ? 'true' : 'false' }}"
                        />
                        @error('interest_rate')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-1.5">
                        <label for="due_date" class="kt-form-label">Due date</label>
                        <input
                            type="date"
                            name="due_date"
                            id="due_date"
                            value="{{ old('due_date') }}"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('due_date') ? 'true' : 'false' }}"
                        />
                        @error('due_date')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Status --}}
                <div class="grid gap-1.5 max-w-xs">
                    <label for="status" class="kt-form-label">Status <span class="text-destructive">*</span></label>
                    <select
                        name="status"
                        id="status"
                        class="kt-select"
                        aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                        required
                    >
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="overdue" {{ old('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    @error('status')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>

                {{-- Linked to --}}
                <div class="border-t border-border pt-4 mt-2">
                    <h2 class="text-sm font-semibold text-foreground mb-3">Link to asset, wallet or budget (optional)</h2>
                    <p class="text-xs text-muted-foreground mb-3">
                        Linking a liability helps reports explain what the debt belongs to (wallet, project, property, budget or savings goal).
                    </p>

                    <div class="grid gap-4 lg:grid-cols-2">
                        {{-- Wallet --}}
                        <div class="grid gap-1.5">
                            <label for="wallet_id" class="kt-form-label">Wallet</label>
                            <select name="wallet_id" id="wallet_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}" @selected(old('wallet_id') == $wallet->id)>
                                        {{ $wallet->name }} ({{ $wallet->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>

                        {{-- Project --}}
                        <div class="grid gap-1.5">
                            <label for="project_id" class="kt-form-label">Project</label>
                            <select name="project_id" id="project_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>

                        {{-- Property --}}
                        <div class="grid gap-1.5">
                            <label for="property_id" class="kt-form-label">Property</label>
                            <select name="property_id" id="property_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>

                        {{-- Budget --}}
                        <div class="grid gap-1.5">
                            <label for="budget_id" class="kt-form-label">Budget</label>
                            <select name="budget_id" id="budget_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($budgets as $budget)
                                    <option value="{{ $budget->id }}" @selected(old('budget_id') == $budget->id)>
                                        {{ $budget->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('budget_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>

                        {{-- Savings goal --}}
                        <div class="grid gap-1.5">
                            <label for="savings_goal_id" class="kt-form-label">Savings goal</label>
                            <select name="savings_goal_id" id="savings_goal_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($savingsGoals as $goal)
                                    <option value="{{ $goal->id }}" @selected(old('savings_goal_id') == $goal->id)>
                                        {{ $goal->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('savings_goal_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('families.liabilities.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                        <i class="ki-filled ki-check"></i>
                        Save liability
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

