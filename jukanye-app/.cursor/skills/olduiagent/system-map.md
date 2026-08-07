# Old system map

## Layout on disk

```
old/admin/
├── index.php                 # Public entry → ncsitebuilder
├── config.php                # DB + Flutterwave + Africa's Talking placeholders
├── upload.php
├── ncsitebuilder/            # Nicepage / Website Builder export (marketing UI)
│   ├── index.php             # Router, $pages, $forms, langs, domain
│   ├── <hash>.php            # One file per page (often EN+SW markup)
│   ├── css/                  # common-bundle + per-page bundles
│   ├── gallery/ + gallery_gen/
│   ├── GatewayMpesa.php / GatewayPaypal.php
│   ├── main_mpesa.php / main_BuyNow.php
│   └── src/, js/, fonts/, …  # Builder runtime
├── api/                      # Custom PHP API (apps, payments, SMS)
│   ├── jukanye_submit.php
│   ├── generate_qr.php
│   ├── flutter_init.php
│   ├── flutter_webhook.php
│   ├── payment_verify.php
│   ├── sms_send.php
│   └── google_sync.php
├── admin/                    # Ops dashboard (Bootstrap 5)
│   ├── login.php
│   ├── index.php             # Totals: applications / paid / pending
│   ├── registrations.php
│   ├── view.php
│   ├── actions.php
│   ├── export.php
│   ├── print_badge.php
│   └── admin-uploads.html
├── sql/schema.sql
├── assets/                   # e.g. logo.png
├── uploads/                  # Application documents
└── badges/                   # Generated QR badge PNGs
```

Relative to Flutter app: from `jukanye-app/`, use **`../old/admin/...`**.

## Request flow (marketing site)

```
Browser → old/admin/index.php
       → ncsitebuilder/index.php
       → resolve lang (en|sw) + page alias
       → include matching <hash>.php
       → CSS: css/common-bundle.css + css/<hash>-bundle.css
```

Languages in router: `en` enabled, `sw` present; `$def_lang = 'en'`. Domain: `jukanye.com`.

## Registration / payments (custom stack)

Separate from Nicepage form email: custom ops API + MySQL.

### `applications` (see `sql/schema.sql`)

Key fields: `full_name`, `email`, `phone`, `organization`, `role`, `participation_type`, `program_interest`, `booth`, `technical`, `staff_count`, `document_path`, `signature`, `qr_code_path`, `status` (`pending|approved|rejected`), `payment_status` (`unpaid|pending|paid`).

### `payments`

`application_id`, `provider`, `external_txn_id`, `amount`, `currency` (default TZS), `status` (`initiated|success|failed`), `meta` JSON.

### `users`

Admin/staff accounts for the Bootstrap panel.

### API roles

| File | Purpose |
|------|---------|
| `jukanye_submit.php` | POST application → DB → QR under `badges/` → confirmation email |
| `generate_qr.php` | QR generation helper |
| `flutter_init.php` | Flutterwave v3 payment initiation (TZS, title JUKANYE 2026) |
| `flutter_webhook.php` | Payment webhook handling |
| `payment_verify.php` | Verify payment status |
| `sms_send.php` | Africa's Talking SMS (config keys) |
| `google_sync.php` | Sheets sync hook |

Admin UI: Bootstrap cards for counts; `registrations.php` list; `print_badge.php` for badge printing; `export.php` for data export.

## Site-builder commerce

Marketing pages may embed:

- PayPal Buy Now (`main_BuyNow.php` / PayPal gateway)
- M-Pesa gateway (`GatewayMpesa.php`, `main_mpesa.php`)

These are parallel to Flutterwave in `api/` — check which surface the user means.

## Relationship to new Flutter app

| Concern | Legacy (`old/`) | New (`jukanye-app` + `jukanye-ui`) |
|---------|-----------------|-------------------------------------|
| Visual source | Nicepage pages + gallery | Design mockup PNG + Flutter theme |
| Content | EN/SW HTML in hash PHP | `lib/data/`, screens |
| Tickets / donate UX | Web pages + gateways | Mobile screens (Home/Tickets/Donate/Menu) |
| Ops | PHP admin + MySQL | (may reuse concepts; do not assume same schema wired) |

When asked to “match old website”: read this system. When asked to “match mockup / picha”: use `jukanye-ui`.

## Safety

- `config.php` often contains credential placeholders — do not leak or hardcode real secrets into commits.
- `ncsitebuilder/` is huge generated output; prefer targeted reads/greps over bulk rewrites.
- Admin panel assumes session auth (`login.php`); do not weaken auth when editing.
