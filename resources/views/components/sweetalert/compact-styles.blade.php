{{-- Compact SweetAlert2 modal sizing (all non-toast dialogs). Use with x-sweetalert.cdn + app-scripts or guest-auth-scripts. --}}
<style>
    .swal2-popup:not(.swal2-toast) {
        padding: 0.65rem 1.15rem 0.75rem !important;
        width: 360px !important;
        max-width: min(360px, calc(100vw - 2rem)) !important;
        box-sizing: border-box !important;
        /* Swal2 scales the whole icon (ring + checkmark) via zoom; do not set fixed width/height on .swal2-icon */
        --swal2-icon-zoom: 0.68;
    }
    .swal2-popup:not(.swal2-toast) .swal2-icon {
        margin: 0.35rem auto 0.45rem !important;
    }
    .swal2-popup:not(.swal2-toast) .swal2-title {
        padding: 0 !important;
        margin: 0 0 0.15rem !important;
        line-height: 1.25 !important;
        font-size: 1.0625rem !important;
        font-weight: 600 !important;
    }
    .swal2-popup:not(.swal2-toast) .swal2-html-container,
    .swal2-popup:not(.swal2-toast) #swal2-html-container {
        margin: 0.35rem 0 0 !important;
        padding: 0 !important;
        font-size: 0.875rem !important;
        line-height: 1.45 !important;
    }
    .swal2-popup:not(.swal2-toast) .swal2-actions {
        margin: 0.45rem auto 0 !important;
        gap: 0.5rem !important;
    }
    .swal2-popup:not(.swal2-toast) .swal2-styled.swal2-confirm,
    .swal2-popup:not(.swal2-toast) .swal2-styled.swal2-cancel,
    .swal2-popup:not(.swal2-toast) .swal2-styled.swal2-deny {
        padding: 0.35rem 0.9rem !important;
        font-size: 0.8125rem !important;
    }
    /* Destructive confirm (e.g. delete family): stronger warning chrome */
    .swal2-popup.swal2-danger-confirm:not(.swal2-toast) {
        width: 400px !important;
        max-width: min(400px, calc(100vw - 2rem)) !important;
        border: 2px solid rgb(252 165 165) !important;
        box-shadow: 0 10px 40px -10px rgb(185 28 28 / 0.25) !important;
        background: linear-gradient(180deg, #fff 0%, rgb(254 242 242) 100%) !important;
    }
    .dark .swal2-popup.swal2-danger-confirm:not(.swal2-toast) {
        border-color: rgb(127 29 29 / 0.55) !important;
        background: linear-gradient(180deg, var(--tw-card, #1e293b) 0%, rgb(127 29 29 / 0.12) 100%) !important;
        box-shadow: 0 10px 40px -10px rgb(0 0 0 / 0.45) !important;
    }
    .swal2-popup.swal2-danger-confirm:not(.swal2-toast) .swal2-title {
        color: rgb(185 28 28) !important;
    }
    .dark .swal2-popup.swal2-danger-confirm:not(.swal2-toast) .swal2-title {
        color: rgb(252 165 165) !important;
    }

    /* FamLedger global search — match marketing contact modal (#landingContactModal) chrome */
    .swal2-container.fl-sw-search-z {
        z-index: 10060 !important;
    }
    .swal2-popup.fl-sw-search-popup:not(.swal2-toast) {
        width: min(96vw, 720px) !important;
        max-width: min(96vw, 720px) !important;
        padding: 0 !important;
        border-radius: 10px !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15) !important;
        background: #fff !important;
        box-sizing: border-box !important;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
        overflow: hidden !important;
    }
    .dark .swal2-popup.fl-sw-search-popup:not(.swal2-toast) {
        border-color: rgb(51 65 85 / 0.55) !important;
        background: rgb(30 41 59) !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35) !important;
    }
    .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-close {
        font-size: 28px !important;
        font-weight: 300 !important;
        color: #222 !important;
        margin: 0 !important;
        padding: 8px 12px !important;
        height: auto !important;
        width: auto !important;
        line-height: 1 !important;
        top: 0.5rem !important;
        right: 0.35rem !important;
        transition: color 0.15s ease, opacity 0.15s ease;
    }
    .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-close:hover {
        color: #009ef7 !important;
    }
    .dark .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-close {
        color: rgb(226 232 240) !important;
    }
    .dark .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-close:hover {
        color: #38bdf8 !important;
    }
    /* Search modal: full-width LTR layout; header row like Bootstrap .modal-header */
    .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-title {
        text-align: left !important;
        width: 100% !important;
        box-sizing: border-box !important;
        padding: 18px 48px 18px 22px !important;
        margin: 0 !important;
        border-bottom: 1px solid #eee !important;
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #222 !important;
        line-height: 1.4 !important;
    }
    .dark .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-title {
        border-bottom-color: rgb(51 65 85 / 0.55) !important;
        color: rgb(241 245 249) !important;
    }
    .swal2-popup.fl-sw-search-popup:not(.swal2-toast) .swal2-html-container,
    .swal2-popup.fl-sw-search-popup:not(.swal2-toast) #swal2-html-container {
        text-align: left !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 20px 22px 18px !important;
        font-size: 16px !important;
        line-height: 1.5 !important;
    }
    .fl-sw-search-input {
        display: block;
        width: 100%;
        box-sizing: border-box;
        font-size: 16px;
        line-height: 1.4;
        padding: 10px 12px;
        min-height: 48px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: none;
        margin: 0 0 12px 0;
        color: #222;
        -webkit-appearance: none;
        appearance: none;
    }
    .fl-sw-search-input::placeholder {
        color: #999;
    }
    .fl-sw-search-input:focus {
        outline: none;
        border-color: #009ef7;
        box-shadow: 0 0 0 1px rgba(0, 158, 247, 0.22);
    }
    .dark .fl-sw-search-input {
        background: rgb(15 23 42 / 0.5);
        border-color: rgb(71 85 105);
        color: rgb(241 245 249);
    }
    .dark .fl-sw-search-input:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.25);
    }
    .fl-sw-search-inner {
        width: 100%;
        text-align: left;
    }
    .fl-sw-search-inner input[type='search'],
    .fl-sw-search-inner input[type='search']::placeholder {
        text-align: left;
    }
    #fl-sw-search-results {
        text-align: left;
    }

    /*
     * Search hits: plain CSS (do not rely on Tailwind for JS-built rows — utilities are often purged
     * when class names only appear inside <script> strings). Buttons default to text-align: center.
     */
    body .swal2-container.fl-sw-search-z .swal2-popup.fl-sw-search-popup .swal2-html-container,
    body .swal2-container.fl-sw-search-z .swal2-popup.fl-sw-search-popup #swal2-html-container {
        text-align: left !important;
    }
    #fl-sw-search-results button.fl-sw-search-hit {
        display: block;
        width: 100%;
        box-sizing: border-box;
        text-align: left !important;
        border: 0;
        border-bottom: 1px solid #eee;
        background: transparent;
        cursor: pointer;
        font: inherit;
        padding: 0.75rem 0.75rem;
        margin: 0;
    }
    .dark #fl-sw-search-results button.fl-sw-search-hit {
        border-bottom-color: rgb(51 65 85 / 0.45);
    }
    #fl-sw-search-results button.fl-sw-search-hit:hover {
        background: rgb(248 250 252);
    }
    .dark #fl-sw-search-results button.fl-sw-search-hit:hover {
        background: rgb(51 65 85 / 0.35);
    }
    #fl-sw-search-results .fl-sw-search-hit .fl-sw-search-title {
        text-align: left !important;
        color: #009ef7 !important;
        font-size: 0.9375rem;
        font-weight: 600;
        line-height: 1.375;
    }
    #fl-sw-search-results .fl-sw-search-hit:hover .fl-sw-search-title {
        text-decoration: underline;
        color: #0088d4 !important;
    }
    .dark #fl-sw-search-results .fl-sw-search-hit .fl-sw-search-title {
        color: #60a5fa !important;
    }
    .dark #fl-sw-search-results .fl-sw-search-hit:hover .fl-sw-search-title {
        color: #93c5fd !important;
    }
    #fl-sw-search-results .fl-sw-search-hit .fl-sw-search-meta {
        text-align: left !important;
        margin-top: 0.25rem;
        color: #666;
        font-size: 0.75rem;
        line-height: 1.4;
    }
    .dark #fl-sw-search-results .fl-sw-search-hit .fl-sw-search-meta {
        color: rgb(148 163 184);
    }
    #fl-sw-search-results .fl-sw-search-hit .fl-sw-search-ledger {
        text-align: left !important;
        margin-top: 0.25rem;
        font-size: 0.6875rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        color: rgb(100 116 139);
    }
    .dark #fl-sw-search-results .fl-sw-search-hit .fl-sw-search-ledger {
        color: rgb(148 163 184);
    }
    #fl-sw-search-results .fl-sw-search-empty,
    #fl-sw-search-results .fl-sw-search-loading {
        text-align: left !important;
        padding: 2rem 1rem;
        font-size: 0.875rem;
        color: rgb(71 85 105);
    }
    .dark #fl-sw-search-results .fl-sw-search-empty,
    .dark #fl-sw-search-results .fl-sw-search-loading {
        color: rgb(148 163 184);
    }
    #fl-sw-search-results .fl-sw-search-loading {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.75rem;
    }
    .fl-sw-search-inner .fl-sw-search-footer {
        text-align: left !important;
        margin-top: 0.75rem;
        font-size: 13px;
        line-height: 1.65;
        color: #777;
    }
    .dark .fl-sw-search-inner .fl-sw-search-footer {
        color: rgb(148 163 184);
    }
    #fl-sw-search-results.fl-sw-search-results-box {
        min-height: 12rem;
        max-height: min(58vh, 400px);
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        border-radius: 6px;
        border: 1px solid #ddd;
        background: rgb(255 255 255);
    }
    .dark #fl-sw-search-results.fl-sw-search-results-box {
        border-color: rgb(51 65 85 / 0.55);
        background: rgb(15 23 42 / 0.35);
    }
    #fl-sw-search-results .fl-sw-search-section {
        box-sizing: border-box;
        text-align: left !important;
        position: sticky;
        top: 0;
        z-index: 1;
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid #eee;
        background: rgb(248 250 252 / 0.97);
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgb(100 116 139);
    }
    .dark #fl-sw-search-results .fl-sw-search-section {
        border-bottom-color: rgb(51 65 85 / 0.5);
        background: rgb(30 41 59 / 0.92);
        color: rgb(148 163 184);
    }
    .fl-sw-search-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid #009ef7;
        border-top-color: transparent;
        border-radius: 50%;
        animation: fl-sw-spin 0.75s linear infinite;
        flex-shrink: 0;
    }
    @keyframes fl-sw-spin {
        to {
            transform: rotate(360deg);
        }
    }
    #fl-sw-search-results .fl-sw-search-error {
        color: rgb(220 38 38) !important;
    }
    .dark #fl-sw-search-results .fl-sw-search-error {
        color: rgb(248 113 113) !important;
    }
</style>
