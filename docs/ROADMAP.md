# FamLedger — 1-page roadmap (P0 / P1 / P2)

**Goal:** Ship a reliable family finance product on **web + mobile**, with one clear “money in/out” story and safe operations at scale.

---

## P0 — Must ship (stability, trust, parity)

| Item | Why |
|------|-----|
| **Transactions (web) — complete the story** | Modal or single “Add transaction” flow (expense/income toggle), validation, success/error UX; optional deep links from Transactions list to existing create/edit routes. |
| **Auth & family access** | Audit critical routes: 403 on wrong family, no IDOR; consistent checks in web + API. |
| **API ↔ web parity for mobile** | Any screen the app relies on (`/dashboard`, `/user`, incomes/expenses CRUD) stays backward compatible when Laravel changes. |
| **Environment & secrets** | Clear `.env.example`, production `APP_URL`, Sanctum/session config, HTTPS; no secrets in repo. |
| **Backup & recovery runbook** | DB backup, restore smoke test, who to call — one short doc. |

---

## P1 — Should ship (quality, growth, maintainability)

| Item | Why |
|------|-----|
| **Automated tests** | Feature tests: login, family membership gate, create expense/income, transactions index; smoke on key PDF/report routes. |
| **Line endings & formatting** | `.gitattributes` (LF for `*.php`, `*.blade.php`), Pint/Prettier where applicable — fewer noisy diffs. |
| **Observability** | Structured logging for money mutations; optional Sentry (web + API). |
| **Transactions list performance** | Indexes on `incomes(family_id, received_date)`, `expenses(family_id, expense_date)`; revisit union query if volumes grow. |
| **Mobile: navigation + tabs** | Keep tab shell stable; document deep-link names for QA. |
| **EAS / Expo** | Pin `EXPO_PUBLIC_API_BASE_URL` per profile; document dev vs prod. |

---

## P2 — Nice to have (differentiation, polish)

| Item | Why |
|------|-----|
| **Recurring transactions UI** | Align web + mobile with recurring flags where backend supports it. |
| **Budget insights** | “Over budget” callouts on dashboard + mobile. |
| **Exports** | CSV export from Transactions; email scheduled reports (later). |
| **i18n / accessibility** | Screen reader labels on mobile; RTL/date/locale if you expand markets. |
| **Dark mode (web)** | Match Metronic tokens with FamLedger brand. |

---

## Success metrics (lightweight)

- **P0:** Zero critical security gaps on family-scoped routes; mobile works against production API without surprise 404s.
- **P1:** CI green on core tests; &lt;2s perceived load on Transactions for typical families.
- **P2:** User-facing polish that reduces support questions (“where do I add money?”).

---

*Suggested horizon: P0 in the next release; P1 in the following sprint; P2 as backlog pulled by feedback.*
