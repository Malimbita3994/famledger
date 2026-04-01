@extends('layouts.metronic')

@section('title', __('Add Family Memory'))
@section('page_title', __('Add Family Memory'))

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Timeline') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('New memory') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.timeline.index') }}">{{ __('Timeline') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('New') }}</span>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                <a href="{{ route('families.timeline.index') }}" class="famledger-timeline-create-back">
                    <i class="ki-filled ki-arrow-left"></i>
                    {{ __('Back to timeline') }}
                </a>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        <div class="famledger-timeline-create-page">
            <div class="famledger-timeline-create-card">
                <div class="famledger-timeline-create-hero">
                    <div class="famledger-timeline-create-hero-inner">
                        <div class="famledger-timeline-create-hero-icon" aria-hidden="true">
                            <i class="ki-filled ki-calendar-add"></i>
                        </div>
                        <div class="famledger-timeline-create-hero-text">
                            <h2 class="famledger-timeline-create-hero-title">{{ __('Record a moment') }}</h2>
                        </div>
                    </div>
                </div>

                <form action="{{ route('families.timeline.store') }}" method="POST" class="famledger-timeline-create-form">
                    @csrf

                    <div class="famledger-timeline-create-fields">
                        <div class="famledger-timeline-create-field famledger-timeline-create-field--title famledger-timeline-create-field--enhanced @error('title') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="memory-title">{{ __('Title') }}</label>
                            <div class="famledger-timeline-create-control-wrap">
                                <input
                                    id="memory-title"
                                    type="text"
                                    name="title"
                                    class="form-control form-control-solid famledger-timeline-create-input-filled @error('title') is-invalid @enderror"
                                    required
                                    maxlength="255"
                                    autocomplete="off"
                                    value="{{ old('title') }}"
                                >
                            </div>
                            @error('title') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-row">
                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="memory-date">{{ __('Date') }}</label>
                                <input id="memory-date" type="date" name="date" class="form-control form-control-solid famledger-timeline-create-input @error('date') is-invalid @enderror" required value="{{ old('date', date('Y-m-d')) }}">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="memory-category">{{ __('Category') }}</label>
                                <select id="memory-category" name="category" class="kt-select w-full famledger-timeline-create-select">
                                    <option value=""></option>
                                    <option value="birthday" {{ old('category') === 'birthday' ? 'selected' : '' }}>{{ __('Birthday') }}</option>
                                    <option value="graduation" {{ old('category') === 'graduation' ? 'selected' : '' }}>{{ __('Graduation') }}</option>
                                    <option value="wedding" {{ old('category') === 'wedding' ? 'selected' : '' }}>{{ __('Wedding') }}</option>
                                    <option value="anniversary" {{ old('category') === 'anniversary' ? 'selected' : '' }}>{{ __('Anniversary') }}</option>
                                    <option value="achievement" {{ old('category') === 'achievement' ? 'selected' : '' }}>{{ __('Achievement') }}</option>
                                    <option value="travel" {{ old('category') === 'travel' ? 'selected' : '' }}>{{ __('Travel') }}</option>
                                    <option value="purchase" {{ old('category') === 'purchase' ? 'selected' : '' }}>{{ __('Purchase') }}</option>
                                    <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--description famledger-timeline-create-field--enhanced @error('description') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="memory-description">{{ __('Description') }}</label>
                            <div class="famledger-timeline-create-control-wrap">
                                <textarea
                                    id="memory-description"
                                    name="description"
                                    class="form-control form-control-solid famledger-timeline-create-textarea"
                                    rows="6"
                                    spellcheck="true"
                                    autocomplete="off"
                                >{{ old('description') }}</textarea>
                            </div>
                            @error('description') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--url famledger-timeline-create-field--enhanced @error('media_url') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="memory-media">{{ __('Image URL') }}</label>
                            <div class="famledger-timeline-create-control-wrap">
                                <input
                                    id="memory-media"
                                    type="url"
                                    name="media_url"
                                    inputmode="url"
                                    autocomplete="url"
                                    class="form-control form-control-solid famledger-timeline-create-input-filled @error('media_url') is-invalid @enderror"
                                    value="{{ old('media_url') }}"
                                >
                            </div>
                            @error('media_url') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="famledger-timeline-create-actions">
                        <a href="{{ route('families.timeline.index') }}" class="famledger-timeline-create-btn famledger-timeline-create-btn--secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="famledger-timeline-create-btn famledger-timeline-create-btn--primary">{{ __('Save memory') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    @include('partials.famledger-create-form-styles')
@endpush
