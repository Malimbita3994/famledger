@push('styles')
<style>
    /* Accent tokens: public/css/famledger-pulse-buttons.css (:root). Scoped to .fin-pulse-page (layout wrapper). */
    .fin-pulse-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .fin-pulse-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--fin-accent);
    }
    .fin-pulse-breadcrumb a {
        color: #64748b;
        transition: color 0.2s ease;
    }
    .fin-pulse-breadcrumb a:hover {
        color: var(--fin-accent);
    }
    /* Pulse cards: explicit class or any kt-card in scope */
    .fin-pulse-page .fin-pulse-kt-card,
    .fin-pulse-page .kt-card:not(.no-fin-pulse) {
        border-radius: 16px !important;
        border: 1px solid rgba(14, 165, 233, 0.16) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .dark .fin-pulse-page .fin-pulse-kt-card,
    .dark .fin-pulse-page .kt-card:not(.no-fin-pulse) {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.5) 0%, rgb(15 23 42 / 0.85) 100%) !important;
        border-color: rgba(14, 165, 233, 0.2) !important;
    }
    .fin-pulse-page .fin-pulse-kt-card .kt-card-header,
    .fin-pulse-page .kt-card:not(.no-fin-pulse) .kt-card-header {
        border-bottom-color: rgba(14, 165, 233, 0.12);
    }
    .dark .fin-pulse-page .fin-pulse-kt-card .kt-card-header,
    .dark .fin-pulse-page .kt-card:not(.no-fin-pulse) .kt-card-header {
        border-bottom-color: rgba(14, 165, 233, 0.18);
    }
    .fin-pulse-page .fin-pulse-kt-card .kt-card-title,
    .fin-pulse-page .kt-card:not(.no-fin-pulse) .kt-card-title {
        color: var(--fin-accent);
        font-weight: 700;
        letter-spacing: -0.02em;
    }
</style>
@endpush
