{{--
    Metronic ⋮ dropdown with colored row icons and sky-tinted caret.

    <x-famledger.dots-actions-menu :label="__('Actions')">
        <div class="kt-menu-item">
            <a class="kt-menu-link cursor-pointer" href="...">
                <span class="kt-menu-icon"><i class="ki-filled ki-eye fl-dots-action-icon--primary"></i></span>
                <span class="kt-menu-title">View</span>
            </a>
        </div>
    </x-famledger.dots-actions-menu>

    Icon modifiers (Metronic tints .kt-menu-icon i; these override): fl-dots-action-icon--primary | --warning | --danger | --success

    align: start | end (justify on the trigger row)
    placement: bottom-end | bottom-start (matches caret position)
--}}
@props([
    'label' => 'Actions',
    'align' => 'end',
    'placement' => 'bottom-end',
    'dropdownClass' => 'w-[180px]',
])

@php
    $justify = $align === 'start' ? 'justify-start' : 'justify-end';
    $caretModifier = $placement === 'bottom-start'
        ? 'fl-dots-actions-menu--caret-start'
        : 'fl-dots-actions-menu--caret-end';
@endphp

@once
@push('styles')
<style>
    .fl-dots-actions-menu .kt-menu-toggle,
    .fl-dots-actions-menu .kt-menu-link {
        cursor: pointer;
    }
    /* Metronic: .kt-menu-default .kt-menu-item .kt-menu-icon i { color: var(--muted-foreground); } */
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--primary {
        color: #0284c7 !important;
    }
    .dark .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--primary {
        color: #38bdf8 !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover .kt-menu-icon i.fl-dots-action-icon--primary {
        color: #0369a1 !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--warning {
        color: #d97706 !important;
    }
    .dark .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--warning {
        color: #fbbf24 !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover .kt-menu-icon i.fl-dots-action-icon--warning {
        color: #b45309 !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--danger {
        color: #e11d48 !important;
    }
    .dark .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--danger {
        color: #fb7185 !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover .kt-menu-icon i.fl-dots-action-icon--danger {
        color: #be123c !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--success {
        color: #059669 !important;
    }
    .dark .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-icon i.fl-dots-action-icon--success {
        color: #34d399 !important;
    }
    .fl-dots-actions-menu .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover .kt-menu-icon i.fl-dots-action-icon--success {
        color: #047857 !important;
    }
    .fl-dots-actions-menu .fl-dots-actions-dropdown {
        overflow: visible;
    }
    .fl-dots-actions-menu.fl-dots-actions-menu--caret-end .fl-dots-actions-dropdown::before {
        content: '';
        position: absolute;
        top: -5px;
        right: 10px;
        left: auto;
        width: 9px;
        height: 9px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.22) 0%, var(--popover) 55%);
        border-left: 1px solid rgba(14, 165, 233, 0.55);
        border-top: 1px solid rgba(14, 165, 233, 0.55);
        box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.12);
        transform: rotate(45deg);
        z-index: 1;
        pointer-events: none;
    }
    .dark .fl-dots-actions-menu.fl-dots-actions-menu--caret-end .fl-dots-actions-dropdown::before {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.28) 0%, var(--popover) 55%);
        border-left-color: rgba(56, 189, 248, 0.65);
        border-top-color: rgba(56, 189, 248, 0.65);
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.18);
    }
    .fl-dots-actions-menu.fl-dots-actions-menu--caret-start .fl-dots-actions-dropdown::before {
        content: '';
        position: absolute;
        top: -5px;
        left: 10px;
        right: auto;
        width: 9px;
        height: 9px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.22) 0%, var(--popover) 55%);
        border-left: 1px solid rgba(14, 165, 233, 0.55);
        border-top: 1px solid rgba(14, 165, 233, 0.55);
        box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.12);
        transform: rotate(45deg);
        z-index: 1;
        pointer-events: none;
    }
    .dark .fl-dots-actions-menu.fl-dots-actions-menu--caret-start .fl-dots-actions-dropdown::before {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.28) 0%, var(--popover) 55%);
        border-left-color: rgba(56, 189, 248, 0.65);
        border-top-color: rgba(56, 189, 248, 0.65);
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.18);
    }
</style>
@endpush
@endonce

<div {{ $attributes->class(['fl-dots-actions-menu kt-menu flex-inline', $justify, $caretModifier]) }} data-kt-menu="true">
    <div
        class="kt-menu-item"
        data-kt-menu-item-offset="0, 10px"
        data-kt-menu-item-placement="{{ $placement }}"
        data-kt-menu-item-toggle="dropdown"
        data-kt-menu-item-trigger="click"
    >
        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost cursor-pointer" type="button" aria-label="{{ $label }}">
            <i class="ki-filled ki-dots-vertical text-lg"></i>
        </button>
        <div class="fl-dots-actions-dropdown kt-menu-dropdown kt-menu-default {{ $dropdownClass }}" data-kt-menu-dismiss="true">
            {{ $slot }}
        </div>
    </div>
</div>
