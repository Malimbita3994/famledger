@extends('layouts.metronic')

@section('title', 'New Savings Goal')
@section('page_title', 'New Savings Goal')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.savings-goals.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to savings goals
    </a>

    <form action="{{ route('families.savings-goals.store', $family) }}" method="POST">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">New savings goal</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Set a target amount and the wallet where funds will accumulate. You can contribute later via transfers.</p>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="name" class="kt-form-label max-w-56">Name <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Emergency fund" class="kt-input" />
                            @error('name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="description" class="kt-form-label max-w-56">Description</label>
                        <div class="grow">
                            <textarea name="description" id="description" rows="2" class="kt-input" placeholder="Optional">{{ old('description') }}</textarea>
                            @error('description')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="target_amount" class="kt-form-label max-w-56">Target amount <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="target_amount" id="target_amount" value="{{ old('target_amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" />
                            @error('target_amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            @if(isset($mainWallet))
                                <p class="text-xs text-muted-foreground mt-0.5">Main wallet balance: {{ number_format($mainWallet->balance,2) }} {{ $mainWallet->currency_code }}</p>
                            @endif
                            @if(isset($mainBudget))
                                <p class="text-xs text-muted-foreground mt-0.5">Remaining main budget: {{ number_format($mainBudget->remaining_amount,2) }} {{ $mainBudget->currency_code }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="currency_code" class="kt-form-label max-w-56">Currency <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="currency_code" id="currency_code" required class="kt-select">
                                @foreach ($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', $family->currency_code) === $code ? 'selected' : '' }}>{{ $code }} – {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="wallet_id" class="kt-form-label max-w-56">Wallet (where funds accumulate) <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="wallet_id" id="wallet_id" required class="kt-select">
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" {{ old('wallet_id', $mainWallet->id ?? '') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="budget_id" class="kt-form-label max-w-56">Linked budget</label>
                        <div class="grow">
                            <select name="budget_id" id="budget_id" class="kt-select">
                                <option value="">No budget</option>
                                @foreach ($budgets as $b)
                                    <option value="{{ $b->id }}" {{ old('budget_id', $mainBudget->id ?? '') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} ({{ \App\Models\Budget::types()[$b->type] ?? $b->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('budget_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="target_date" class="kt-form-label max-w-56">Target date</label>
                        <div class="grow">
                            <input type="date" name="target_date" id="target_date" value="{{ old('target_date') }}" class="kt-input" />
                            @error('target_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="start_date" class="kt-form-label max-w-56">Start date</label>
                        <div class="grow">
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="kt-input" />
                            @error('start_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="priority" class="kt-form-label max-w-56">Priority</label>
                        <div class="grow">
                            <select name="priority" id="priority" class="kt-select">
                                @foreach (\App\Models\SavingsGoal::priorities() as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.savings-goals.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Create goal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
