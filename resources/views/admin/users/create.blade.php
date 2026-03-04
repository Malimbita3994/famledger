@extends('layouts.metronic')

@section('title', 'Add User')
@section('page_title', 'Add User')

@section('content')
<style>
@media (min-width: 1024px) {
    .user-main-row {
        display: flex;
        gap: 1.75rem;
    }
    .user-main-row .user-main-col {
        flex: 1 1 0;
    }
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">Back to users</a>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="kt-card p-5 lg:p-6 space-y-4">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold text-mono">Create user</h2>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-foreground mb-3">
                    Basic Information
                </h3>
                <div class="user-main-row">
                    <div class="user-main-col grid gap-1.5">
                        <label for="name" class="kt-form-label">Name <span class="text-destructive">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="kt-input" />
                        @error('name')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="email" class="kt-form-label">Email <span class="text-destructive">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="kt-input" />
                        @error('email')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="password" class="kt-form-label">Password <span class="text-destructive">*</span></label>
                        <input type="password" name="password" id="password" required class="kt-input" />
                        @error('password')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="user-main-row mt-4">
                    <div class="user-main-col grid gap-1.5">
                        <label for="password_confirmation" class="kt-form-label">Confirm password <span class="text-destructive">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="kt-input" />
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="phone" class="kt-form-label">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="kt-input" />
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="status" class="kt-form-label">Status</label>
                        <select name="status" id="status" class="kt-select w-full">
                            @foreach (App\Models\User::statuses() as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-foreground mb-3">
                    Roles
                </h3>
                <div class="space-y-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="kt-checkbox" />
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-outline">Cancel</a>
                <button type="submit" class="kt-btn kt-btn-primary">Create user</button>
            </div>
        </div>
    </form>
</div>
@endsection
