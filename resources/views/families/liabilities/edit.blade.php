@extends('layouts.metronic')

@section('title', __('Edit liability'))
@section('page_title', __('Edit liability'))

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.liabilities.show', $liability) }}">
        {{ __('Back to liability') }}
    </x-fin-back-link>

    <form action="{{ route('families.liabilities.update', $liability) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="kt-card p-5 lg:p-7.5 max-w-5xl mx-auto">
            <div class="mb-5">
                <h1 class="text-lg font-semibold text-mono">{{ __('Edit liability') }}</h1>
                <p class="text-sm text-muted-foreground mt-0.5">{{ $liability->name }}</p>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <div class="famledger-form-row-3">
                    <div class="grid gap-1.5 famledger-form-field">
                        <label for="name" class="kt-form-label">{{ __('Liability name') }} <span class="text-destructive">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $liability->name) }}" class="kt-input" required />
                        @error('name')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-1.5 famledger-form-field">
                        <label for="type" class="kt-form-label">{{ __('Type') }} <span class="text-destructive">*</span></label>
                        <input type="text" name="type" id="type" value="{{ old('type', $liability->type) }}" class="kt-input" required />
                        @error('type')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-1.5 famledger-form-field">
                        <label for="status" class="kt-form-label">{{ __('Status') }} <span class="text-destructive">*</span></label>
                        <select name="status" id="status" class="kt-select" required>
                            @foreach (['active' => __('Active'), 'overdue' => __('Overdue'), 'closed' => __('Closed')] as $val => $label)
                                <option value="{{ $val }}" @selected(old('status', $liability->status) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="famledger-form-row-3">
                    <div class="grid gap-1.5 famledger-form-field">
                        <label for="principal_amount" class="kt-form-label">{{ __('Principal amount') }} <span class="text-destructive">*</span></label>
                        <input type="number" name="principal_amount" id="principal_amount" value="{{ old('principal_amount', $liability->principal_amount) }}" step="0.01" min="0" class="kt-input" required />
                        @error('principal_amount')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-1.5 famledger-form-field">
                        <label for="interest_rate" class="kt-form-label">{{ __('Interest rate (% per year)') }}</label>
                        <input type="number" name="interest_rate" id="interest_rate" value="{{ old('interest_rate', $liability->interest_rate) }}" step="0.01" min="0" class="kt-input" />
                        @error('interest_rate')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-1.5 famledger-form-field">
                        <label for="due_date" class="kt-form-label">{{ __('Due date') }}</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', optional($liability->due_date)->format('Y-m-d')) }}" class="kt-input" />
                        @error('due_date')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-t border-border pt-4 mt-2">
                    <h2 class="text-sm font-semibold text-foreground mb-3">{{ __('Link to asset, wallet or budget (optional)') }}</h2>
                    <div class="famledger-form-row-3">
                        <div class="grid gap-1.5 famledger-form-field">
                            <label for="wallet_id" class="kt-form-label">{{ __('Wallet') }}</label>
                            <select name="wallet_id" id="wallet_id" class="kt-select">
                                <option value="">— {{ __('None') }} —</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}" @selected(old('wallet_id', $liability->wallet_id) == $wallet->id)>{{ $wallet->name }} ({{ $wallet->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-1.5 famledger-form-field">
                            <label for="project_id" class="kt-form-label">{{ __('Project') }}</label>
                            <select name="project_id" id="project_id" class="kt-select">
                                <option value="">— {{ __('None') }} —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id', $liability->project_id) == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-1.5 famledger-form-field">
                            <label for="property_id" class="kt-form-label">{{ __('Property') }}</label>
                            <select name="property_id" id="property_id" class="kt-select">
                                <option value="">— {{ __('None') }} —</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" @selected(old('property_id', $liability->property_id) == $property->id)>{{ $property->name }}</option>
                                @endforeach
                            </select>
                            @error('property_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="famledger-form-row-3 mt-5">
                        <div class="grid gap-1.5 famledger-form-field">
                            <label for="budget_id" class="kt-form-label">{{ __('Budget') }}</label>
                            <select name="budget_id" id="budget_id" class="kt-select">
                                <option value="">— {{ __('None') }} —</option>
                                @foreach ($budgets as $budget)
                                    <option value="{{ $budget->id }}" @selected(old('budget_id', $liability->budget_id) == $budget->id)>{{ $budget->name }}</option>
                                @endforeach
                            </select>
                            @error('budget_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-1.5 famledger-form-field">
                            <label for="savings_goal_id" class="kt-form-label">{{ __('Savings goal') }}</label>
                            <select name="savings_goal_id" id="savings_goal_id" class="kt-select">
                                <option value="">— {{ __('None') }} —</option>
                                @foreach ($savingsGoals as $goal)
                                    <option value="{{ $goal->id }}" @selected(old('savings_goal_id', $liability->savings_goal_id) == $goal->id)>{{ $goal->name }}</option>
                                @endforeach
                            </select>
                            @error('savings_goal_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('families.liabilities.show', $liability) }}" class="kt-btn kt-btn-outline">{{ __('Cancel') }}</a>
                    <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                        <i class="ki-filled ki-check"></i>
                        {{ __('Save changes') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
