/**
 * Helpers for <x-famledger.detail-modal /> — label | value rows and KTModal.show().
 * Requires Metronic ktui (KTModal) on the page.
 */
(function (window) {
    'use strict';

    function fmt(v) {
        if (v === null || v === undefined || v === '') {
            return '\u2014';
        }
        return String(v);
    }

    function row(label, value) {
        var wrap = document.createElement('div');
        wrap.className = 'famledger-detail-row';
        var dt = document.createElement('div');
        dt.className = 'famledger-detail-label';
        dt.textContent = label;
        var dd = document.createElement('div');
        dd.className = 'famledger-detail-value';
        dd.textContent = value;
        wrap.appendChild(dt);
        wrap.appendChild(dd);
        return wrap;
    }

    /** Stacked label + value for use inside .famledger-detail-modal-body--cols-4 grid */
    function cell(label, value) {
        var wrap = document.createElement('div');
        wrap.className = 'famledger-detail-cell';
        var dt = document.createElement('div');
        dt.className = 'famledger-detail-label';
        dt.textContent = label;
        var dd = document.createElement('div');
        dd.className = 'famledger-detail-value';
        dd.textContent = value;
        wrap.appendChild(dt);
        wrap.appendChild(dd);
        return wrap;
    }

    function show(modalId) {
        var el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
        if (!el || typeof window.KTModal === 'undefined') {
            return;
        }
        var modal = window.KTModal.getInstance(el) || new window.KTModal(el);
        modal.show();
    }

    window.FamLedgerDetailModal = {
        fmt: fmt,
        row: row,
        cell: cell,
        show: show,
    };
})(window);
