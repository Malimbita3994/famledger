{{--
    Reusable read-only detail modal: portals to @stack('famledger_bootstrap_modals'), fills from JSON payloads,
    opens on delegated clicks or optional flash id.

    Payload shape (PHP array, json-encoded):
        [
            entityId => [
                'title' => string,   // modal title when opened for this entity
                'rows'  => [
                    ['l' => 'Label', 'v' => 'Value'],
                    ['l' => '…', 'v' => '…', 'full' => true],  // optional; grid4 only — span all columns
                ],
            ],
            …
        ]

    Usage (property grid example):
        <x-famledger.entity-detail-modal
            id="property_details_modal"
            :title="__('Property details')"
            :payloads="$propertyModalPayloads"
            :open-on-load="$openPropertyModalId"
            variant="grid4"
            trigger-attribute="data-property-modal"
        />

    List layout (label | value rows, narrow panel):
        variant="default"

    Requires: layouts with @stack('famledger_bootstrap_modals'), @stack('scripts'), Metronic KTModal,
    famledger-form-grids.css, famledger-detail-modal.js (loaded by <x-famledger.detail-modal />).

    Programmatic open (optional):
        window.FamLedgerEntityDetailModals['your_modal_id'](entityId);
--}}
@props([
    'id',
    'title' => null,
    'payloads' => [],
    'openOnLoad' => null,
    'variant' => 'default',
    'triggerAttribute' => 'data-fl-entity-detail',
    'titleId' => null,
    'bodyId' => null,
    'closeLabel' => null,
])

@php
    $payloads = is_array($payloads) ? $payloads : [];
    $title = $title ?? __('Details');
    $titleIdResolved = $titleId ?? "{$id}_title";
    $bodyIdResolved = $bodyId ?? "{$id}_body";
    $closeLabelResolved = $closeLabel ?? __('Close');
    $useGrid4 = $variant === 'grid4';
    $modalExtraClass = trim(
        ($useGrid4 ? 'famledger-detail-modal--wide famledger-detail-modal--grid-4' : '')
        . ' ' . (string) ($attributes->get('class') ?? '')
    );
@endphp

@if (! empty($payloads))
    @push('famledger_bootstrap_modals')
        <x-famledger.detail-modal
            :id="$id"
            :title="$title"
            :title-id="$titleIdResolved"
            :body-id="$bodyIdResolved"
            :close-label="$closeLabelResolved"
            class="{{ trim($modalExtraClass) }}"
        />
    @endpush

    @push('scripts')
    <script>
    (function () {
        var payloads = @json($payloads);
        var autoOpenId = @json($openOnLoad);
        var modalId = @json($id);
        var titleId = @json($titleIdResolved);
        var bodyId = @json($bodyIdResolved);
        var triggerAttr = @json($triggerAttribute);
        var useGrid4 = @json($useGrid4);
        var D = window.FamLedgerDetailModal;
        if (!D) return;

        function openEntityDetailModal(entityId) {
            var payload = payloads[String(entityId)] || payloads[entityId];
            if (!payload) return;
            var body = document.getElementById(bodyId);
            var titleEl = document.getElementById(titleId);
            if (!body || !titleEl) return;
            titleEl.textContent = payload.title || '';
            body.innerHTML = '';
            if (useGrid4) {
                body.classList.add('famledger-detail-modal-body--cols-4');
            } else {
                body.classList.remove('famledger-detail-modal-body--cols-4');
            }
            (payload.rows || []).forEach(function (r) {
                var node = useGrid4 ? D.cell(r.l, D.fmt(r.v)) : D.row(r.l, D.fmt(r.v));
                if (useGrid4 && r.full) {
                    node.classList.add('famledger-detail-cell--full');
                }
                body.appendChild(node);
            });
            D.show(modalId);
        }

        // Capture phase: KTMenu/dropdown handlers often stopPropagation on bubble, so document bubble listeners never run.
        document.addEventListener('click', function (e) {
            var sel = '[' + triggerAttr + ']';
            var el = e.target.closest(sel);
            if (!el) return;
            e.preventDefault();
            e.stopPropagation();
            openEntityDetailModal(el.getAttribute(triggerAttr));
        }, true);

        document.addEventListener('DOMContentLoaded', function () {
            if (autoOpenId) {
                openEntityDetailModal(autoOpenId);
            }
        });

        if (!window.FamLedgerEntityDetailModals) {
            window.FamLedgerEntityDetailModals = {};
        }
        window.FamLedgerEntityDetailModals[@json($id)] = openEntityDetailModal;
    })();
    </script>
    @endpush
@endif
