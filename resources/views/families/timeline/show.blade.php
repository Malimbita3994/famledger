@extends('layouts.metronic')

@section('title', __('View Family Memory'))
@section('page_title', __('View Family Memory'))

@section('content')
@php
    $categoryLabels = [
        'birthday' => __('Birthday'),
        'graduation' => __('Graduation'),
        'wedding' => __('Wedding'),
        'anniversary' => __('Anniversary'),
        'achievement' => __('Achievement'),
        'travel' => __('Travel'),
        'purchase' => __('Purchase'),
        'other' => __('Other'),
    ];
@endphp
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Timeline') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Memory details') }}</h1>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                <a href="{{ route('families.timeline.index') }}" class="famledger-timeline-create-back">
                    <i class="ki-filled ki-arrow-left"></i>
                    {{ __('Back to timeline') }}
                </a>
                @if($milestone->user_id === auth()->id())
                    <a href="{{ route('families.timeline.edit', $milestone) }}" class="famledger-timeline-create-btn famledger-timeline-create-btn--primary">
                        <i class="ki-filled ki-pencil"></i>
                        {{ __('Edit memory') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        <div class="famledger-timeline-create-page">
            <div class="famledger-timeline-create-card">
                <div class="famledger-timeline-create-hero">
                    <div class="famledger-timeline-create-hero-inner">
                        <div class="famledger-timeline-create-hero-icon" aria-hidden="true">
                            <i class="ki-filled ki-calendar"></i>
                        </div>
                        <div class="famledger-timeline-create-hero-text">
                            <h2 class="famledger-timeline-create-hero-title">{{ $milestone->title }}</h2>
                            <p class="famledger-timeline-create-hero-sub">
                                {{ $milestone->date ? $milestone->date->format('l, F j, Y') : __('No date') }}
                                @if($milestone->category && isset($categoryLabels[$milestone->category]))
                                    · {{ $categoryLabels[$milestone->category] }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="famledger-timeline-create-form">
                    @if($milestone->description)
                        <div class="famledger-timeline-create-field">
                            <label class="famledger-timeline-create-label">{{ __('Description') }}</label>
                            <p class="text-sm leading-7 text-slate-700 mb-0">{{ $milestone->description }}</p>
                        </div>
                    @endif

                    @if($milestone->media_url)
                        <div class="famledger-timeline-create-field">
                            <label class="famledger-timeline-create-label">{{ __('Image') }}</label>
                            <div class="famledger-timeline-media">
                                <img src="{{ $milestone->media_url }}" alt="" loading="lazy" decoding="async" />
                            </div>
                        </div>
                    @endif

                    <div class="famledger-timeline-create-actions">
                        <a href="{{ route('families.timeline.index') }}" class="famledger-timeline-create-btn famledger-timeline-create-btn--secondary">{{ __('Back') }}</a>
                        @if($milestone->user_id === auth()->id())
                            <a href="{{ route('families.timeline.edit', $milestone) }}" class="famledger-timeline-create-btn famledger-timeline-create-btn--primary">{{ __('Edit memory') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    @include('partials.famledger-create-form-styles')
    <style>
        .famledger-timeline-media {
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .famledger-timeline-media img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 22rem;
            object-fit: cover;
        }
    </style>
@endpush
