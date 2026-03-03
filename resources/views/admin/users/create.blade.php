@extends('layouts.metronic')

@section('title', 'Add User')
@section('page_title', 'Add User')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">Back to users</a>

    <form action="{{ route('admin.users.store') }}" method="POST" class="max-w-xl">
        @csrf
        <div class="kt-card p-5 space-y-4">
            <h2 class="text-lg font-semibold">Create user</h2>
            <div>
                <label for="name" class="kt-form-label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="kt-input" />
                @error('name')<p class="kt-form-message">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="kt-form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="kt-input" />
                @error('email')<p class="kt-form-message">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="kt-form-label">Password</label>
                <input type="password" name="password" id="password" required class="kt-input" />
                @error('password')<p class="kt-form-message">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="kt-form-label">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="kt-input" />
            </div>
            <div>
                <label for="phone" class="kt-form-label">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="kt-input" />
            </div>
            <div>
                <label for="status" class="kt-form-label">Status</label>
                <select name="status" id="status" class="kt-select w-auto max-w-xs">
                    @foreach (App\Models\User::statuses() as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="kt-form-label">Roles</label>
                @foreach ($roles as $role)
                    <label class="flex items-center gap-2"><input type="checkbox" name="roles[]" value="{{ $role->name }}" class="kt-checkbox" /> {{ $role->name }}</label>
                @endforeach
            </div>
            <div class="flex gap-2 pt-2">
                <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-outline">Cancel</a>
                <button type="submit" class="kt-btn kt-btn-primary">Create user</button>
            </div>
        </div>
    </form>
</div>
@endsection
