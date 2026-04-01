{{-- Shared styles: /family/timeline/create, /family/vision-board/create — FamLedger teal, control wraps, Metronic-safe --}}
<style>
/* Uses --fin-crud-teal-* from public/css/famledger-kt-btn-crud.css (:root) when loaded */
.famledger-timeline-create-page {
    width: 100%;
    max-width: 44rem;
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box;
}

/* Vision board / wider create forms — room for two-column rows + long hints */
.famledger-create-form-page--wide {
    max-width: min(60rem, 100%);
}
@media (min-width: 1024px) {
    .famledger-create-form-page--wide {
        max-width: min(72rem, 100%);
    }
}

.famledger-timeline-create-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: background 0.15s ease, border-color 0.15s ease;
}
.famledger-timeline-create-back:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}

.famledger-timeline-create-card {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.famledger-timeline-create-hero {
    width: 100%;
    box-sizing: border-box;
    padding: 1.1rem 1.25rem 1.25rem;
    color: #fff;
    background: linear-gradient(
        135deg,
        var(--fin-crud-teal-from-dark, #5eead4) 0%,
        var(--fin-crud-teal-from, #2dd4bf) 38%,
        var(--fin-crud-teal-to, #14b8a6) 72%,
        var(--fin-crud-teal-to-dark, #0d9488) 100%
    );
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
}
@media (min-width: 640px) {
    .famledger-timeline-create-hero {
        padding: 1.3rem 1.6rem 1.45rem;
    }
}

.famledger-timeline-create-hero-inner {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.famledger-timeline-create-hero-text {
    flex: 1;
    min-width: 0;
}
.famledger-create-form-page--wide .famledger-timeline-create-hero-sub {
    max-width: none;
}

.famledger-timeline-create-hero-icon {
    flex-shrink: 0;
    width: 2.75rem;
    height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.28);
}
.famledger-timeline-create-hero-icon .ki-filled {
    font-size: 1.3rem;
    color: #fff;
}

.famledger-timeline-create-hero-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.3;
    color: #fff;
}
.famledger-timeline-create-hero-sub {
    margin: 0.2rem 0 0;
    font-size: 0.875rem;
    line-height: 1.4;
    color: rgba(255, 255, 255, 0.92);
    max-width: 36rem;
}

.famledger-timeline-create-form {
    padding: 1.75rem 1.5rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 0;
}
@media (min-width: 640px) {
    .famledger-timeline-create-form {
        padding: 2rem 2.25rem 2.25rem;
    }
}

.famledger-timeline-create-fields {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.famledger-timeline-create-row {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: 1fr;
}
@media (min-width: 768px) {
    .famledger-timeline-create-row {
        grid-template-columns: 1fr 1fr;
        align-items: start;
    }
}

.famledger-timeline-create-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}
.famledger-timeline-create-label .optional {
    font-weight: 500;
    color: #94a3b8;
}

.famledger-timeline-create-field .invalid-feedback {
    display: block;
    margin-top: 0.375rem;
    font-size: 0.8125rem;
}

.famledger-timeline-create-row input.famledger-timeline-create-input {
    border-radius: 0.75rem !important;
    border: 1px solid #e2e8f0 !important;
    min-height: 2.75rem;
}

.famledger-timeline-create-field-lead {
    margin: 0 0 0.5rem;
    font-size: 0.8125rem;
    line-height: 1.45;
    color: #64748b;
}

.famledger-timeline-create-error {
    margin-top: 0.5rem;
    font-size: 0.8125rem;
    line-height: 1.4;
    color: #dc2626;
}

.famledger-timeline-create-control-wrap {
    position: relative;
    border-radius: 0.875rem;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.famledger-timeline-create-field--enhanced:focus-within:not(.famledger-timeline-create-field--invalid) .famledger-timeline-create-control-wrap {
    border-color: rgba(20, 184, 166, 0.45);
    box-shadow:
        inset 0 1px 2px rgba(15, 23, 42, 0.03),
        0 0 0 3px rgba(20, 184, 166, 0.12);
}

.famledger-timeline-create-field--invalid .famledger-timeline-create-control-wrap {
    border-color: #fca5a5;
    box-shadow:
        inset 0 1px 2px rgba(15, 23, 42, 0.03),
        0 0 0 3px rgba(220, 38, 38, 0.1);
}

input.famledger-timeline-create-input-filled {
    display: block;
    width: 100%;
    box-sizing: border-box;
    margin: 0;
    min-height: 2.875rem;
    padding: 0.7rem 1rem;
    border: none !important;
    border-radius: 0.875rem !important;
    background: transparent !important;
    font-size: 0.9375rem;
    line-height: 1.5;
    color: #0f172a;
    box-shadow: none !important;
}
input.famledger-timeline-create-input-filled::placeholder {
    color: #94a3b8;
    opacity: 1;
}
input.famledger-timeline-create-input-filled:focus {
    outline: none;
}

textarea.famledger-timeline-create-textarea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    margin: 0;
    min-height: 10.5rem;
    padding: 0.9rem 1rem 1rem;
    border: none !important;
    border-radius: 0.875rem !important;
    background: transparent !important;
    font-size: 0.9375rem;
    line-height: 1.65;
    color: #0f172a;
    resize: vertical;
    box-shadow: none !important;
}
textarea.famledger-timeline-create-textarea::placeholder {
    color: #94a3b8;
    opacity: 1;
}
textarea.famledger-timeline-create-textarea:focus {
    outline: none;
}

.famledger-timeline-create-field--steps textarea.famledger-timeline-create-textarea {
    min-height: 7.5rem;
}

.famledger-timeline-create-control-wrap--file {
    padding: 0.35rem 0.5rem;
}
input.famledger-timeline-create-input-file {
    display: block;
    width: 100%;
    box-sizing: border-box;
    margin: 0;
    min-height: 2.75rem;
    padding: 0.45rem 0.5rem;
    border: none !important;
    border-radius: 0.75rem !important;
    background: transparent !important;
    font-size: 0.875rem;
    color: #0f172a;
}
input.famledger-timeline-create-input-file:focus {
    outline: none;
}

.famledger-timeline-create-field .kt-select.famledger-timeline-create-select {
    border-radius: 0.75rem;
    min-height: 2.75rem;
    border: 1px solid #e2e8f0;
}

.famledger-timeline-create-hint {
    margin: 0.4rem 0 0;
    font-size: 0.75rem;
    line-height: 1.45;
    color: #64748b;
}

.famledger-timeline-create-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: center;
    gap: 0.75rem;
    margin-top: 2.5rem;
    padding-top: 1.75rem;
    border-top: 1px solid #e2e8f0;
}
@media (min-width: 640px) {
    .famledger-timeline-create-actions {
        margin-top: 3rem;
        padding-top: 2rem;
    }
}

.famledger-timeline-create-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2.75rem;
    padding: 0 1.5rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
}
.famledger-timeline-create-btn--secondary {
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #334155;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.famledger-timeline-create-btn--secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.famledger-timeline-create-btn--primary {
    padding-left: 2rem;
    padding-right: 2rem;
    color: #fff;
    background: linear-gradient(
        180deg,
        var(--fin-crud-teal-from, #2dd4bf) 0%,
        var(--fin-crud-teal-to, #14b8a6) 100%
    );
    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.06),
        0 4px 14px -3px var(--fin-crud-teal-shadow, rgba(20, 184, 166, 0.42));
}
.famledger-timeline-create-btn--primary:hover {
    filter: brightness(1.04);
    box-shadow:
        0 2px 4px rgba(15, 23, 42, 0.08),
        0 10px 24px -4px var(--fin-crud-teal-shadow-strong, rgba(20, 184, 166, 0.52));
}

.famledger-create-form-tip {
    margin-top: 2rem;
    padding: 1rem 1.15rem;
    border-radius: 0.875rem;
    border: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.8125rem;
    line-height: 1.55;
    color: #475569;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.famledger-create-form-tip .ki-filled {
    flex-shrink: 0;
    margin-top: 0.1rem;
    color: var(--fin-accent, #14b8a6);
    font-size: 1.15rem;
}
.famledger-create-form-tip strong {
    color: #0f172a;
    font-weight: 600;
}
</style>
