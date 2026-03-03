@extends('layouts.metronic')

@section('title', 'Edit Member')
@section('page_title', 'Edit Member')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <form action="{{ route('families.members.update', [$family, $familyMember]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Edit member</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <span class="kt-form-label max-w-56">Account</span>
                        <div class="grow">
                            <p class="text-foreground font-medium">{{ $familyMember->user->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-muted-foreground">{{ $familyMember->user->email ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="member_name" class="kt-form-label max-w-56">Member name</label>
                        <div class="grow">
                            <input type="text" name="member_name" id="member_name" value="{{ old('member_name', $familyMember->member_name) }}" placeholder="Display name in this family" class="kt-input" />
                            @error('member_name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="kt-form-label max-w-56">Sex</label>
                        <div class="grow flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="sex" value="male" {{ old('sex', $familyMember->sex) === 'male' ? 'checked' : '' }} class="kt-radio" /><span class="text-sm">Male</span></label>
                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="sex" value="female" {{ old('sex', $familyMember->sex) === 'female' ? 'checked' : '' }} class="kt-radio" /><span class="text-sm">Female</span></label>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="kt-form-label max-w-56">Adult or child</label>
                        <div class="grow flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="member_type" value="adult" {{ old('member_type', $familyMember->member_type ?? 'adult') === 'adult' ? 'checked' : '' }} class="kt-radio" /><span class="text-sm">Adult</span></label>
                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="member_type" value="child" {{ old('member_type', $familyMember->member_type) === 'child' ? 'checked' : '' }} class="kt-radio" /><span class="text-sm">Child</span></label>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="role_id" class="kt-form-label max-w-56">Role <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select
                                name="role_id"
                                id="role_id"
                                required
                                class="kt-select"
                                aria-invalid="{{ $errors->has('role_id') ? 'true' : 'false' }}"
                            >
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $familyMember->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @php
                        $ownerRole = $roles->firstWhere('name', 'Owner');
                        $canBePrimary = $ownerRole && $familyMember->role_id == $ownerRole->id;
                    @endphp
                    @if ($ownerRole)
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5" id="primary_owner_row">
                        <label for="is_primary" class="kt-form-label max-w-56">Primary owner</label>
                        <div class="grow flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="is_primary"
                                id="is_primary"
                                value="1"
                                {{ ($canBePrimary && old('is_primary', $familyMember->is_primary)) ? 'checked' : '' }}
                                {{ ! $canBePrimary ? 'disabled' : '' }}
                                class="kt-checkbox js-primary-checkbox"
                                data-owner-role-id="{{ $ownerRole->id }}"
                            />
                            <span class="text-sm text-muted-foreground">Only owners can be set as primary. One primary per family.</span>
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.show', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Save changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@if ($ownerRole ?? null)
<script>
(function () {
    var roleSelect = document.getElementById('role_id');
    var primaryCheckbox = document.querySelector('.js-primary-checkbox');
    var ownerRoleId = primaryCheckbox && primaryCheckbox.getAttribute('data-owner-role-id');
    if (!roleSelect || !primaryCheckbox || !ownerRoleId) return;
    function togglePrimary() {
        var isOwner = roleSelect.value === ownerRoleId;
        primaryCheckbox.disabled = !isOwner;
        if (!isOwner) primaryCheckbox.checked = false;
    }
    roleSelect.addEventListener('change', togglePrimary);
    togglePrimary();
})();
</script>
@endif
@endsection
