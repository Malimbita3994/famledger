@extends('layouts.metronic')

@section('title', 'Add Permission')
@section('page_title', 'Add Permission')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        Back to permissions
    </a>

    <form action="{{ route('admin.permissions.store') }}" method="POST" class="max-w-xl">
        @csrf
        <div class="kt-card p-5 space-y-4">
            <h2 class="text-lg font-semibold">
                Create permission
            </h2>

            <div>
                <label for="name" class="kt-form-label">
                    Permission name
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    required
                    class="kt-input"
                    placeholder="e.g. dashboard_view, reports_download"
                />
                @error('name')
                    <p class="kt-form-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 pt-2">
                <a href="{{ route('admin.permissions.index') }}" class="kt-btn kt-btn-outline">
                    Cancel
                </a>
                <button type="submit" class="kt-btn kt-btn-primary">
                    Create permission
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

