@extends('layouts.metronic')

@section('title', 'Edit Project')
@section('page_title', 'Edit Project')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.projects.show', [$family, $project]) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to project
    </a>

    <form action="{{ route('families.projects.update', [$family, $project]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Edit project</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="name" class="kt-form-label max-w-56">Project name <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required class="kt-input" />
                            @error('name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="description" class="kt-form-label max-w-56">Description</label>
                        <div class="grow">
                            <textarea name="description" id="description" rows="3" class="kt-textarea resize-y">{{ old('description', $project->description) }}</textarea>
                            @error('description')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="type" class="kt-form-label max-w-56">Type</label>
                        <div class="grow">
                            <select name="type" id="type" class="kt-select">
                                <option value="">— Select —</option>
                                @foreach ($projectTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $project->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="planned_budget" class="kt-form-label max-w-56">Planned budget <span class="text-destructive">*</span></label>
                        <div class="grow flex gap-2">
                            <input type="number" name="planned_budget" id="planned_budget" value="{{ old('planned_budget', $project->planned_budget) }}" step="0.01" min="0" required class="kt-input" />
                            <select name="currency_code" id="currency_code" required class="kt-select w-24">
                                @foreach ($currencies ?? [] as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', $project->currency_code) === $code ? 'selected' : '' }}>{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('planned_budget')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="start_date" class="kt-form-label max-w-56">Start date</label>
                        <div class="grow">
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="kt-input" />
                            @error('start_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="target_end_date" class="kt-form-label max-w-56">Target end date</label>
                        <div class="grow">
                            <input type="date" name="target_end_date" id="target_end_date" value="{{ old('target_end_date', $project->target_end_date?->format('Y-m-d')) }}" class="kt-input" />
                            @error('target_end_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="actual_end_date" class="kt-form-label max-w-56">Actual end date</label>
                        <div class="grow">
                            <input type="date" name="actual_end_date" id="actual_end_date" value="{{ old('actual_end_date', $project->actual_end_date?->format('Y-m-d')) }}" class="kt-input" />
                            @error('actual_end_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="status" class="kt-form-label max-w-56">Status <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="status" id="status" required class="kt-select">
                                @foreach ($projectStatuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $project->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="priority" class="kt-form-label max-w-56">Priority</label>
                        <div class="grow">
                            <select name="priority" id="priority" class="kt-select">
                                <option value="">— Select —</option>
                                @foreach ($priorities as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', $project->priority) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.projects.show', [$family, $project]) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary">Update project</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
