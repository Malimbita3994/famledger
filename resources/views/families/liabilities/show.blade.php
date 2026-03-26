@extends('layouts.metronic')

@section('title', $liability->name)
@section('page_title', $liability->name)

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.liabilities.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        {{ __('Back to liabilities') }}
    </a>

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">{{ $liability->name }}</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                {{ ucfirst($liability->type) }} ·
                <span class="capitalize">{{ $liability->status }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('families.liabilities.edit', $liability) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-pencil"></i>
                {{ __('Edit') }}
            </a>
            <form action="{{ route('families.liabilities.destroy', $liability) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="{{ __('Remove this liability?') }}" data-confirm-message="{{ __('This cannot be undone.') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="kt-btn kt-btn-outline text-destructive border-destructive/30">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 max-w-4xl">
        <div class="kt-card p-5 rounded-xl border border-border">
            <h2 class="text-sm font-semibold mb-3">{{ __('Amounts') }}</h2>
            <dl class="grid gap-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-muted-foreground">{{ __('Principal') }}</dt><dd class="font-medium tabular-nums">{{ number_format($liability->principal_amount, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-muted-foreground">{{ __('Outstanding') }}</dt><dd class="font-medium tabular-nums">{{ number_format($liability->outstanding_balance, 2) }}</dd></div>
                @if ($liability->interest_rate !== null)
                    <div class="flex justify-between gap-4"><dt class="text-muted-foreground">{{ __('Interest rate') }}</dt><dd>{{ number_format($liability->interest_rate, 2) }}%</dd></div>
                @endif
                @if ($liability->due_date)
                    <div class="flex justify-between gap-4"><dt class="text-muted-foreground">{{ __('Due date') }}</dt><dd>{{ $liability->due_date->format('M j, Y') }}</dd></div>
                @endif
            </dl>
        </div>
        <div class="kt-card p-5 rounded-xl border border-border">
            <h2 class="text-sm font-semibold mb-3">{{ __('Linked to') }}</h2>
            <ul class="text-sm space-y-1 text-muted-foreground">
                <li>@if($liability->wallet)<span class="text-foreground font-medium">{{ $liability->wallet->name }}</span> ({{ __('wallet') }}) @else — @endif</li>
                <li>@if($liability->project)<span class="text-foreground font-medium">{{ $liability->project->name }}</span> ({{ __('project') }}) @else — @endif</li>
                <li>@if($liability->property)<span class="text-foreground font-medium">{{ $liability->property->name }}</span> ({{ __('property') }}) @else — @endif</li>
                <li>@if($liability->budget)<span class="text-foreground font-medium">{{ $liability->budget->name }}</span> ({{ __('budget') }}) @else — @endif</li>
                <li>@if($liability->savingsGoal)<span class="text-foreground font-medium">{{ $liability->savingsGoal->name }}</span> ({{ __('savings goal') }}) @else — @endif</li>
            </ul>
        </div>
    </div>
</div>
@endsection
