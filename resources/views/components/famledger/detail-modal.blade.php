{{--
    FamLedger — centered, compact KT read-only detail modal (label | value rows).

    Requires: layouts that load Metronic `ktui.min.js` (KTModal) and `famledger-form-grids.css`.

    For JSON-driven lists with portal + delegated open, use <x-famledger.entity-detail-modal /> (see that file).

    Basic (fill body with JS using FamLedgerDetailModal from famledger-detail-modal.js):
        <x-famledger.detail-modal id="member_details_modal" :title="__('Member details')" />
        <script>
          var body = document.getElementById('member_details_modal_body');
          body.innerHTML = '';
          body.appendChild(FamLedgerDetailModal.row('Name', FamLedgerDetailModal.fmt(x)));
          FamLedgerDetailModal.show('member_details_modal');
        </script>

    Custom element ids:
        <x-famledger.detail-modal id="x" title-id="x_title" body-id="x_body" />

    Static body (default slot replaces empty body; still uses body-id on the wrapper):
        <x-famledger.detail-modal id="about_modal" :title="__('About')">
            <p class="text-sm text-muted-foreground">…</p>
        </x-famledger.detail-modal>
--}}
@props([
    'id',
    'title' => '',
    'titleId' => null,
    'bodyId' => null,
    'closeLabel' => null,
])

@php
    $titleId = $titleId ?? "{$id}_title";
    $bodyId = $bodyId ?? "{$id}_body";
    $closeLabel = $closeLabel ?? __('Close');
@endphp

@once
    @prepend('scripts')
        <script src="{{ asset('js/famledger-detail-modal.js') }}"></script>
    @endprepend
@endonce

<div
    {{ $attributes->class(['kt-modal', 'kt-modal-center', 'famledger-detail-modal'])->merge([
        'id' => $id,
        'data-kt-modal' => 'true',
    ]) }}
>
    <div class="kt-modal-content famledger-detail-modal-content">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title min-w-0 truncate pe-2" id="{{ $titleId }}">{{ $title }}</h3>
            <button
                type="button"
                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0"
                data-kt-modal-dismiss="true"
                aria-label="{{ $closeLabel }}"
            >
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body px-5 py-4">
            <div id="{{ $bodyId }}" class="famledger-detail-modal-body">{{ $slot }}</div>
            <div class="famledger-detail-modal-footer">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" data-kt-modal-dismiss="true">
                    {{ $closeLabel }}
                </button>
            </div>
        </div>
    </div>
</div>
