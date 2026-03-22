# Reusable contact form modal

The marketing contact modal (Quill message + spam protection) is packaged for reuse.

**Default:** a simple **math question** (`a + b`) stored in the session — no Google or third-party scripts.

## Files

| File | Role |
|------|------|
| `resources/views/components/contact-form-modal.blade.php` | Blade component `<x-contact-form-modal />` |
| `public/css/famledger-contact-form-modal.css` | Scoped styles (`.famledger-contact-form-modal`) |
| `public/js/famledger-contact-form-modal.js` | Quill + captcha (math or reCAPTCHA) for Bootstrap 3 modals |

## Admin: same modal on the **list** page (no separate detail page)

On **`/admin/contact-messages`**, **View** loads the message in the same **`<x-contact-form-modal variant="view" />`** via **AJAX** (`GET /admin/contact-messages/{id}/modal`), injects HTML into `#famledger-admin-contact-modal-container` (stacked in `layouts/metronic.blade.php` outside `<main>`), **moves** `#adminContactMessageModal` to **`document.body`** (avoids an `aria-hidden` wrapper around a focused dialog), then runs `FamLedgerContactFormModal.init()` and opens the Bootstrap modal. **Opening the modal does not change read status** (the table is not re-rendered until you reload); use **Mark as read** so the form POST redirects and the list refreshes. Deep links **`/admin/contact-messages/{id}`** still run **`show`**, mark the message read, then redirect to the index with **`?open={id}`** (full page load = list is up to date).

Assets on the index: **`famledger-bootstrap3-modal-slim.css`**, **`famledger-contact-form-modal.css`**, **jQuery**, **`bootstrap.min.js`**, **`famledger-contact-form-modal.js`**, **`admin-contact-messages-modal.js`**. Do **not** load full **`bootstrap.min.css`** on Metronic (it breaks the shell). Delete/mark-read forms inside the modal redirect back to the index (often with **`?open=`** to refresh the modal).

**Host mismatch:** Modal fetch URLs and form `action`s use **relative** `route(..., false)` so posting from `http://127.0.0.1:8000` does not target `APP_URL` (e.g. `http://localhost:8000`), which would drop the session cookie and break CSRF (**Mark as read** would fail silently or show 419).

## Configuration (`.env`)

| Variable | Values | Notes |
|----------|--------|--------|
| `CONTACT_CAPTCHA_DRIVER` | `math` (default), `recaptcha`, `none` | `none` is for local dev only. |

When `CONTACT_CAPTCHA_DRIVER=recaptcha`, set `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` (v2 “I’m not a robot”).

The landing route seeds `contact_math_a` / `contact_math_b` in session when the driver is `math`. After a successful submit, those keys are cleared so the next visit gets a new question.

## Requirements

- **jQuery** + **Bootstrap 3** modals (`data-toggle="modal"`, `shown.bs.modal`, `data-dismiss="modal"`).
- **Quill 1.3.x** + **quill.snow.css** (load before the modal script). The landing page ships them from **`public/vendor/quill/`** (first-party) so strict tracking prevention (e.g. Edge) does not treat the editor as cross-site storage.
- **POST** handler: default `route('contact.store')` (`ContactController@store`).
- **reCAPTCHA only if** `CONTACT_CAPTCHA_DRIVER=recaptcha`: `window.__recaptchaSiteKey` + `https://www.google.com/recaptcha/api.js` (see landing page).

## Admin list looks empty but the form “worked”

1. **Filters** — On `/admin/contact-messages`, if **Read** is set to “Read” but every message is still **New**, the table is empty. Use **All** or click **Show all messages** when the yellow notice appears.
2. **Search** — A leftover search string can hide everything. Clear the search box and filter again.
3. **Confirm saves** — After a successful submit, `storage/logs/laravel.log` contains `Contact message stored` with `contact_message_id`. If you see `Contact message could not be saved to the database`, run `php artisan migrate` and check MySQL/SQLite credentials in `.env`.
4. **Same database** — The browser request and `php artisan tinker` must use the same `DB_*` configuration (same `.env` as `php artisan serve`).

## Same host as `APP_URL`

The default form `action` is a **relative** URL (`/contact`), and the controller redirects with a **relative** landing path plus `#contact`, so posting from `http://127.0.0.1:8000` still hits your local app even when `APP_URL` is `http://localhost`. If you ever override `action` with an absolute URL, keep it on the same host the user is using.

## Basic usage (e.g. Metronic layout)

In the layout or page `<head>`:

```html
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<link rel="stylesheet" href="{{ asset('css/famledger-contact-form-modal.css') }}">
```

Before `</body>` (after jQuery, Bootstrap, Quill):

```html
<script src="{{ asset('vendor/quill/quill.min.js') }}" defer></script>
<script src="{{ asset('js/famledger-contact-form-modal.js') }}" defer></script>
@php
    $useRecaptcha = config('services.contact_captcha.driver') === 'recaptcha';
    $recaptchaKey = $useRecaptcha ? config('services.recaptcha.site_key') : null;
@endphp
@if (filled($recaptchaKey))
<script>window.__recaptchaSiteKey = @json($recaptchaKey);</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
```

In the view body:

```blade
<x-contact-form-modal
    :captcha-driver="config('services.contact_captcha.driver')"
    :recaptcha-site-key="config('services.contact_captcha.driver') === 'recaptcha' ? config('services.recaptcha.site_key') : null"
/>
<button type="button" class="kt-btn kt-btn-primary" data-toggle="modal" data-target="#landingContactModal">
    {{ __('Contact us') }}
</button>
```

### Metronic / Tailwind button styling

Pass classes for footer actions:

```blade
<x-contact-form-modal
    submit-button-class="kt-btn kt-btn-primary"
    close-button-class="kt-btn kt-btn-outline"
/>
```

### Second modal on another URL

Use a unique `modal-id` and matching `data-target`, and a distinct `form-source` if you handle validation separately:

```blade
<x-contact-form-modal
    modal-id="footerContactModal"
    form-source="footer_modal"
    :open-on-load="$errors->any() && old('_contact_form_source') === 'footer_modal'"
/>
```

Ensure `ContactController` (or your endpoint) still validates and redirects back with errors; use the same `form-source` value in `old('_contact_form_source')` checks.

### JavaScript API

After load, `window.FamLedgerContactFormModal.init()` re-scans for `.famledger-contact-form-modal` (e.g. after AJAX). `attach(element)` binds one modal root element.

## Math captcha

- Wrong answer: server rotates to a new pair in session; the modal reopens with validation errors and the **new** question.
- Stale session (numbers missing or invalid): user is asked to **refresh** the landing page.
- `contact_captcha_answer` is **not** flashed into `old()` after errors (same idea as not flashing reCAPTCHA tokens).

## Troubleshooting reCAPTCHA (`CONTACT_CAPTCHA_DRIVER=recaptcha`)

1. **Submit guard** — The modal script blocks submit until the widget has loaded and `grecaptcha.getResponse()` is non-empty, so you don’t POST an empty token (common when sending before the checkbox appears or before Google’s script finishes).
2. **Script timing** — `recaptcha/api.js` loads asynchronously. The modal JS retries until `grecaptcha` exists; if it still fails, check the browser console (network, ad blocker, or CSP blocking Google).
3. **Key pairs** — Use **reCAPTCHA v2 “I’m not a robot”** checkbox keys. `RECAPTCHA_SITE_KEY` + `RECAPTCHA_SECRET_KEY` must be the matching pair from the same key set.
4. **Domains** — In the reCAPTCHA admin console, add every hostname (e.g. `localhost`, `127.0.0.1`, production domain). **hostname-mismatch** in logs means the domain isn’t allowed.
5. **Server logs** — On failure, `storage/logs/laravel.log` includes Google’s `error-codes` from `siteverify` (e.g. `invalid-input-secret`, `invalid-input-response`).
6. **Single-use token** — Submitting the same challenge twice can yield `timeout-or-duplicate`; open the modal again and complete the checkbox.
7. **Siteverify** — The app sends only `secret` and `response` to Google (`remoteip` omitted) to avoid rare proxy/IP mismatches.
8. **Fourth (or later) submit “does nothing”** — reCAPTCHA tokens are **single-use**. The app does **not** flash `g-recaptcha-response` into `old()` input, so you always get a fresh checkbox after an error. Validation order: name/email/phone/message → Purify → **then** captcha verify, so a bad message does not burn a token.

## Reference implementation

See `resources/views/marketing/landing.blade.php` and `routes/web.php` (landing route seeds math session).
