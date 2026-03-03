@extends('layouts.metronic')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

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
    <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i> Back to user
    </a>

    <form id="edit-user-form" action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="kt-card p-5 lg:p-6 space-y-4">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold text-mono">Edit user</h2>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-foreground mb-3">
                    Basic Information
                </h3>
                <div class="user-main-row">
                    <div class="user-main-col grid gap-1.5">
                        <label for="name" class="kt-form-label">Name <span class="text-destructive">*</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="kt-input"
                        />
                        @error('name')
                            <p class="kt-form-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="email" class="kt-form-label">Email <span class="text-destructive">*</span></label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="kt-input"
                        />
                        @error('email')
                            <p class="kt-form-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="phone" class="kt-form-label">Phone</label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $user->phone) }}"
                            class="kt-input"
                        />
                    </div>
                    <div class="user-main-col grid gap-1.5">
                        <label for="status" class="kt-form-label">Status</label>
                        <select name="status" id="status" class="kt-select w-full">
                            @foreach (\App\Models\User::statuses() as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $user->status) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
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
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }} class="kt-checkbox" />
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.users.show', $user) }}" class="kt-btn kt-btn-outline">
                    Cancel
                </a>
                <button type="submit" class="kt-btn kt-btn-primary">
                    Update user
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
 document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('edit-user-form');
  if (!form || typeof Swal === 'undefined') return;

  form.addEventListener('submit', function (e) {
   // Avoid double confirmation when form is re-submitted programmatically
   if (form.dataset.submitting === 'true') {
    return;
   }

   e.preventDefault();

   var name = @json($user->name);

   Swal.fire({
    title: 'Update user?',
    text: 'Save changes to ' + name + '\'s account.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, save',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#6b7280'
   }).then(function (result) {
    if (result.isConfirmed) {
     form.dataset.submitting = 'true';
     form.submit();
    }
   });
  });
 });
</script>
@endpush
