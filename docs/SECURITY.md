# Security notes (family scope)

## Web & API

- **Family membership**: Any route under `families/{family}` or `/api/families/{family}/…` must treat `{family}` as untrusted. Controllers call `authorizeFamilyMember($family)` (or the API `AuthorizesFamilyMember` trait) so only users in `family_user` can proceed; otherwise **403**.
- **Nested IDs**: Foreign keys (`wallet_id`, `budget_id`, `project_id`, `family_liability_id`, etc.) are validated with `exists(…)->where('family_id', $family->id)` (or equivalent) so another family’s rows cannot be referenced.
- **Wallet show (web/API)**: After auth, `wallet->family_id` must match the route `family` or the response is **404** (prevents wallet IDOR).
- **List filters**: `wallet_id` on income/expense index endpoints only applies when the wallet belongs to that family (`$family->wallets()->whereKey(…)`), matching web behaviour.

## Transactions page

- `POST families/{family}/transactions` uses the same validation rules as standalone income/expense creation and redirects back to the unified transactions index.

## Audit log `family_id`

- Rows in `audit_logs` store `family_id` when known.
- **Application events** (e.g. login) use `session('current_family_id')`. That session key is updated by middleware on any route that has a `{family}` parameter, and after login it is set from the user’s first family (same idea as the sidebar).
- **Database/model events** use the model’s `family_id` when present; **`Family`** records use the family’s own `id` (there is no `family_id` column on `families`).
