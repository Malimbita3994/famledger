@extends('layouts.metronic')

@section('title', 'Add Role')
@section('page_title', 'Add Role')

@section('content')
<style>
@media (min-width: 1024px) {
    .role-main-row {
        display: flex;
        gap: 1.75rem;
    }
    .role-main-row .role-main-col {
        flex: 1 1 0;
    }
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        Back to roles
    </a>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="kt-card p-5 lg:p-6 space-y-4">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold text-mono">
                    Role Information
                </h2>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-foreground mb-3">
                    Basic Information
                </h3>
                <div class="role-main-row">
                    <div class="role-main-col grid gap-1.5">
                        <label for="name" class="kt-form-label">
                            Role Name <span class="text-destructive">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            class="kt-input"
                            placeholder="e.g., manager, editor"
                        />
                        <p class="text-xs text-muted-foreground mt-1">
                            Use lowercase with underscores (e.g., content_manager)
                        </p>
                        @error('name')
                            <p class="kt-form-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="role-main-col grid gap-1.5">
                        <label for="display_name" class="kt-form-label">
                            Display Name
                        </label>
                        <input
                            type="text"
                            name="display_name"
                            id="display_name"
                            value="{{ old('display_name') }}"
                            class="kt-input"
                            placeholder="e.g., Content Manager"
                        />
                        <p class="text-xs text-muted-foreground mt-1">
                            Human-readable name (optional)
                        </p>
                        @error('display_name')
                            <p class="kt-form-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="role-main-col grid gap-1.5">
                        <label for="description" class="kt-form-label">
                            Description
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="kt-input min-h-[80px] resize-y"
                            placeholder="Brief description of this role"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="kt-form-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.roles.index') }}" class="kt-btn kt-btn-outline">
                    Cancel
                </a>
                <button type="submit" class="kt-btn kt-btn-primary">
                    Create role
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
