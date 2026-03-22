# Pulse UI kit (distribution)

Self-contained CSS, JavaScript, and Lottie embeds for dashboard and marketing
landing patterns. Copy this folder into your app (for example `public/vendor/pulse-ui-kit/` or `static/pulse-ui-kit/`).

## Contents

| File | Role |
|------|------|
| `pulse-ui.css` | Tokens, layout, components |
| `pulse-ui.js` | **Recommended:** embedded Lottie data + `PulseUI` runtime (`file://` safe) |
| `pulse-ui.runtime.js` | Logic only; load with `*-lottie-data.js` before `min.js` if you split bundles |
| `min.js` | lottie-web 5.x (required before `PulseUI` Lottie helpers) |
| `profile.js` | Header profile menu + avatar Lottie (`PulseProfile.init`) |
| `profile-lottie-data.js` | `window.__LOTTIE_PROFILE__` (dashboard example) |
| `*-lottie-data.js` | Optional separate embeds if you use `pulse-ui.runtime.js` instead of `pulse-ui.js` |
| `source/lottie/*.json` | Original animations (reference / pipeline input; re-embed via your dev kit) |
| `examples/` | `index.html` + `dashboard.html` wired to parent assets |

## Script order (full bundle)

```html
<link rel="stylesheet" href="pulse-ui.css">
<script src="min.js"></script>
<script src="pulse-ui.js"></script>
<script src="profile-lottie-data.js"></script>
<script src="profile.js"></script>
<script>
  PulseUI.init();
  PulseProfile.init();
</script>
```

Landing-only:

```html
<link rel="stylesheet" href="pulse-ui.css">
<script src="min.js"></script>
<script src="pulse-ui.js"></script>
<script>
  PulseUI.initLandingPageLottie();
  PulseUI.initSidebarAppIcon({ containerId: "lottie-landing-app-icon" });
</script>
```

## Modular build (smaller JS)

Define embed globals, then load lottie-web, then the runtime (Lottie must exist before `PulseUI.init()` runs):

```html
<script src="dashboard-lottie-data.js"></script>
<script src="input-field-lottie-data.js"></script>
<script src="landing-page-lottie-data.js"></script>
<script src="icon-lottie-data.js"></script>
<script src="login-screen-lottie-data.js"></script>
<script src="footer-lottie-data.js"></script>
<script src="min.js"></script>
<script src="pulse-ui.runtime.js"></script>
```

## Rebuilding `pulse-ui.js`

Use the **full Pulse UI development tree** (where `merge-pulse-ui.py` lives next to the `*-lottie-data.js` fragments). Update JSON or embed scripts, run `python merge-pulse-ui.py`, then run `python build-dist.py` again to refresh this folder.

## Third-party

`min.js` is **lottie-web** (see [Lottie license](https://github.com/airbnb/lottie-web/blob/master/LICENSE)). Fonts in examples use Google Fonts (Inter); swap or self-host in production if needed.

## Examples

Open `examples/index.html` or `examples/dashboard.html` over **http://localhost** (or locally with merged `pulse-ui.js` for `file://`).
