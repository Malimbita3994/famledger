@extends('layouts.metronic')

@section('title', 'Valuation History')
@section('page_title', 'Valuation History')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.properties.assets', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to assets list
    </a>

    <div class="kt-card p-5 lg:p-7.5">
        <div class="mb-5">
            <h1 class="text-lg font-semibold text-mono">Valuation history</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Keep track of valuation updates and market value changes for each property.
            </p>
        </div>

        <p class="text-sm text-muted-foreground">
            Valuation history views will be added here.
        </p>
    </div>
</div>
@endsection

