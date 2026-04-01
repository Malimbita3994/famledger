{{-- Shared field row for inline + modal; $idPrefix keeps ids unique --}}
@php
    $p = $idPrefix ?? 'modal';
@endphp
<div class="famledger-tree-relationship-modal-fields famledger-form-row-3 famledger-tree-relationship-fields">
    <div class="famledger-tree-field min-w-0">
        <label class="famledger-tree-field-label" for="tree-rel-user-{{ $p }}">
            {{ __('First person') }}<span class="famledger-tree-field-required" aria-hidden="true">*</span>
        </label>
        <p class="famledger-tree-field-hint" id="tree-rel-user-{{ $p }}-hint">{{ __('Who this relationship starts from.') }}</p>
        <select
            id="tree-rel-user-{{ $p }}"
            name="user_id"
            class="famledger-tree-field-select kt-select w-full min-h-[2.5rem]"
            required
            aria-describedby="tree-rel-user-{{ $p }}-hint"
        >
            <option value="">{{ __('Select family member') }}</option>
            @foreach($members as $member)
                <option value="{{ $member->user_id }}">{{ $member->user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="famledger-tree-field min-w-0">
        <label class="famledger-tree-field-label" for="tree-rel-type-{{ $p }}">
            {{ __('Relationship type') }}<span class="famledger-tree-field-required" aria-hidden="true">*</span>
        </label>
        <p class="famledger-tree-field-hint" id="tree-rel-type-{{ $p }}-hint">{{ __('How the two members are connected.') }}</p>
        <select
            id="tree-rel-type-{{ $p }}"
            name="type"
            class="famledger-tree-field-select kt-select w-full min-h-[2.5rem]"
            required
            aria-describedby="tree-rel-type-{{ $p }}-hint"
        >
            <option value="parent">{{ __('Parent of') }}</option>
            <option value="child">{{ __('Child of') }}</option>
            <option value="spouse">{{ __('Spouse of') }}</option>
            <option value="sibling">{{ __('Sibling of') }}</option>
        </select>
    </div>

    <div class="famledger-tree-field min-w-0">
        <label class="famledger-tree-field-label" for="tree-rel-related-{{ $p }}">
            {{ __('Second person') }}<span class="famledger-tree-field-required" aria-hidden="true">*</span>
        </label>
        <p class="famledger-tree-field-hint" id="tree-rel-related-{{ $p }}-hint">{{ __('The other person in this link.') }}</p>
        <select
            id="tree-rel-related-{{ $p }}"
            name="related_user_id"
            class="famledger-tree-field-select kt-select w-full min-h-[2.5rem]"
            required
            aria-describedby="tree-rel-related-{{ $p }}-hint"
        >
            <option value="">{{ __('Select family member') }}</option>
            @foreach($members as $member)
                <option value="{{ $member->user_id }}">{{ $member->user->name }}</option>
            @endforeach
        </select>
    </div>
</div>
