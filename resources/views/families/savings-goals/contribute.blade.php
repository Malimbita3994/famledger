@extends('layouts.metronic')

@section('title', 'Contribute to ' . $savingsGoal->name)
@section('page_title', 'Contribute')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.savings-goals.show', [$family, $savingsGoal]) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $savingsGoal->name }}
    </a>

    <form action="{{ route('families.savings-goals.contribute.store', [$family, $savingsGoal]) }}" method="POST">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:max-w-xl">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Contribute to {{ $savingsGoal->name }}</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Transfer money from a wallet into the goal wallet. This will record a transfer and count as a contribution.</p>

                    <div class="rounded-lg border border-border bg-muted/30 p-3 text-sm">
                        <p class="text-muted-foreground">Goal wallet (destination): <strong>{{ $savingsGoal->wallet->name }}</strong> ({{ $savingsGoal->wallet->currency_code }})</p>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="from_wallet_id" class="kt-form-label max-w-56">From wallet <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="from_wallet_id" id="from_wallet_id" required class="kt-select">
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" {{ old('from_wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('from_wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="amount" class="kt-form-label max-w-56">Amount <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" />
                            <p class="text-xs text-muted-foreground mt-1">Currency must match goal wallet ({{ $savingsGoal->wallet->currency_code }}).</p>
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.savings-goals.show', [$family, $savingsGoal]) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Transfer &amp; contribute
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
