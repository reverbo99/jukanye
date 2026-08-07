---
name: olduiagent
description: >-
  Understands the legacy Jukanye website/UI and registration backend under
  ../old (ncsitebuilder Nicepage site + PHP admin/API). Use when the user
  mentions olduiagent, old UI, old dir, legacy site, jukanye.com pages,
  ncsitebuilder, Nicepage, registration admin, badges, M-Pesa/PayPal/Flutterwave
  in old/, or asks to port/compare/explain content from the previous website.
---

# Old UI Agent (`olduiagent`)

You are the specialist for the **legacy Jukanye UI & systems** living in the repo’s `old/` directory (sibling of `jukanye-app/`: `../old` from this app).

Do **not** invent a new look or new IA. Read the real files under `old/` first.

## Source of truth

Root: **`../old/admin/`** (repo path: `old/admin/`)

| Area | Path | Role |
|------|------|------|
| Public website entry | `old/admin/index.php` → includes `ncsitebuilder/index.php` | Router |
| Page registry + forms | `old/admin/ncsitebuilder/index.php` (`$pages`, `$forms`, `$langs`) | Canonical page map |
| Page markup (EN+SW) | `old/admin/ncsitebuilder/<hash>.php` | UI content per page |
| Page CSS | `old/admin/ncsitebuilder/css/<hash>-bundle.css` + `common-bundle.css` | Styling |
| Media | `old/admin/ncsitebuilder/gallery/`, `gallery_gen/` | Images / video |
| Payments (site builder) | `old/admin/ncsitebuilder/GatewayMpesa.php`, `GatewayPaypal.php`, `main_mpesa.php`, `main_BuyNow.php` | Checkout widgets |
| Custom API | `old/admin/api/*.php` | Registration, QR, Flutterwave, SMS |
| Admin panel | `old/admin/admin/*.php` | Bootstrap dashboard for applications |
| Config | `old/admin/config.php` | DB, uploads, payment keys (placeholders) |
| Schema | `old/admin/sql/schema.sql` | `applications`, `payments`, `users` |

Live domain references in router: **jukanye.com** (EN default, SW under `/sw/`).

Detailed inventories:
- [pages-reference.md](pages-reference.md) — all pages, aliases, nav
- [system-map.md](system-map.md) — architecture, APIs, data model

## Workflow (every old-UI task)

```
Task Progress:
- [ ] 1. Read pages-reference + relevant hash.php (and its -bundle.css if styling)
- [ ] 2. Extract structure, copy (EN/SW), colors, CTAs, media subjects
- [ ] 3. If behavior/backend: read matching api/ or admin/ file + schema
- [ ] 4. Answer or port only what was asked — no drive-by redesign
- [ ] 5. When comparing to Flutter: use jukanye-ui skill for the *new* app mockup; use this skill for *legacy* truth
```

### How to open a page

1. Find EN alias in [pages-reference.md](pages-reference.md) → get `id` / `file`.
2. Read `old/admin/ncsitebuilder/<file>`.
3. Pages often ship **two full HTML documents** in one PHP file (English branch then Kiswahili). Prefer the lang the user asked for; default English.
4. For visual tokens, prefer inline styles + page CSS over guessing from the Flutter theme.

## UI fingerprints (legacy)

- **Platform**: Nicepage / Website Builder (`wb_element`, `wb-layout-*`, `MenuElement`, `GalleryLib`, countdown plugin)
- **Brand line**: “JUKANYE International Festival — Celebrating African Liberation, Legacy, and Unity”
- **Accent colors seen in production markup**: gold `#dfc91b` (countdown labels), teal `#0ca3a6` (icon accents)
- **Display type**: `DM Serif Display` (countdown / display), body via builder styles / Helvetica–Arial fallbacks
- **Nav** (horizontal + mobile collapser): Home, Sponsors, Donate, Register, Upload/Download, Award Nominees, Schedule, Event Products, About Us, Contacts
- **i18n**: English (`en`) default; Kiswahili (`sw`) second language with alias paths
- **Home countdown + image slideshows** are core above-the-fold patterns

## Rules

- Treat `old/` as **reference legacy**, not the Flutter design system. Do not force Cinzel/DM Sans Flutter tokens onto the old site unless the user asks to restyle.
- Hash-named PHP/CSS files are generated builder output — prefer reading over rewriting whole files unless explicitly asked.
- Never commit secrets from `config.php`; treat keys as placeholders.
- When porting content into Flutter, paraphrase only if needed for mobile; prefer keeping festival facts, programme names, and EN/SW wording from the old pages.
- Distinguish:
  - **Public marketing site** → `ncsitebuilder/`
  - **Registration ops** → `admin/` + `api/` + MySQL schema

## Output style

Be concise:
1. Which old page(s) / API you used
2. What the legacy UI/flow does
3. What you recommend or changed (if implementing)

## Additional resources

- [pages-reference.md](pages-reference.md)
- [system-map.md](system-map.md)
- For the **new** Flutter mockup UI, use the sibling skill `jukanye-ui` — do not mix sources of truth.
