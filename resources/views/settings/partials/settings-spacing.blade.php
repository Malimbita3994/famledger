{{-- Explicit spacing for /settings and sub-routes (Metronic bundle may omit Tailwind gap/grid). Injected from layouts.metronic for reliable load. --}}
@once('famledger-settings-spacing')
<style id="famledger-settings-spacing">
    /* —— Settings hub (index, categories, property) —— */
    .settings-pulse .settings-grid-3 {
        display: grid !important;
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    @media (min-width: 900px) {
        .settings-pulse .settings-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 2.5rem;
        }
    }
    .settings-pulse .settings-grid-2 {
        display: grid !important;
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse .settings-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2.5rem;
        }
    }
    .settings-pulse .settings-grid-2-mt {
        margin-top: 1.75rem;
    }
    .settings-pulse .settings-stack {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .settings-pulse .settings-stack-loose {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse .settings-stack-loose {
            gap: 2rem;
        }
    }
    .settings-pulse .settings-card-pad {
        padding: 1.65rem 1.85rem !important;
    }
    @media (min-width: 1024px) {
        .settings-pulse .settings-card-pad {
            padding: 1.85rem 2.15rem !important;
        }
    }
    .settings-pulse .settings-page-toolbar {
        padding-bottom: 1.25rem;
    }
    .settings-pulse .settings-lookup-panel-inner {
        padding: 1.15rem 1.5rem;
    }
    @media (min-width: 640px) {
        .settings-pulse .settings-lookup-panel-inner {
            padding: 1.25rem 1.75rem;
        }
    }
    .settings-pulse .settings-tile-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .settings-pulse .settings-tile-body {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .settings-pulse .settings-intro-inner {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    @media (min-width: 640px) {
        .settings-pulse .settings-intro-inner {
            flex-direction: row;
            align-items: flex-start;
            gap: 1.25rem;
        }
    }
    .settings-pulse .settings-intro-banner {
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.75rem;
    }
    @media (min-width: 640px) {
        .settings-pulse .settings-intro-banner {
            padding: 1.35rem 1.75rem;
        }
    }
    .settings-pulse form.settings-form-cat {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    @media (min-width: 640px) {
        .settings-pulse form.settings-form-cat {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem 1.35rem;
            align-items: start;
        }
        .settings-pulse form.settings-form-cat .settings-form-cat-name {
            grid-column: 1;
        }
        .settings-pulse form.settings-form-cat .settings-form-cat-parent {
            grid-column: 2;
        }
        .settings-pulse form.settings-form-cat .settings-form-cat-actions {
            grid-column: 1 / -1;
            justify-self: end;
        }
    }
    .settings-pulse form.settings-form-attr {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    @media (min-width: 640px) {
        .settings-pulse form.settings-form-attr {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1.25rem 1.35rem;
            align-items: start;
        }
        .settings-pulse form.settings-form-attr .settings-form-attr-name {
            grid-column: 1 / 3;
        }
        .settings-pulse form.settings-form-attr .settings-form-attr-cat {
            grid-column: 3;
        }
        .settings-pulse form.settings-form-attr .settings-form-attr-type {
            grid-column: 1;
        }
        .settings-pulse form.settings-form-attr .settings-form-attr-checks {
            grid-column: 1 / -1;
        }
        .settings-pulse form.settings-form-attr .settings-form-attr-sort {
            grid-column: 1;
        }
        .settings-pulse form.settings-form-attr .settings-form-attr-actions {
            grid-column: 1 / -1;
            justify-self: end;
        }
    }

    /* —— Audit log —— */
    .audit-log-page .audit-main-stack {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }
    @media (min-width: 1024px) {
        .audit-log-page .audit-main-stack {
            gap: 2rem;
        }
    }
    .audit-log-page .audit-col-stack {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
        min-width: 0;
    }
    @media (min-width: 1024px) {
        .audit-log-page .audit-col-stack {
            gap: 2rem;
        }
    }
    .audit-log-page .audit-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .audit-log-page .audit-summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
        }
    }
    .audit-log-page .audit-summary-card {
        padding: 0.9rem 1.15rem;
    }
    .audit-log-page .audit-summary-content {
        padding: 1.25rem 1.5rem;
    }

    /* —— Notifications (scoped shell) —— */
    .settings-pulse.settings-notifications-page .settings-notifications-main {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .settings-pulse.settings-notifications-page #notification_settings_tabs_root {
        padding: 1.5rem 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse.settings-notifications-page #notification_settings_tabs_root {
            padding: 1.75rem 2rem;
            gap: 1.5rem;
        }
    }
    .settings-pulse.settings-notifications-page #notification_settings_tabs .settings-notif-tab-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .settings-pulse.settings-notifications-page .notifications-settings-tab-panel {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse.settings-notifications-page .notifications-settings-tab-panel {
            gap: 2rem;
        }
    }
    .settings-pulse.settings-notifications-page .notifications-faq-subpanel {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse.settings-notifications-page .notifications-faq-subpanel {
            gap: 2rem;
        }
    }
    .settings-pulse.settings-notifications-page .kt-tab-content .kt-card-group.flex {
        gap: 0.85rem;
    }
    .settings-pulse.settings-notifications-page .kt-tab-content .kt-card-group.flex.items-center {
        column-gap: 0.85rem;
        row-gap: 0.65rem;
    }
    .settings-pulse.settings-notifications-page #notif_faq_subtabs_tabs .settings-notif-tab-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .settings-pulse.settings-notifications-page .kt-card-content.settings-notif-section-pad {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 1.5rem 1.75rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse.settings-notifications-page .kt-card-content.settings-notif-section-pad {
            gap: 2rem;
            padding: 1.75rem 2rem;
        }
    }
    .settings-pulse.settings-notifications-page form.settings-notif-form-grid {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    @media (min-width: 1024px) {
        .settings-pulse.settings-notifications-page form.settings-notif-form-grid {
            gap: 1.5rem;
        }
    }
</style>
@endonce
