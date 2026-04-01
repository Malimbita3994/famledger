@extends('layouts.metronic')

@section('title', __('Edit Family Memory'))
@section('page_title', __('Edit Family Memory'))

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Timeline') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Edit memory') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.timeline.index') }}">{{ __('Timeline') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Edit') }}</span>
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
                            <i class="ki-filled ki-notepad-edit"></i>
                        </div>
                        <div class="famledger-timeline-create-hero-text">
                            <h2 class="famledger-timeline-create-hero-title">{{ __('Update this moment') }}</h2>
                            <p class="famledger-timeline-create-hero-sub">{{ __('Keep the story accurate as your family memory evolves.') }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('families.timeline.update', $milestone) }}" method="POST" class="famledger-timeline-create-form">
                    @csrf
                    @method('PUT')

                    <div class="famledger-timeline-create-fields">
                        <div class="famledger-timeline-create-field famledger-timeline-create-field--title famledger-timeline-create-field--enhanced @error('title') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="memory-title">{{ __('Title') }}</label>
                            <p class="famledger-timeline-create-field-lead" id="memory-title-hint">{{ __('A short, clear name so everyone recognizes this memory on the timeline.') }}</p>
                            <div class="famledger-timeline-create-control-wrap">
                                <input
                                    id="memory-title"
                                    type="text"
                                    name="title"
                                    class="form-control form-control-solid famledger-timeline-create-input-filled @error('title') is-invalid @enderror"
                                    placeholder="{{ __('e.g. First day of school · Summer in Zanzibar · Keys to our house') }}"
                                    required
                                    maxlength="255"
                                    autocomplete="off"
                                    aria-describedby="memory-title-hint"
                                    value="{{ old('title', $milestone->title) }}"
                                >
                            </div>
                            @error('title') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-row">
                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="memory-date">{{ __('Date') }}</label>
                                <input id="memory-date" type="date" name="date" class="form-control form-control-solid famledger-timeline-create-input @error('date') is-invalid @enderror" required value="{{ old('date', optional($milestone->date)->format('Y-m-d')) }}">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="memory-category">{{ __('Category') }}</label>
                                <select id="memory-category" name="category" class="kt-select w-full famledger-timeline-create-select">
                                    <option value="">{{ __('Optional — choose a category') }}</option>
                                    <option value="birthday" {{ old('category', $milestone->category) === 'birthday' ? 'selected' : '' }}>{{ __('Birthday') }}</option>
                                    <option value="graduation" {{ old('category', $milestone->category) === 'graduation' ? 'selected' : '' }}>{{ __('Graduation') }}</option>
                                    <option value="wedding" {{ old('category', $milestone->category) === 'wedding' ? 'selected' : '' }}>{{ __('Wedding') }}</option>
                                    <option value="anniversary" {{ old('category', $milestone->category) === 'anniversary' ? 'selected' : '' }}>{{ __('Anniversary') }}</option>
                                    <option value="achievement" {{ old('category', $milestone->category) === 'achievement' ? 'selected' : '' }}>{{ __('Achievement') }}</option>
                                    <option value="travel" {{ old('category', $milestone->category) === 'travel' ? 'selected' : '' }}>{{ __('Travel') }}</option>
                                    <option value="purchase" {{ old('category', $milestone->category) === 'purchase' ? 'selected' : '' }}>{{ __('Purchase') }}</option>
                                    <option value="other" {{ old('category', $milestone->category) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--description famledger-timeline-create-field--enhanced @error('description') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="memory-description">{{ __('Description') }} <span class="optional">({{ __('optional') }})</span></label>
                            <div class="famledger-timeline-create-control-wrap">
                                <textarea
                                    id="memory-description"
                                    name="description"
                                    class="form-control form-control-solid famledger-timeline-create-textarea"
                                    rows="6"
                                    spellcheck="true"
                                    autocomplete="off"
                                    placeholder="{{ __('Write a few sentences… (e.g. where you were, what made it special)') }}"
                                >{{ old('description', $milestone->description) }}</textarea>
                            </div>
                            @error('description') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--url famledger-timeline-create-field--enhanced @error('media_url') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="memory-media">{{ __('Image URL') }} <span class="optional">({{ __('optional') }})</span></label>
                            <div class="famledger-timeline-create-control-wrap">
                                <input
                                    id="memory-media"
                                    type="url"
                                    name="media_url"
                                    inputmode="url"
                                    autocomplete="url"
                                    class="form-control form-control-solid famledger-timeline-create-input-filled @error('media_url') is-invalid @enderror"
                                    placeholder="{{ __('https://example.com/album/photo.jpg') }}"
                                    aria-describedby="memory-media-hint"
                                    value="{{ old('media_url', $milestone->media_url) }}"
                                >
                            </div>
                            <p class="famledger-timeline-create-hint" id="memory-media-hint">{{ __('Public or shareable links work best; login-only pages may not show for others.') }}</p>
                            @error('media_url') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="famledger-timeline-create-actions">
                        <a href="{{ route('families.timeline.index') }}" class="famledger-timeline-create-btn famledger-timeline-create-btn--secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="famledger-timeline-create-btn famledger-timeline-create-btn--primary">{{ __('Update memory') }}</button>
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
