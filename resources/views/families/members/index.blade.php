@extends('layouts.metronic')

@section('title', __('Family members'))
@section('page_title', __('Family members'))

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Family') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Members') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.index') }}">{{ __('Families') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.overview') }}" class="truncate max-w-[12rem] sm:max-w-none">{{ $family->name }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Members') }}</span>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                @if ($canManageMembers)
                    <x-famledger.pulse-button variant="primary" :href="route('families.members.create')">
                        <i class="ki-filled ki-plus"></i>
                        {{ __('Add member') }}
                    </x-famledger.pulse-button>
                @endif
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
                <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
                <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @include('families.members.partials.list')
    </div>
</div>
@endsection
