## API write inventory (Laravel backend)

This file summarizes which **write operations (create/update/delete)** are exposed via the JSON API (`routes/api.php`), based on `php artisan route:list`, and which ones currently exist only in web routes (`routes/web.php`).

### Accounts & transactions

- **Incomes**
  - API: `GET /api/families/{family}/incomes`, `POST /api/families/{family}/incomes`
  - Web: list + create UI in `routes/web.php` (`families/{family}/incomes`)
  - **Missing in API:** update (`PUT/PATCH`) and delete (`DELETE`) for incomes.

- **Expenses**
  - API: `GET /api/families/{family}/expenses`, `POST /api/families/{family}/expenses`
  - Web: list + create UI (`families/{family}/expenses`)
  - **Missing in API:** update and delete for expenses.

- **Transfers**
  - API: `GET /api/families/{family}/transfers`, `POST /api/families/{family}/transfers`
  - Web: full list/create via `families/{family}/transfers`
  - **Missing in API:** update and delete for transfers.

- **Wallets**
  - API: `GET /api/families/{family}/wallets`, `GET /api/families/{family}/wallets/{wallet}`
  - Web: full CRUD via `Route::resource('wallets', WalletController::class)` under `families/{family}`
  - **Missing in API:** create (`POST`), update (`PUT/PATCH`), delete (`DELETE`) for wallets.

### Families & members

- **Families**
  - API: `GET /api/families`, `GET /api/families/{family}`
  - Web: full CRUD via `Route::resource('families', FamilyController::class)`
  - **Missing in API:** create/update/delete families.

- **Family members**
  - API: `GET /api/families/{family}/members`
  - Web: create, edit, deactivate/activate, transfer ownership, delete via `FamilyMemberController`.
  - **Missing in API:** all write operations for members (invite/add, update role/status, deactivate/activate, delete).

### Projects & funding

- **Projects**
  - API: `GET /api/families/{family}/projects`, `GET /api/families/{family}/projects/{project}`
  - Web: full CRUD on projects.
  - **Missing in API:** create/update/delete projects.

- **Project funding**
  - API: `GET /api/families/{family}/projects-funding`
  - Web: create funding entries (`projects-funding`).
  - **Missing in API:** create/delete/adjust project funding entries.

### Properties & submodules

- **Properties & assets**
  - API: `GET /api/families/{family}/properties`, `GET /api/families/{family}/properties/{property}`
  - Web: create/edit properties via `PropertyController`.
  - **Missing in API:** create/update/delete properties.

- **Maintenance, valuations, documents, depreciation**
  - API: `GET /properties/maintenance`, `GET /properties/valuations`, `GET /properties/documents`, `GET /properties/depreciation`
  - Web: corresponding `store*` methods for each.
  - **Missing in API:** create/update/delete for maintenance, valuations, documents, depreciation.

### Budgets, savings, liabilities, reconciliations

- **Budgets**
  - API: `GET /api/families/{family}/budgets`
  - Web: full CRUD via `Route::resource('budgets', BudgetController::class)`.
  - **Missing in API:** create/update/delete budgets.

- **Savings goals**
  - API: `GET /api/families/{family}/savings-goals`
  - Web: full CRUD + contribute/allocate actions.
  - **Missing in API:** create/update/delete savings goals, contribute, allocate.

- **Liabilities**
  - API: `GET /api/families/{family}/liabilities`
  - Web: full CRUD via `FamilyLiabilityController`.
  - **Missing in API:** create/update/delete liabilities.

- **Reconciliations**
  - API: `GET /api/families/{family}/reconciliations`
  - Web: create reconciliation entries.
  - **Missing in API:** create reconciliation entries (and any edits/deletes, if allowed).

### Reports, settings, admin

- **Reports**
  - API: all report endpoints are **read‑only** (`GET /api/families/{family}/reports/*`).
  - Web: also read‑only analytics; no CRUD required beyond underlying data.

- **Settings & admin**
  - API: admin endpoints are read/patch for a few resources (users, roles, contact messages).
  - Web: wider CRUD through admin controllers.
  - For mobile, we will expose **only a safe subset** of admin write actions as needed.

