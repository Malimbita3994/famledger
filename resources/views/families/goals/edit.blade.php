@extends('layouts.metronic')

@section('title', __('Edit vision goal'))
@section('page_title', __('Edit vision goal'))

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Vision Board') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Edit goal') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.goals.index') }}">{{ __('Vision Board') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.goals.show', $goal) }}" class="truncate max-w-[12rem] sm:max-w-none">{{ $goal->title }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Edit') }}</span>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                <a href="{{ route('families.goals.show', $goal) }}" class="famledger-timeline-create-back">
                    <i class="ki-filled ki-arrow-left"></i>
                    {{ __('Back to details') }}
                </a>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        <div class="famledger-timeline-create-page famledger-create-form-page--wide">
            <div class="famledger-timeline-create-card">
                <div class="famledger-timeline-create-hero">
                    <div class="famledger-timeline-create-hero-inner">
                        <div class="famledger-timeline-create-hero-icon" aria-hidden="true">
                            <i class="ki-filled ki-compass"></i>
                        </div>
                        <div class="famledger-timeline-create-hero-text">
                            <h2 class="famledger-timeline-create-hero-title">{{ __('Update this vision') }}</h2>
                        </div>
                    </div>
                </div>

                <form action="{{ route('families.goals.update', $goal) }}" method="POST" class="famledger-timeline-create-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="famledger-timeline-create-fields">
                        <div class="famledger-timeline-create-field famledger-timeline-create-field--title famledger-timeline-create-field--enhanced @error('title') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="goal-title">{{ __('Goal title') }}</label>
                            <div class="famledger-timeline-create-control-wrap">
                                <input
                                    id="goal-title"
                                    type="text"
                                    name="title"
                                    class="form-control form-control-solid famledger-timeline-create-input-filled @error('title') is-invalid @enderror"
                                    required
                                    maxlength="255"
                                    autocomplete="off"
                                    value="{{ old('title', $goal->title) }}"
                                >
                            </div>
                            @error('title') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--image famledger-timeline-create-field--enhanced @error('image') famledger-timeline-create-field--invalid @enderror @error('image_url') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="goal-image-file">{{ __('Image') }}</label>
                            <p class="text-xs text-muted-foreground mb-2">{{ __('File upload: max 4 MB (JPEG, PNG, GIF, WebP). Or paste an image URL below.') }}</p>
                            @if($goal->image_url)
                                <p class="text-xs text-muted-foreground mb-2">{{ __('Current image') }}</p>
                                <div class="mb-3 rounded-lg overflow-hidden border border-border max-w-md max-h-40">
                                    <img src="{{ $goal->resolved_image_url }}" alt="" class="w-full h-full object-cover max-h-40" onerror="this.closest('div').style.display='none'">
                                </div>
                            @endif
                            <div class="famledger-timeline-create-control-wrap famledger-timeline-create-control-wrap--file">
                                <input
                                    id="goal-image-file"
                                    type="file"
                                    name="image"
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                    class="form-control form-control-solid famledger-timeline-create-input-file @error('image') is-invalid @enderror"
                                >
                            </div>
                            @error('image') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                            <div class="famledger-timeline-create-control-wrap mt-2">
                                <input
                                    id="goal-image-url"
                                    type="url"
                                    name="image_url"
                                    inputmode="url"
                                    autocomplete="url"
                                    class="form-control form-control-solid famledger-timeline-create-input-filled @error('image_url') is-invalid @enderror"
                                    value="{{ old('image_url', $goal->image_url) }}"
                                >
                            </div>
                            @error('image_url') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-row">
                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="goal-target-date">{{ __('Target date') }}</label>
                                <input id="goal-target-date" type="date" name="target_date" class="form-control form-control-solid famledger-timeline-create-input @error('target_date') is-invalid @enderror" value="{{ old('target_date', $goal->target_date?->format('Y-m-d')) }}">
                                @error('target_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="goal-category">{{ __('Category') }}</label>
                                <select id="goal-category" name="category" class="kt-select w-full famledger-timeline-create-select">
                                    <option value=""></option>
                                    <option value="financial" {{ old('category', $goal->category) === 'financial' ? 'selected' : '' }}>{{ __('Financial') }}</option>
                                    <option value="home" {{ old('category', $goal->category) === 'home' ? 'selected' : '' }}>{{ __('Home') }}</option>
                                    <option value="travel" {{ old('category', $goal->category) === 'travel' ? 'selected' : '' }}>{{ __('Travel') }}</option>
                                    <option value="health" {{ old('category', $goal->category) === 'health' ? 'selected' : '' }}>{{ __('Health') }}</option>
                                    <option value="education" {{ old('category', $goal->category) === 'education' ? 'selected' : '' }}>{{ __('Education') }}</option>
                                    <option value="family" {{ old('category', $goal->category) === 'family' ? 'selected' : '' }}>{{ __('Family') }}</option>
                                    <option value="other" {{ old('category', $goal->category) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="famledger-timeline-create-row">
                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="goal-status">{{ __('Status') }}</label>
                                <select id="goal-status" name="status" class="kt-select w-full famledger-timeline-create-select @error('status') is-invalid @enderror" required>
                                    <option value="draft" {{ old('status', $goal->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                    <option value="active" {{ old('status', $goal->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="completed" {{ old('status', $goal->status) === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="famledger-timeline-create-field">
                                <label class="famledger-timeline-create-label" for="goal-progress">{{ __('Progress') }} (%)</label>
                                <input
                                    id="goal-progress"
                                    type="number"
                                    name="progress"
                                    class="form-control form-control-solid famledger-timeline-create-input @error('progress') is-invalid @enderror"
                                    min="0"
                                    max="100"
                                    step="1"
                                    inputmode="numeric"
                                    value="{{ old('progress', (string) $goal->progress) }}"
                                >
                                @error('progress') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--description famledger-timeline-create-field--enhanced @error('description') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="goal-description">{{ __('Description') }}</label>
                            <div class="famledger-timeline-create-control-wrap">
                                <textarea
                                    id="goal-description"
                                    name="description"
                                    class="form-control form-control-solid famledger-timeline-create-textarea"
                                    rows="6"
                                    spellcheck="true"
                                    autocomplete="off"
                                >{{ old('description', $goal->description) }}</textarea>
                            </div>
                            @error('description') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>

                        <div class="famledger-timeline-create-field famledger-timeline-create-field--steps famledger-timeline-create-field--enhanced @error('step_lines') famledger-timeline-create-field--invalid @enderror">
                            <label class="famledger-timeline-create-label" for="goal-step-lines">{{ __('Action steps') }}</label>
                            <div class="famledger-timeline-create-control-wrap">
                                <textarea
                                    id="goal-step-lines"
                                    name="step_lines"
                                    class="form-control form-control-solid famledger-timeline-create-textarea"
                                    rows="5"
                                    spellcheck="true"
                                    autocomplete="off"
                                >{{ old('step_lines', $stepLines) }}</textarea>
                            </div>
                            @error('step_lines') <div class="famledger-timeline-create-error" role="alert">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="famledger-timeline-create-actions">
                        <a href="{{ route('families.goals.show', $goal) }}" class="famledger-timeline-create-btn famledger-timeline-create-btn--secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="famledger-timeline-create-btn famledger-timeline-create-btn--primary">{{ __('Save changes') }}</button>
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
